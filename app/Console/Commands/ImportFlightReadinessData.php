<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Flight;
use App\Models\ReadinessType;
use App\Models\FlightReadinessType;

class ImportFlightReadinessData extends Command
{
    protected $signature = 'import:flight-readiness';
    protected $description = 'Import flight readiness data from CSV file';

    public function handle()
    {
        $this->info('Starting flight readiness data import...');

        $csvFile = storage_path('app/flight_readiness_data.csv');
        
        if (!file_exists($csvFile)) {
            $this->error('CSV file not found at: ' . $csvFile);
            return 1;
        }

        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->error('Cannot open CSV file');
            return 1;
        }

        // Skip header row
        fgetcsv($handle);

        $imported = 0;
        $errors = 0;

        while (($data = fgetcsv($handle)) !== false) {
            try {
                $flightNumber = $data[0];
                $readinessTypeName = $data[1];
                $isCompleted = filter_var($data[2], FILTER_VALIDATE_BOOLEAN);
                $completedAt = !empty($data[3]) ? $data[3] : null;
                $notes = $data[4] ?? null;

                // Find flight
                $flight = Flight::where('flight_number', $flightNumber)->first();
                if (!$flight) {
                    $this->warn("Flight not found: {$flightNumber}");
                    $errors++;
                    continue;
                }

                // Find readiness type
                $readinessType = ReadinessType::where('name', $readinessTypeName)->first();
                if (!$readinessType) {
                    $this->warn("Readiness type not found: {$readinessTypeName}");
                    $errors++;
                    continue;
                }

                // Create or update flight readiness record
                FlightReadinessType::updateOrCreate(
                    [
                        'flight_id' => $flight->id,
                        'readiness_type_id' => $readinessType->id,
                    ],
                    [
                        'is_completed' => $isCompleted,
                        'completed_at' => $completedAt,
                        'notes' => $notes,
                    ]
                );

                $imported++;
                $this->line("Imported: {$flightNumber} - {$readinessTypeName}");

            } catch (\Exception $e) {
                $this->error("Error importing row: " . implode(',', $data) . " - " . $e->getMessage());
                $errors++;
            }
        }

        fclose($handle);

        $this->info("Import completed. Imported: {$imported}, Errors: {$errors}");
        return 0;
    }
} 