<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Services\Admin\DatabaseBackupService;
use Carbon\Carbon;

class AutoBackupCommand extends Command
{
    protected $signature = 'backup:auto {--force : Force backup regardless of schedule}';
    protected $description = 'Run automatic database backup based on configured schedule';

    public function __construct(protected DatabaseBackupService $backupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Check if auto backup is enabled
        $enabled = Setting::getValue('auto_backup_enabled', false);

        if (!$enabled && !$this->option('force')) {
            $this->info('Auto backup is disabled.');
            return self::SUCCESS;
        }

        // Get interval settings
        $months = (int) Setting::getValue('auto_backup_interval_months', 0);
        $weeks = (int) Setting::getValue('auto_backup_interval_weeks', 0);
        $days = (int) Setting::getValue('auto_backup_interval_days', 0);
        $hours = (int) Setting::getValue('auto_backup_interval_hours', 0);
        $minutes = (int) Setting::getValue('auto_backup_interval_minutes', 0);

        // Calculate total interval in minutes
        $totalMinutes = ($months * 30 * 24 * 60) + ($weeks * 7 * 24 * 60) + ($days * 24 * 60) + ($hours * 60) + $minutes;

        $this->info("Interval configured: {$totalMinutes} minutes");

        if ($totalMinutes <= 0 && !$this->option('force')) {
            $this->warn('No backup interval configured. Please set at least one interval value.');
            return self::SUCCESS;
        }

        // Check if it's time to backup
        $nextRun = Setting::getValue('auto_backup_next_run');
        $now = Carbon::now();

        // If force option, skip time check
        if (!$this->option('force')) {
            // If next_run is set and hasn't passed yet, skip
            if ($nextRun) {
                $nextRunTime = Carbon::parse($nextRun);
                if ($now->lt($nextRunTime)) {
                    $this->info("Next backup scheduled for: {$nextRunTime->format('Y-m-d H:i:s')}");
                    $this->info("Current time: {$now->format('Y-m-d H:i:s')}");
                    return self::SUCCESS;
                }
            }
            // If no next_run scheduled but last_run exists, calculate from last_run
            $lastRun = Setting::getValue('auto_backup_last_run');
            if (!$nextRun && $lastRun) {
                $lastRunTime = Carbon::parse($lastRun);
                $calculatedNextRun = $lastRunTime->copy()->addMinutes($totalMinutes);
                if ($now->lt($calculatedNextRun)) {
                    // Update next_run setting
                    Setting::setValue('auto_backup_next_run', $calculatedNextRun->toDateTimeString());
                    $this->info("Next backup scheduled for: {$calculatedNextRun->format('Y-m-d H:i:s')}");
                    return self::SUCCESS;
                }
            }
        }

        // Run backup
        $this->info('Starting automatic database backup...');

        try {
            $result = $this->backupService->create();

            // Update last run time
            Setting::setValue('auto_backup_last_run', $now->toDateTimeString());

            // Calculate and set next run time
            $nextRunNew = $now->copy()->addMinutes($totalMinutes > 0 ? $totalMinutes : 1);
            Setting::setValue('auto_backup_next_run', $nextRunNew->toDateTimeString());

            $this->info("Backup created successfully: {$result['filename']}");
            $this->info("Next backup scheduled for: {$nextRunNew->format('Y-m-d H:i:s')}");

            // Clean up old backups if max files is set
            $this->cleanupOldBackups();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Backup failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function cleanupOldBackups(): void
    {
        $maxFiles = (int) Setting::getValue('auto_backup_max_files', 10);

        if ($maxFiles <= 0) {
            return;
        }

        $backups = $this->backupService->getBackups();

        if (count($backups) > $maxFiles) {
            // Backups are already sorted by date descending
            $toDelete = array_slice($backups, $maxFiles);

            foreach ($toDelete as $backup) {
                $this->backupService->delete($backup['filename']);
                $this->info("Deleted old backup: {$backup['filename']}");
            }
        }
    }
}
