<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AircraftsType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportUsersFromExcel extends Command
{
    protected $signature = 'users:import {path : Путь к Excel файлу}';

    protected $description = 'Импорт пользователей из Excel (ФИО, логин, роль, тип ВС, должность и пр.)';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("Файл не найден: {$path}");
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $header = array_map('trim', $rows[1] ?? []);
        unset($rows[1]);

        // Формат Скайлайт: ID, Логин, ФИО, Статус, Должность, Рабочий E-Mail, Мобильный телефон
        $skylightMap = [
            'id' => array_search('ID', $header, true),
            'login' => array_search('Логин', $header, true),
            'fio' => array_search('ФИО', $header, true),
            'status' => array_search('Статус', $header, true),
            'position' => array_search('Должность', $header, true),
            'email' => array_search('Рабочий E-Mail', $header, true),
            'mobile' => array_search('Мобильный телефон', $header, true),
        ];

        $isSkylight = !in_array(false, $skylightMap, true);

        if ($isSkylight) {
            return $this->importSkylightFormat($rows, $skylightMap);
        }

        // Формат по умолчанию: ФИО | Таб. № | Адрес эл.почты | Тип ВС | Должность | Роль
        $map = [
            'fio' => array_search('ФИО', $header, true),
            'tabnum' => array_search('Таб. №', $header, true),
            'email' => array_search('Адрес эл.почты', $header, true),
            'typeac' => array_search('Тип ВС', $header, true),
            'position' => array_search('Должность', $header, true),
            'role' => array_search('Роль', $header, true),
        ];

        if (in_array(false, $map, true)) {
            $this->error('Не найдены заголовки. Поддерживаются: Скайлайт (ID, Логин, ФИО, Статус, Должность, Рабочий E-Mail, Мобильный телефон) или: ФИО, Таб. №, Адрес эл.почты, Тип ВС, Должность, Роль');
            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($rows as $i => $row) {
            $fio = trim((string)($row[$map['fio']] ?? ''));
            $email = trim((string)($row[$map['email']] ?? ''));
            $position = trim((string)($row[$map['position']] ?? ''));
            $roleName = trim((string)($row[$map['role']] ?? ''));
            $typeAcStr = trim((string)($row[$map['typeac']] ?? ''));
            $tab = trim((string)($row[$map['tabnum']] ?? ''));

            if ($fio === '' || $email === '') {
                $this->warn("Строка {$i}: пропущена (нет ФИО или email)");
                continue;
            }

            $login = strstr($email, '@', true) ?: $email;
            $user = User::where('email', $email)->first();
            $data = [
                'name' => $fio,
                'email' => $email,
                'login' => $login,
                'position' => $position ?: null,
                'personnel_number' => $tab ?: null,
                'status' => 'active',
            ];

            if (!$user) {
                $data['password'] = Hash::make('Password123!');
                $user = User::create($data);
                $created++;
            } else {
                $user->update($data);
                $updated++;
            }

            if ($roleName !== '') {
                $slug = match (mb_strtolower($roleName)) {
                    'администратор' => 'admin',
                    'методист' => 'methodist',
                    'слушатель' => 'listener',
                    default => str()->slug($roleName),
                };
                $role = Role::firstOrCreate(['slug' => $slug], ['name' => $roleName]);
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            if ($typeAcStr !== '') {
                $ids = [];
                foreach (preg_split('/[,;]+|\s{2,}/u', $typeAcStr) as $token) {
                    $token = trim($token);
                    if ($token === '' || $token === '-') {
                        continue;
                    }
                    $ac = AircraftsType::where('icao', $token)->first();
                    if ($ac) {
                        $ids[] = $ac->id;
                    }
                }
                // Таблица aircraft_type_user удалена — привязка типов ВС к пользователю отключена
            }
        }

        $this->info("Создано: {$created}, обновлено: {$updated}");
        return self::SUCCESS;
    }

    private function importSkylightFormat(array $rows, array $map): int
    {
        $created = 0;
        $updated = 0;

        foreach ($rows as $i => $row) {
            $fio = trim((string)($row[$map['fio']] ?? ''));
            $login = trim((string)($row[$map['login']] ?? ''));
            $email = trim((string)($row[$map['email']] ?? ''));
            $statusStr = trim((string)($row[$map['status']] ?? ''));
            $position = trim((string)($row[$map['position']] ?? ''));
            $mobile = trim((string)($row[$map['mobile']] ?? ''));
            $externalId = trim((string)($row[$map['id']] ?? ''));

            if ($fio === '' && $login === '') {
                $this->warn("Строка {$i}: пропущена (пустая)");
                continue;
            }

            $name = $fio !== '' ? $fio : $login;
            if ($email === '') {
                $email = $login !== '' ? $login . '@skylight.import' : 'user' . $i . '@skylight.import';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Строка {$i}: пропущена (некорректный email: {$email})");
                continue;
            }

            $status = (mb_strtolower($statusStr) === 'активен' || $statusStr === '') ? 'active' : 'blocked';

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::where('login', $login)->first();
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'login' => $login ?: null,
                'position' => $position ?: null,
                'personnel_number' => $externalId ?: null,
                'mobile_phone' => $mobile ?: null,
                'status' => $status,
            ];

            if (!$user) {
                $data['password'] = Hash::make('Password123!');
                $user = User::create($data);
                $created++;
            } else {
                $user->update($data);
                $updated++;
            }
        }

        $this->info("Формат Скайлайт: создано {$created}, обновлено {$updated}.");
        return self::SUCCESS;
    }
}
