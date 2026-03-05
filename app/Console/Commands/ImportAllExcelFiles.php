<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportAllExcelFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:excel-all {directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all Excel files from a directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = $this->argument('directory');
        
        // Fix directory path - check different possible locations
        $fullPath = null;
        
        if (is_dir($directory)) {
            $fullPath = $directory;
        } else {
            // Try storage path
            $storagePath = storage_path($directory);
            if (is_dir($storagePath)) {
                $fullPath = $storagePath;
            } else {
                // Try base path
                $basePath = base_path($directory);
                if (is_dir($basePath)) {
                    $fullPath = $basePath;
                } else {
                    $this->error("Directory not found in any of these locations:");
                    $this->error("- " . $directory);
                    $this->error("- " . $storagePath);
                    $this->error("- " . $basePath);
                    return 1;
                }
            }
        }
        
        $this->info("Scanning directory: {$fullPath}");

        // Find all Excel files
        $files = File::glob($fullPath . '/*.{xlsx,xls,csv}', GLOB_BRACE);
        
        if (empty($files)) {
            $this->info('No Excel files found in the directory.');
            return 0;
        }

        $this->info('Found ' . count($files) . ' Excel files.');

        $successCount = 0;
        $failCount = 0;

        foreach ($files as $file) {
            $fileName = basename($file);
            $this->info("Processing: {$fileName}");
            
            try {
                // Call the import command for each file
                $exitCode = $this->call('import:excel', [
                    'file' => $file
                ]);
                
                if ($exitCode === 0) {
                    $this->info("✓ Successfully imported: {$fileName}");
                    $successCount++;
                } else {
                    $this->error("✗ Failed to import: {$fileName}");
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to import {$fileName}: " . $e->getMessage());
                $failCount++;
            }
        }

        $this->info('Mass import completed.');
        $this->info("Success: {$successCount}, Failed: {$failCount}");
        return 0;
    }
}
