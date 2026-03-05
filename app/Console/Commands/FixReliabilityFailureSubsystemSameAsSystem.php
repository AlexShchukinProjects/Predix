<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RelFailureSystem;
use App\Models\ReliabilityFailure;
use Illuminate\Console\Command;

/**
 * Исправляет записи отказов, где при импорте в подсистему попала система (system_name = subsystem_name).
 * Для каждой такой записи подсистема заменяется на первую подходящую из rel_failure_systems (system_name совпадает, subsystem_name != system_name).
 */
class FixReliabilityFailureSubsystemSameAsSystem extends Command
{
    protected $signature = 'reliability:fix-subsystem-same-as-system {--dry-run : only show what would be updated}';

    protected $description = 'Fix rel_failures where subsystem_name was incorrectly set to system_name';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $failures = ReliabilityFailure::whereColumn('system_name', 'subsystem_name')
            ->whereNotNull('system_name')
            ->where('system_name', '!=', '')
            ->get();

        $this->info('Found ' . $failures->count() . ' failures where system_name = subsystem_name.');

        $fixed = 0;
        $noSubsystem = 0;

        foreach ($failures as $failure) {
            $firstSub = RelFailureSystem::where('system_name', $failure->system_name)
                ->whereNotNull('subsystem_name')
                ->where('subsystem_name', '!=', $failure->system_name)
                ->orderBy('subsystem_name')
                ->value('subsystem_name');

            if ($firstSub === null) {
                $this->warn("  No subsystem found for system \"{$failure->system_name}\" (failure id={$failure->id})");
                $noSubsystem++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [dry-run] id={$failure->id}: system={$failure->system_name}, subsystem {$failure->subsystem_name} -> {$firstSub}");
                $fixed++;
                continue;
            }

            $failure->update(['subsystem_name' => $firstSub]);
            $this->line("  id={$failure->id}: subsystem {$failure->system_name} -> {$firstSub}");
            $fixed++;
        }

        $this->info('Fixed: ' . $fixed . '.' . ($noSubsystem ? " Skipped (no subsystem in ref): {$noSubsystem}." : ''));
        return Command::SUCCESS;
    }
}
