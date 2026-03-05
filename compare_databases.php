<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Получаем настройки подключения из конфига
$defaultConnection = Config::get('database.default');
$connections = Config::get('database.connections');
$mainDb = $connections[$defaultConnection] ?? $connections['mysql'] ?? null;

if (!$mainDb) {
    die("Не удалось получить настройки подключения к БД\n");
}

$db1Name = $mainDb['database']; // myflight
$db2Name = 'myflightbackup210126';

$host = $mainDb['host'] ?? '127.0.0.1';
$username = $mainDb['username'] ?? 'root';
$password = $mainDb['password'] ?? '';

echo "═══════════════════════════════════════════════════════════\n";
echo "  СРАВНЕНИЕ БАЗ ДАННЫХ\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "База данных 1: {$db1Name}\n";
echo "База данных 2: {$db2Name}\n";
echo "Хост: {$host}\n\n";

try {
    // Подключаемся к MySQL
    $pdo = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Получаем список таблиц из обеих баз
    $tables1 = [];
    $tables2 = [];
    
    $stmt1 = $pdo->query("SHOW TABLES FROM `{$db1Name}`");
    while ($row = $stmt1->fetch(PDO::FETCH_NUM)) {
        $tables1[] = $row[0];
    }
    
    $stmt2 = $pdo->query("SHOW TABLES FROM `{$db2Name}`");
    while ($row = $stmt2->fetch(PDO::FETCH_NUM)) {
        $tables2[] = $row[0];
    }

    sort($tables1);
    sort($tables2);

    echo "═══════════════════════════════════════════════════════════\n";
    echo "  СРАВНЕНИЕ ТАБЛИЦ\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "Таблиц в {$db1Name}: " . count($tables1) . "\n";
    echo "Таблиц в {$db2Name}: " . count($tables2) . "\n\n";

    // Таблицы только в первой базе
    $onlyIn1 = array_diff($tables1, $tables2);
    if (!empty($onlyIn1)) {
        echo "Таблицы только в {$db1Name}:\n";
        foreach ($onlyIn1 as $table) {
            echo "  - {$table}\n";
        }
        echo "\n";
    }

    // Таблицы только во второй базе
    $onlyIn2 = array_diff($tables2, $tables1);
    if (!empty($onlyIn2)) {
        echo "Таблицы только в {$db2Name}:\n";
        foreach ($onlyIn2 as $table) {
            echo "  - {$table}\n";
        }
        echo "\n";
    }

    // Общие таблицы
    $commonTables = array_intersect($tables1, $tables2);
    echo "Общих таблиц: " . count($commonTables) . "\n\n";

    // Сравниваем количество записей в общих таблицах
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  СРАВНЕНИЕ КОЛИЧЕСТВА ЗАПИСЕЙ\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $differences = [];
    $maxTableNameLength = 0;
    
    foreach ($commonTables as $table) {
        try {
            $count1 = $pdo->query("SELECT COUNT(*) FROM `{$db1Name}`.`{$table}`")->fetchColumn();
            $count2 = $pdo->query("SELECT COUNT(*) FROM `{$db2Name}`.`{$table}`")->fetchColumn();
            
            if ($count1 != $count2) {
                $differences[] = [
                    'table' => $table,
                    'count1' => $count1,
                    'count2' => $count2,
                    'diff' => $count1 - $count2
                ];
                $maxTableNameLength = max($maxTableNameLength, strlen($table));
            }
        } catch (Exception $e) {
            echo "Ошибка при проверке таблицы {$table}: " . $e->getMessage() . "\n";
        }
    }

    if (empty($differences)) {
        echo "✓ Все таблицы имеют одинаковое количество записей\n\n";
    } else {
        printf("%-{$maxTableNameLength}s | %15s | %15s | %10s\n", "Таблица", $db1Name, $db2Name, "Разница");
        echo str_repeat("-", $maxTableNameLength + 15 + 15 + 10 + 9) . "\n";
        
        foreach ($differences as $diff) {
            printf(
                "%-{$maxTableNameLength}s | %15d | %15d | %+10d\n",
                $diff['table'],
                $diff['count1'],
                $diff['count2'],
                $diff['diff']
            );
        }
        echo "\n";
    }

    // Сравниваем структуру общих таблиц
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  СРАВНЕНИЕ СТРУКТУРЫ ТАБЛИЦ\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $structureDifferences = [];
    $checkedTables = 0;
    $maxTableNameLength = 0;
    
    foreach ($commonTables as $table) {
        try {
            $columns1 = $pdo->query("SHOW COLUMNS FROM `{$db1Name}`.`{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            $columns2 = $pdo->query("SHOW COLUMNS FROM `{$db2Name}`.`{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            
            $cols1Map = [];
            $cols2Map = [];
            
            foreach ($columns1 as $col) {
                $cols1Map[$col['Field']] = $col;
            }
            foreach ($columns2 as $col) {
                $cols2Map[$col['Field']] = $col;
            }
            
            $diff = [];
            
            // Колонки только в первой базе
            $onlyIn1 = array_diff_key($cols1Map, $cols2Map);
            if (!empty($onlyIn1)) {
                $diff['only_in_1'] = array_keys($onlyIn1);
            }
            
            // Колонки только во второй базе
            $onlyIn2 = array_diff_key($cols2Map, $cols1Map);
            if (!empty($onlyIn2)) {
                $diff['only_in_2'] = array_keys($onlyIn2);
            }
            
            // Различия в типах/атрибутах
            $typeDiffs = [];
            foreach ($cols1Map as $colName => $col1) {
                if (isset($cols2Map[$colName])) {
                    $col2 = $cols2Map[$colName];
                    $col1Str = $col1['Type'] . ($col1['Null'] === 'NO' ? ' NOT NULL' : '') . 
                              ($col1['Default'] !== null ? " DEFAULT '{$col1['Default']}'" : '') . 
                              ($col1['Extra'] ? " {$col1['Extra']}" : '');
                    $col2Str = $col2['Type'] . ($col2['Null'] === 'NO' ? ' NOT NULL' : '') . 
                              ($col2['Default'] !== null ? " DEFAULT '{$col2['Default']}'" : '') . 
                              ($col2['Extra'] ? " {$col2['Extra']}" : '');
                    
                    if ($col1Str !== $col2Str) {
                        $typeDiffs[$colName] = [
                            $db1Name => $col1Str,
                            $db2Name => $col2Str
                        ];
                    }
                }
            }
            
            if (!empty($typeDiffs)) {
                $diff['type_diffs'] = $typeDiffs;
            }
            
            if (!empty($diff)) {
                $structureDifferences[$table] = $diff;
                $maxTableNameLength = max($maxTableNameLength, strlen($table));
            }
            
            $checkedTables++;
        } catch (Exception $e) {
            // Пропускаем ошибки
        }
    }
    
    if (empty($structureDifferences)) {
        echo "✓ Структура всех общих таблиц идентична ({$checkedTables} таблиц проверено)\n\n";
    } else {
        echo "Найдены различия в структуре " . count($structureDifferences) . " таблиц:\n\n";
        
        foreach ($structureDifferences as $table => $diff) {
            echo "Таблица: {$table}\n";
            
            if (isset($diff['only_in_1'])) {
                echo "  Колонки только в {$db1Name}:\n";
                foreach ($diff['only_in_1'] as $col) {
                    echo "    - {$col}\n";
                }
            }
            
            if (isset($diff['only_in_2'])) {
                echo "  Колонки только в {$db2Name}:\n";
                foreach ($diff['only_in_2'] as $col) {
                    echo "    - {$col}\n";
                }
            }
            
            if (isset($diff['type_diffs'])) {
                echo "  Различия в типах/атрибутах:\n";
                foreach ($diff['type_diffs'] as $col => $types) {
                    echo "    Колонка: {$col}\n";
                    echo "      {$db1Name}: {$types[$db1Name]}\n";
                    echo "      {$db2Name}: {$types[$db2Name]}\n";
                }
            }
            
            echo "\n";
        }
    }

    // Сравниваем таблицы, связанные с импортом
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  ДЕТАЛЬНОЕ СРАВНЕНИЕ ТАБЛИЦ ИМПОРТА\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $importTables = [
        'sr_messages',
        'sr_message_event_descriptions',
        'sr_message_risk_assessments',
        'sr_message_analysis',
        'sr_message_actions'
    ];

    foreach ($importTables as $table) {
        if (!in_array($table, $commonTables)) {
            echo "⚠ Таблица {$table} отсутствует в одной из баз\n";
            continue;
        }

        try {
            $count1 = $pdo->query("SELECT COUNT(*) FROM `{$db1Name}`.`{$table}`")->fetchColumn();
            $count2 = $pdo->query("SELECT COUNT(*) FROM `{$db2Name}`.`{$table}`")->fetchColumn();
            
            echo "Таблица: {$table}\n";
            echo "  {$db1Name}: {$count1} записей\n";
            echo "  {$db2Name}: {$count2} записей\n";
            
            if ($count1 != $count2) {
                $diff = $count1 - $count2;
                echo "  Разница: " . ($diff > 0 ? "+" : "") . "{$diff} записей\n";
            } else {
                echo "  ✓ Количество записей совпадает\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "Ошибка при проверке таблицы {$table}: " . $e->getMessage() . "\n\n";
        }
    }
    
    // Сравниваем таблицы документации
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  СРАВНЕНИЕ ТАБЛИЦ ДОКУМЕНТАЦИИ\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $docTables = [
        'doc_sections',
        'doc_categories',
        'doc_subcategories',
        'doc_documents',
        'doc_document_approvals',
        'doc_document_approvers',
        'doc_document_approval_sheet',
        'doc_document_approval_files',
        'doc_document_familiarizations'
    ];
    
    foreach ($docTables as $table) {
        $in1 = in_array($table, $tables1);
        $in2 = in_array($table, $tables2);
        
        echo "Таблица: {$table}\n";
        echo "  В {$db1Name}: " . ($in1 ? "✓ существует" : "✗ отсутствует") . "\n";
        echo "  В {$db2Name}: " . ($in2 ? "✓ существует" : "✗ отсутствует") . "\n";
        
        if ($in1 && $in2) {
            try {
                $count1 = $pdo->query("SELECT COUNT(*) FROM `{$db1Name}`.`{$table}`")->fetchColumn();
                $count2 = $pdo->query("SELECT COUNT(*) FROM `{$db2Name}`.`{$table}`")->fetchColumn();
                echo "  Записей в {$db1Name}: {$count1}\n";
                echo "  Записей в {$db2Name}: {$count2}\n";
                
                if ($count1 != $count2) {
                    $diff = $count1 - $count2;
                    echo "  Разница: " . ($diff > 0 ? "+" : "") . "{$diff} записей\n";
                }
            } catch (Exception $e) {
                echo "  Ошибка: " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
    exit(1);
}
