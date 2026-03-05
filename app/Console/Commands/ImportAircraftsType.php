<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\AircraftsType;

class ImportAircraftsType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aircrafts:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт типов ВС из Excel в aircrafts_types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('excel/AircraftsType.xlsx');
        if (!file_exists($path)) {
            $this->error('Файл не найден: ' . $path);
            return 1;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Получаем соответствие: имя колонки (нормализованное) => буква
        $header = array_shift($rows); // ['A' => 'IATA', ...]
        $colMap = [];
        foreach ($header as $colLetter => $colName) {
            // Удаляем всё кроме букв/цифр
            $normalized = preg_replace('/[^a-z0-9]/u', '', mb_strtolower($colName));
            $colMap[$normalized] = $colLetter;
        }

        // Маппинг с нормализацией
        $map = [
            'iata' => 'iata',
            'icao' => 'icao',
            'rus' => 'rus',
            'nameeng' => 'name_eng',
            'namerus' => 'name_rus',
            'group' => 'group',
            'crew1' => 'crew1',
            'crew2' => 'crew2',
            'countrymanufacture' => 'country_manufacture',
            'wingspan' => 'wingspan',
            'long' => 'long',
            'helicopter' => 'helicopter',
        ];

        $count = 0;
        foreach ($rows as $row) {
            $data = [];
            foreach ($map as $excelCol => $dbCol) {
                $colLetter = $colMap[$excelCol] ?? null;
                $value = $colLetter ? ($row[$colLetter] ?? null) : null;
                if (is_string($value)) {
                    $value = trim($value);
                    // Удаляем ведущие слэши и пробелы
                    $value = preg_replace('/^[\\\\\s]+/u', '', $value);
                }
                $data[$dbCol] = $value;
            }
            // Преобразуем Helicopter в bool
            if (isset($data['helicopter'])) {
                $data['helicopter'] = ($data['helicopter'] === '1' || strtolower($data['helicopter']) === 'yes' || strtolower($data['helicopter']) === 'true') ? 1 : 0;
            }
            AircraftsType::create($data);
            $count++;
        }
        $this->info("Импортировано: $count записей");
        return 0;
    }
}
