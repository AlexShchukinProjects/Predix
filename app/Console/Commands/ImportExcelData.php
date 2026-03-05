<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportExcelData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:excel {file} {--table=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Excel/CSV data to database tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $tableName = $this->option('table') ?: pathinfo($filePath, PATHINFO_FILENAME);
        
        // Clean table name
        $tableName = $this->cleanTableName($tableName);
        
        // Fix file path - check different possible locations
        if (!file_exists($filePath)) {
            // Try storage/excel directory
            $storagePath = storage_path($filePath);
            if (file_exists($storagePath)) {
                $filePath = $storagePath;
            } else {
                // Try base path + file
                $basePath = base_path($filePath);
                if (file_exists($basePath)) {
                    $filePath = $basePath;
                } else {
                    $this->error("File not found in any of these locations:");
                    $this->error("- " . $this->argument('file'));
                    $this->error("- " . $storagePath);
                    $this->error("- " . $basePath);
                    return 1;
                }
            }
        }

        $this->info("Importing data from {$filePath} to table {$tableName}");

        try {
            // Read Excel/CSV file using Spatie Simple Excel
            $reader = SimpleExcelReader::create($filePath);
            
            // Get headers from first row
            $firstRow = $reader->getRows()->first();
            if (!$firstRow) {
                $this->error('No data found in file');
                return 1;
            }
            
            $headers = array_keys($firstRow);
            
            // Clean headers and ensure uniqueness
            $cleanHeaders = $this->cleanAndUniqueHeaders($headers);
            
            // Create mapping between original and clean headers
            $headerMapping = array_combine($headers, $cleanHeaders);
            
            // Get all data
            $allRows = $reader->getRows()->toArray();
            
            if (empty($allRows)) {
                $this->error('No data found in file');
                return 1;
            }

            // Create table if not exists
            $this->createTable($tableName, $cleanHeaders, $allRows);

            // Insert data
            $this->insertData($tableName, $headerMapping, $allRows);

            $this->info("Successfully imported " . count($allRows) . " rows to table {$tableName}");
            
        } catch (\Exception $e) {
            $this->error("Error importing data: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function cleanTableName($tableName)
    {
        // Remove file extension and clean name
        $tableName = pathinfo($tableName, PATHINFO_FILENAME);
        
        // Replace dots and other special chars
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '_', $tableName);
        
        // Remove multiple underscores
        $tableName = preg_replace('/_+/', '_', $tableName);
        
        // Remove leading/trailing underscores
        $tableName = trim($tableName, '_');
        
        // Ensure it starts with a letter
        if (preg_match('/^[0-9]/', $tableName)) {
            $tableName = 'table_' . $tableName;
        }
        
        // Ensure it's not empty
        if (empty($tableName)) {
            $tableName = 'imported_table_' . time();
        }
        
        return strtolower($tableName);
    }

    private function cleanAndUniqueHeaders($headers)
    {
        $cleanHeaders = [];
        $usedHeaders = [];
        
        foreach ($headers as $header) {
            // Clean header
            $cleanHeader = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($header)));
            
            // Remove multiple underscores
            $cleanHeader = preg_replace('/_+/', '_', $cleanHeader);
            
            // Remove leading/trailing underscores
            $cleanHeader = trim($cleanHeader, '_');
            
            // Ensure it's not empty
            if (empty($cleanHeader)) {
                $cleanHeader = 'column';
            }
            
            // Ensure it starts with a letter
            if (preg_match('/^[0-9]/', $cleanHeader)) {
                $cleanHeader = 'col_' . $cleanHeader;
            }
            
            // Make it unique
            $originalCleanHeader = $cleanHeader;
            $counter = 1;
            while (in_array($cleanHeader, $usedHeaders)) {
                $cleanHeader = $originalCleanHeader . '_' . $counter;
                $counter++;
            }
            
            $usedHeaders[] = $cleanHeader;
            $cleanHeaders[] = $cleanHeader;
        }
        
        return $cleanHeaders;
    }

    private function createTable($tableName, $headers, $rows)
    {
        if (Schema::hasTable($tableName)) {
            $this->info("Table {$tableName} already exists, skipping creation");
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($headers, $rows) {
            $table->id();
            
            foreach ($headers as $index => $header) {
                if (empty($header)) continue;
                
                // Analyze data type based on sample data
                $dataType = $this->guessDataType($rows, $header);
                
                switch ($dataType) {
                    case 'integer':
                        $table->integer($header)->nullable();
                        break;
                    case 'decimal':
                        $table->decimal($header, 10, 2)->nullable();
                        break;
                    case 'date':
                        $table->date($header)->nullable();
                        break;
                    case 'datetime':
                        $table->datetime($header)->nullable();
                        break;
                    default:
                        $table->text($header)->nullable();
                }
            }
            
            $table->timestamps();
        });

        $this->info("Created table: {$tableName}");
    }

    private function guessDataType($rows, $columnKey)
    {
        $samples = array_slice($rows, 0, 10); // Check first 10 rows
        
        foreach ($samples as $row) {
            if (!isset($row[$columnKey]) || empty($row[$columnKey])) continue;
            
            $value = $row[$columnKey];
            
            // Check if it's a number
            if (is_numeric($value)) {
                return strpos($value, '.') !== false ? 'decimal' : 'integer';
            }
            
            // Check if it's a date
            if ($this->isDate($value)) {
                return strpos($value, ':') !== false ? 'datetime' : 'date';
            }
        }
        
        return 'string';
    }

    private function isDate($value)
    {
        return (bool) strtotime($value);
    }

    private function insertData($tableName, $headerMapping, $rows)
    {
        $batchSize = 100;
        $batches = array_chunk($rows, $batchSize);
        
        foreach ($batches as $batch) {
            $insertData = [];
            
            foreach ($batch as $row) {
                $rowData = [];
                
                foreach ($headerMapping as $originalHeader => $cleanHeader) {
                    if (empty($cleanHeader)) continue;
                    
                    $value = isset($row[$originalHeader]) ? $row[$originalHeader] : null;
                    
                    // Convert dates
                    if ($value && $this->isDate($value)) {
                        try {
                            $value = date('Y-m-d H:i:s', strtotime($value));
                        } catch (\Exception $e) {
                            // Keep original value if conversion fails
                        }
                    }
                    
                    $rowData[$cleanHeader] = $value;
                }
                
                $rowData['created_at'] = now();
                $rowData['updated_at'] = now();
                
                $insertData[] = $rowData;
            }
            
            DB::table($tableName)->insert($insertData);
        }
    }
}
