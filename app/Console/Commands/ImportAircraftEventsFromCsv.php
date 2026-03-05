<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SRMessage;
use App\Models\SRMessageEventDescription;
use App\Models\SRMessageAnalysis;
use App\Models\SRMessageRiskAssessment;
use App\Models\SRMessageAction;
use App\Models\SRMessageType;
use Carbon\Carbon;

class ImportAircraftEventsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:aircraft-events-csv 
                            {file : Путь к CSV файлу}
                            {--dry-run : Тестовый запуск без сохранения в БД}
                            {--skip-existing : Пропустить записи, которые уже существуют}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт данных о авиационных событиях из CSV файла в базу данных';

    /**
     * Маппинг типов событий из CSV в типы сообщений БД
     */
    private array $eventTypeMapping = [
        1 => 'deviation',      // Отклонение
        2 => 'precursor',      // Предвестник
        4 => 'aviation_incident', // Авиационный инцидент
        11 => 'event_type_11', // Специальный тип
    ];

    /**
     * Маппинг факторов из CSV
     */
    private array $factorMapping = [
        1 => 'liveware',      // Человеческий фактор
        2 => 'hardware',      // Техника
        3 => 'environment',   // Внешняя среда
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $skipExisting = $this->option('skip-existing');

        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $this->info("Начинаю импорт данных из файла: {$filePath}");
        
        if ($dryRun) {
            $this->warn("═══════════════════════════════════════════════════════════");
            $this->warn("  РЕЖИМ ТЕСТОВОГО ЗАПУСКА (DRY-RUN)");
            $this->warn("  ✓ Файл будет прочитан и проанализирован");
            $this->warn("  ✓ Данные будут проверены на корректность");
            $this->warn("  ✗ Данные НЕ будут сохранены в базу данных");
            $this->warn("═══════════════════════════════════════════════════════════");
        }

        try {
            $csvData = $this->readCsvFile($filePath);
            $totalRows = count($csvData);
            $this->info("Найдено записей для импорта: {$totalRows}");

            $bar = $this->output->createProgressBar($totalRows);
            $bar->start();

            $imported = 0;
            $skipped = 0;
            $errors = 0;

            // НЕ используем общую транзакцию - каждая запись импортируется отдельно
            // Это позволяет продолжить импорт даже при ошибках в отдельных записях

            foreach ($csvData as $index => $row) {
                try {
                    // Заголовок уже пропущен при чтении CSV, все строки - это данные

                    // Проверяем, существует ли уже запись (по aircaft_event_id или другим уникальным полям)
                    if ($skipExisting && $this->recordExists($row)) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        // Используем отдельную транзакцию для каждой записи
                        DB::beginTransaction();
                        try {
                            $this->importRow($row);
                            DB::commit();
                            $imported++;
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $errors++;
                            Log::error("Ошибка импорта строки {$index}: " . $e->getMessage());
                            $this->warn("\nОшибка в строке {$index}: " . $e->getMessage());
                        }
                    } else {
                        // В режиме dry-run проверяем данные без сохранения
                        if ($this->validateRow($row)) {
                            $imported++;
                        } else {
                            $errors++;
                        }
                    }

                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Критическая ошибка в строке {$index}: " . $e->getMessage());
                    $this->warn("\nКритическая ошибка в строке {$index}: " . $e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if ($dryRun) {
                $this->newLine();
                $this->info("═══════════════════════════════════════════════════════════");
                $this->info("  РЕЗУЛЬТАТЫ ТЕСТОВОГО ЗАПУСКА:");
                $this->info("═══════════════════════════════════════════════════════════");
            } else {
                $this->info("Импорт завершен:");
            }
            
            $this->info("  - " . ($dryRun ? "Проверено записей" : "Импортировано") . ": {$imported}");
            $this->info("  - Пропущено: {$skipped}");
            $this->info("  - Ошибок: {$errors}");
            
            if ($dryRun && $errors === 0) {
                $this->newLine();
                $this->info("✓ Все записи прошли проверку. Можно запускать реальный импорт без флага --dry-run");
            } elseif ($dryRun && $errors > 0) {
                $this->newLine();
                $this->warn("⚠ Обнаружены ошибки в данных. Проверьте логи перед реальным импортом.");
            }
            $this->info("  - Ошибок: {$errors}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Критическая ошибка: " . $e->getMessage());
            Log::error("Критическая ошибка импорта: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Чтение CSV файла
     */
    private function readCsvFile(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new \Exception("Не удалось открыть файл: {$filePath}");
        }

        // Читаем первую строку (заголовки)
        $headers = fgetcsv($handle);
        
        if ($headers === false) {
            fclose($handle);
            throw new \Exception("Файл пуст или неверный формат");
        }

        // Нормализуем заголовки (убираем кавычки и пробелы)
        $headers = array_map(function($header) {
            return trim($header, '" ');
        }, $headers);

        // Читаем данные
        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Если количество колонок не совпадает, пытаемся исправить
            if (count($row) !== count($headers)) {
                // Логируем проблемную строку, но не пропускаем её полностью
                // Возможно, это многострочное поле, которое было разбито
                Log::warning("Строка {$rowNumber}: неверное количество колонок (" . count($row) . " вместо " . count($headers) . ")");
                
                // Если колонок меньше, дополняем пустыми значениями
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }
                // Если колонок больше, обрезаем
                elseif (count($row) > count($headers)) {
                    $row = array_slice($row, 0, count($headers));
                }
            }
            
            try {
                $dataRow = array_combine($headers, $row);
                $data[] = $dataRow;
            } catch (\Exception $e) {
                Log::warning("Не удалось объединить строку {$rowNumber}: " . $e->getMessage());
                // Пропускаем только если действительно не удалось объединить
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Проверка существования записи
     */
    private function recordExists(array $row): bool
    {
        $aircraftEventId = $row['aircaft_event_id'] ?? null;
        
        if (!$aircraftEventId) {
            return false;
        }

        // Проверяем по metadata или другому уникальному полю
        return SRMessage::whereJsonContains('metadata->aircraft_event_id', $aircraftEventId)
            ->exists();
    }

    /**
     * Валидация строки без сохранения (для dry-run)
     */
    private function validateRow(array $row): bool
    {
        try {
            // Проверяем обязательные поля
            if (empty($row['occurrence_title'])) {
                return false;
            }

            // Проверяем, что тип сообщения может быть определен
            $occurrenceTypeId = $row['occurrence_type_id'] ?? null;
            $this->getOrCreateMessageType($occurrenceTypeId);

            // Проверяем парсинг дат
            $this->parseDate($row['occurrence_date'] ?? null);
            $this->parseDateTime($row['add_date'] ?? null);
            $this->parseTime($row['utc_time'] ?? null);
            $this->parseTime($row['local_time'] ?? null);

            // Проверяем парсинг причин
            $this->parseEventCauses($row);

            return true;
        } catch (\Exception $e) {
            Log::warning("Ошибка валидации строки: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Импорт одной строки
     */
    private function importRow(array $row): void
    {
        try {
            // 1. Создаем основное сообщение
            $message = $this->createMessage($row);
            
            // 2. Создаем описание события (может быть пустым)
            try {
                $this->createEventDescription($message->id, $row);
            } catch (\Exception $e) {
                $errorMsg = "Не удалось создать описание события для сообщения {$message->id}: " . $e->getMessage();
                Log::warning($errorMsg);
                $this->warn("\n⚠ " . $errorMsg);
                // Продолжаем импорт даже если описание не создалось
            }
            
            // 3. Создаем оценку рисков (может быть пустой)
            try {
                $this->createRiskAssessment($message->id, $row);
            } catch (\Exception $e) {
                $errorMsg = "Не удалось создать оценку рисков для сообщения {$message->id}: " . $e->getMessage();
                Log::warning($errorMsg);
                $this->warn("\n⚠ " . $errorMsg);
            }
            
            // 4. Создаем анализ (может быть пустым)
            try {
                $this->createAnalysis($message->id, $row);
            } catch (\Exception $e) {
                Log::warning("Не удалось создать анализ для сообщения {$message->id}: " . $e->getMessage());
            }
            
            // 5. Создаем корректирующие действия (если есть)
            try {
                $this->createActions($message->id, $row);
            } catch (\Exception $e) {
                Log::warning("Не удалось создать действия для сообщения {$message->id}: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Если не удалось создать основное сообщение, пробрасываем исключение
            throw $e;
        }
    }

    /**
     * Создание основного сообщения
     */
    private function createMessage(array $row): SRMessage
    {
        // Определяем тип сообщения
        $occurrenceTypeId = $row['occurrence_type_id'] ?? null;
        $messageType = $this->getOrCreateMessageType($occurrenceTypeId);
        
        // Определяем статус
        // Допустимые значения: 'draft', 'submitted', 'in_review', 'approved', 'rejected'
        $isActive = $this->parseBoolean($row['is_active'] ?? 'N');
        // Если is_active = Y, то статус 'submitted', иначе 'draft'
        $status = $isActive ? 'submitted' : 'draft';
        
        // Определяем, является ли авиационным событием
        $isAviationEvent = ($occurrenceTypeId == 4); // 4 = aviation_incident
        
        // Формируем metadata
        $metadata = [
            'aircraft_event_id' => $row['aircaft_event_id'] ?? null,
            'company_id' => $row['company_id'] ?? null,
            'company_name' => $row['company_name'] ?? null,
            'airline_id' => $row['airline_id'] ?? null,
            'original_id' => $row['id'] ?? null,
        ];

        $message = SRMessage::create([
            'sr_message_type_id' => $messageType->id,
            'title' => $row['occurrence_title'] ?? 'Без названия',
            'description' => $row['description'] ?? null,
            'status' => $status,
            'created_by' => $this->getValidUserId($row['add_user_id'] ?? null), // Проверяем существование пользователя
            'is_aviation_event' => $isAviationEvent,
            'actions_required' => !empty($row['corrective_actions']),
            'metadata' => $metadata,
            'created_at' => $this->parseDateTime($row['add_date'] ?? null),
        ]);

        return $message;
    }

    /**
     * Создание описания события
     */
    private function createEventDescription(int $messageId, array $row): void
    {
        // Парсим причины события
        $eventCauses = $this->parseEventCauses($row);
        
        // Определяем фактор, вызвавший событие
        $factorId = $row['factor_causing_event_id'] ?? $row['factor_id'] ?? null;
        $factorName = $row['factor_causing_event_name'] ?? $row['factor_name'] ?? null;

        SRMessageEventDescription::create([
            'sr_message_id' => $messageId,
            'event_date' => $this->parseDate($row['occurrence_date'] ?? null),
            'event_time_utc' => $this->parseTime($row['utc_time'] ?? null),
            'event_time_local' => $this->parseTime($row['local_time'] ?? null),
            'time_of_day_id' => $this->parseNullableInt($row['time_day_id'] ?? null),
            'weather_cond_id' => $this->parseNullableInt($row['weather_cond_id'] ?? null),
            'operation_stage_id' => $this->parseNullableInt($row['operating_phase_id'] ?? null),
            'aircraft_event_type_id' => $this->parseNullableInt($row['event_type_id'] ?? null),
            'event_causes' => $eventCauses,
            'other_cause_description' => $row['comment_eng'] ?? null,
            'flight_number' => $this->truncateString($row['flight_number'] ?? null, 10),
            'airport' => $this->truncateString($row['airport_name'] ?? null, 10),
            'aircraft_type_icao' => $this->truncateString($row['aircraft_type_name'] ?? null, 10),
            'aircraft_regn' => $row['aircraft_number'] ?? null,
            // 'departure_airport' => $row['icao'] ?? null, // Поле не существует в таблице
            // 'summary' => $row['occurrence_title'] ?? null, // Поле не существует в таблице
            'description' => $row['description'] ?? null,
            // 'details' => $row['desc_occurrence_investig'] ?? null, // Поле не существует в таблице
            'department' => $row['dept_organization'] ?? null,
            'meta' => [
                'iata' => $row['iata'] ?? null,
                'airport_id' => $row['airport_id'] ?? null,
                'aircraft_id' => $row['aircraft_id'] ?? null,
                'aircraft_type_id' => $row['aircraft_type_id'] ?? null,
                'detection_phase_id' => $row['detection_phase_id'] ?? null,
                'participants_occurrence' => $row['participants_occurrence'] ?? null,
                'left_pilot_management' => $row['left_pilot_management'] ?? null,
                'factor_causing_event_id' => $factorId,
                'factor_causing_event_name' => $factorName,
                // Сохраняем поля, которых нет в таблице, в meta
                'departure_airport' => $row['icao'] ?? null,
                'summary' => $row['occurrence_title'] ?? null,
                'details' => $row['desc_occurrence_investig'] ?? null,
            ],
        ]);
    }

    /**
     * Создание оценки рисков
     */
    private function createRiskAssessment(int $messageId, array $row): void
    {
        $riskIndex = $row['risk_index'] ?? $row['event_risk_index'] ?? null;
        
        SRMessageRiskAssessment::create([
            'sr_message_id' => $messageId,
            'risk_level' => $riskIndex ? (string)$riskIndex : null, // В таблице это text, не float
            'comment' => $row['risk_index_text'] ?? null,
            // 'severity' => $row['weight_id'] ?? null, // Поле не существует в таблице
            // 'probability' => $row['probability_id'] ?? null, // Поле не существует в таблице
            'meta' => [
                'weight_id' => $row['weight_id'] ?? null,
                'probability_id' => $row['probability_id'] ?? null,
                'weight_code' => $row['weight_code'] ?? null,
                'probability_code' => $row['probability_code'] ?? null,
                'weight_name' => $row['weight_name'] ?? null,
                'probability_name' => $row['probability_name'] ?? null,
                'rm_weight_id' => $row['rm_weight_id'] ?? null,
                'rm_probability_id' => $row['rm_probability_id'] ?? null,
            ],
        ]);
    }

    /**
     * Создание анализа
     */
    private function createAnalysis(int $messageId, array $row): void
    {
        // Парсим causes для analysis_comment
        $causesText = $row['causes'] ?? null;
        $analysisComment = null;
        if (!empty($causesText) && strtoupper($causesText) !== 'NULL') {
            // Если causes - это массив причин, объединяем их в текст
            if (is_array($causesText)) {
                $analysisComment = implode("\n", array_filter($causesText, function($cause) {
                    return !empty($cause) && strtoupper($cause) !== 'NULL';
                }));
            } else {
                // Если это строка, используем как есть
                $analysisComment = $causesText;
            }
        }

        SRMessageAnalysis::create([
            'sr_message_id' => $messageId,
            'conclusion' => $row['conclusion'] ?? null,
            'recommendations' => $row['recommendations'] ?? null,
            'circumstances' => $row['grounds_for_events'] ?? null,
            'investigation_status' => $row['investigation_status_id'] ?? null,
            'commission_order_number' => $row['order_number'] ?? null,
            'hazard_factor' => $row['factor_id'] ?? null,
            'responsible_department' => $row['dept_organization'] ?? null,
            'analysis_comment' => $analysisComment,
            'meta' => [
                'date_approval' => $this->parseDate($row['date_approval'] ?? null),
                'factor_name' => $row['factor_name'] ?? null,
                'factor_title' => $row['factor_title'] ?? null,
                'downtime' => $row['downtime'] ?? null,
                'replacement_units' => $row['replacement_units'] ?? null,
                'cargo_condition' => $row['cargo_condition'] ?? null,
                'costs_eliminating' => $row['costs_eliminating'] ?? null,
            ],
        ]);
    }

    /**
     * Создание корректирующих действий
     */
    private function createActions(int $messageId, array $row): void
    {
        $correctiveActions = $row['corrective_actions'] ?? null;
        
        if (empty($correctiveActions)) {
            return;
        }

        // Разбиваем действия по строкам, если их несколько
        $actions = preg_split('/\r?\n/', $correctiveActions);
        
        foreach ($actions as $index => $action) {
            $action = trim($action);
            if (empty($action)) {
                continue;
            }

            SRMessageAction::create([
                'sr_message_id' => $messageId,
                'description' => $action,
                'due_date' => $this->parseDate($row['date_approval'] ?? null),
                'status' => 'pending',
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * Получение или создание типа сообщения
     */
    private function getOrCreateMessageType(?string $occurrenceTypeId): SRMessageType
    {
        if (!$occurrenceTypeId) {
            // Возвращаем тип по умолчанию
            return SRMessageType::firstOrCreate(
                ['name' => 'default'],
                ['description' => 'Тип по умолчанию']
            );
        }

        $typeName = $this->eventTypeMapping[$occurrenceTypeId] ?? 'default';
        
        return SRMessageType::firstOrCreate(
            ['name' => $typeName],
            ['description' => "Тип события: {$typeName}"]
        );
    }

    /**
     * Парсинг причин события
     */
    private function parseEventCauses(array $row): array
    {
        $causes = [];
        
        // Основное поле causes
        if (!empty($row['causes'])) {
            $causesText = $row['causes'];
            // Разбиваем по переносам строк или запятым
            $causesList = preg_split('/[\r\n,]+/', $causesText);
            foreach ($causesList as $cause) {
                $cause = trim($cause);
                // Пропускаем пустые значения и строку 'NULL'
                if (!empty($cause) && strtoupper($cause) !== 'NULL') {
                    $causes[] = $cause;
                }
            }
        }

        // Добавляем фактор, вызвавший событие
        $factorName = $row['factor_causing_event_name'] ?? $row['factor_name'] ?? null;
        if ($factorName && strtoupper($factorName) !== 'NULL' && !in_array($factorName, $causes)) {
            $causes[] = $factorName;
        }

        return $causes;
    }

    /**
     * Парсинг булевого значения
     */
    private function parseBoolean(?string $value): bool
    {
        return strtoupper($value ?? 'N') === 'Y';
    }

    /**
     * Парсинг даты
     */
    private function parseDate(?string $value): ?Carbon
    {
        if (empty($value) || $value === 'NULL') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            Log::warning("Не удалось распарсить дату: {$value}");
            return null;
        }
    }

    /**
     * Парсинг даты и времени
     */
    private function parseDateTime(?string $value): ?Carbon
    {
        return $this->parseDate($value);
    }

    /**
     * Получение валидного ID пользователя
     */
    private function getValidUserId(?string $userId): int
    {
        if (!$userId) {
            return 1; // По умолчанию
        }

        $userId = (int)$userId;
        
        // Проверяем существование пользователя в БД
        $exists = DB::table('users')->where('id', $userId)->exists();
        
        if (!$exists) {
            // Если пользователь не существует, используем ID 1
            return 1;
        }

        return $userId;
    }

    /**
     * Обрезка строки до указанной длины
     */
    private function truncateString(?string $value, int $maxLength): ?string
    {
        if ($value === null || $value === '' || strtoupper($value) === 'NULL') {
            return null;
        }
        
        return mb_substr($value, 0, $maxLength);
    }

    /**
     * Парсинг nullable integer (обрабатывает 'NULL' строку)
     */
    private function parseNullableInt($value): ?int
    {
        if ($value === null || $value === '' || strtoupper($value) === 'NULL') {
            return null;
        }
        
        $intValue = (int)$value;
        return $intValue > 0 ? $intValue : null;
    }

    /**
     * Парсинг времени
     */
    private function parseTime($value): ?string
    {
        if ($value === null || $value === '' || strtoupper($value) === 'NULL') {
            return null;
        }

        // Если время в формате числа (минуты от начала суток), конвертируем
        if (is_numeric($value)) {
            $minutes = (int)$value;
            if ($minutes <= 0) {
                return null;
            }
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return sprintf('%02d:%02d:00', $hours, $mins);
        }

        // Если это уже строка времени, возвращаем как есть
        return (string)$value;
    }
}
