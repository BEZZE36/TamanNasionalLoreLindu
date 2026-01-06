<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

/**
 * DatabaseBackupService
 * Handles database backup creation, listing, and management
 */
class DatabaseBackupService
{
    /**
     * Get list of available backups
     */
    public function getBackups(): array
    {
        $backupPath = storage_path('app/backups');

        if (!file_exists($backupPath)) {
            return [];
        }

        $files = glob($backupPath . '/*.sql');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        usort($backups, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        return $backups;
    }

    /**
     * Create a database backup
     */
    public function create(): array
    {
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        $this->ensureBackupDirectoryExists();

        // Try mysqldump first
        if ($this->tryMysqldump($path)) {
            ActivityLog::log('backup', 'Membuat backup database: ' . $filename);
            return ['success' => true, 'filename' => $filename];
        }

        // Fallback to PHP backup
        return $this->createSimpleBackup($filename);
    }

    /**
     * Create backup using PHP (fallback when mysqldump unavailable)
     */
    public function createSimpleBackup(string $filename): array
    {
        $path = storage_path('app/backups/' . $filename);

        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');
        $key = "Tables_in_{$database}";

        $sql = "-- Backup created at " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $sql .= $this->getTableSql($tableName);
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($path, $sql);

        ActivityLog::log('backup', 'Membuat backup database (Simple): ' . $filename);

        return ['success' => true, 'filename' => $filename];
    }

    /**
     * Download a backup file
     */
    public function download(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            return null;
        }

        ActivityLog::log('backup', 'Mengunduh backup: ' . $filename);
        return response()->download($path);
    }

    /**
     * Delete a backup file
     */
    public function delete(string $filename): bool
    {
        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            unlink($path);
            ActivityLog::log('backup', 'Menghapus backup: ' . $filename);
            return true;
        }

        return false;
    }

    /**
     * Get database statistics
     */
    public function getStats(): array
    {
        $database = config('database.connections.mysql.database');

        $query = DB::select("SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb',
            COUNT(*) AS 'tables_count'
            FROM information_schema.TABLES 
            WHERE table_schema = ?", [$database]);

        return [
            'size_mb' => $query[0]->size_mb ?? 0,
            'tables_count' => $query[0]->tables_count ?? 0,
            'connection' => config('database.default'),
            'name' => $database,
        ];
    }

    /**
     * Ensure backup directory exists
     */
    protected function ensureBackupDirectoryExists(): void
    {
        $path = storage_path('app/backups');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Try to create backup using mysqldump
     */
    protected function tryMysqldump(string $path): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $port = config('database.connections.mysql.port', 3306);

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > "%s"',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            $path
        );

        try {
            exec($command, $output, $returnVar);
            return $returnVar === 0 && file_exists($path) && filesize($path) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get SQL for a single table
     */
    protected function getTableSql(string $tableName): string
    {
        $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
        $sql = $createTable[0]->{'Create Table'} . ";\n\n";

        $rows = DB::table($tableName)->get();
        foreach ($rows as $row) {
            $values = array_map(function ($value) {
                return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
            }, (array) $row);

            $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
        }

        return $sql . "\n";
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
