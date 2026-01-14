<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\DatabaseBackupService;
use App\Services\Admin\DatabaseImportService;
use Inertia\Inertia;

/**
 * DatabaseController
 * Thin controller for database management operations
 * Logic delegated to DatabaseBackupService and DatabaseImportService
 */
class DatabaseController extends Controller
{
    public function __construct(
        protected DatabaseBackupService $backupService,
        protected DatabaseImportService $importService
    ) {
        // Check for permission to manage database backups
        $this->middleware(function ($request, $next) {
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

            // Super admin has all permissions
            if ($admin->isSuperAdmin()) {
                return $next($request);
            }

            // Check if admin has 'manage-backups' permission
            if (!$admin->hasPermission('manage-backups')) {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengelola database.');
            }

            return $next($request);
        });
    }

    /**
     * Display database management page
     */
    public function index()
    {
        $backups = $this->backupService->getBackups();
        $stats = $this->backupService->getStats();

        return Inertia::render('Admin/Database/Index', compact('backups', 'stats'));
    }

    /**
     * Get backups list via API (for auto-refresh)
     */
    public function getBackupsList()
    {
        return response()->json([
            'backups' => $this->backupService->getBackups(),
            'stats' => $this->backupService->getStats(),
        ]);
    }

    /**
     * Create new backup
     */
    public function backupCreate()
    {
        try {
            $result = $this->backupService->create();

            return redirect()->route('admin.database.index')
                ->with('success', 'Backup berhasil dibuat: ' . $result['filename']);
        } catch (\Exception $e) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function backupDownload($filename)
    {
        $response = $this->backupService->download($filename);

        if (!$response) {
            return redirect()->route('admin.database.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        return $response;
    }

    /**
     * Delete backup file
     */
    public function backupDestroy($filename)
    {
        $this->backupService->delete($filename);

        return redirect()->route('admin.database.index')
            ->with('success', 'Backup berhasil dihapus.');
    }

    /**
     * Import database from SQL file
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'import_mode' => 'nullable|in:full,data_only',
            'clear_existing' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $importMode = $request->input('import_mode', 'full');
        $clearExisting = $request->boolean('clear_existing', false);

        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'sql') {
            return back()->with('error', 'Hanya file .sql yang didukung.');
        }

        try {
            if ($importMode === 'data_only') {
                // Import data only - preserves current schema
                $result = $this->importService->importSQLDataOnly($file, [
                    'clear_existing' => $clearExisting
                ]);

                $message = "Import data berhasil! {$result['insert_count']} data diimport, {$result['skipped_count']} dilewati.";
                if (!empty($result['tables_cleared'])) {
                    $message .= " Tabel dibersihkan: " . implode(', ', $result['tables_cleared']);
                }
            } else {
                // Full import - replaces schema and data
                $result = $this->importService->importSQL($file);
                $message = "Database berhasil diimport! {$result['tables_count']} tabel diproses.";
            }

            return redirect()->route('admin.database.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Gagal mengimport database: ' . $e->getMessage());
        }
    }

    /**
     * Get auto backup settings
     */
    public function getAutoBackupSettings()
    {
        return response()->json([
            'enabled' => (bool) \App\Models\Setting::getValue('auto_backup_enabled', false),
            'interval_months' => (int) \App\Models\Setting::getValue('auto_backup_interval_months', 0),
            'interval_weeks' => (int) \App\Models\Setting::getValue('auto_backup_interval_weeks', 0),
            'interval_days' => (int) \App\Models\Setting::getValue('auto_backup_interval_days', 1),
            'interval_hours' => (int) \App\Models\Setting::getValue('auto_backup_interval_hours', 0),
            'interval_minutes' => (int) \App\Models\Setting::getValue('auto_backup_interval_minutes', 0),
            'last_run' => \App\Models\Setting::getValue('auto_backup_last_run'),
            'next_run' => \App\Models\Setting::getValue('auto_backup_next_run'),
            'max_files' => (int) \App\Models\Setting::getValue('auto_backup_max_files', 10),
        ]);
    }

    /**
     * Update auto backup settings
     */
    public function updateAutoBackupSettings(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'interval_months' => 'required|integer|min:0|max:12',
            'interval_weeks' => 'required|integer|min:0|max:52',
            'interval_days' => 'required|integer|min:0|max:365',
            'interval_hours' => 'required|integer|min:0|max:23',
            'interval_minutes' => 'required|integer|min:0|max:59',
            'max_files' => 'required|integer|min:1|max:100',
        ]);

        // Use boolean() method to properly parse the enabled value
        $enabled = $request->boolean('enabled');

        \App\Models\Setting::setValue('auto_backup_enabled', $enabled, 'boolean');
        \App\Models\Setting::setValue('auto_backup_interval_months', $request->interval_months, 'integer');
        \App\Models\Setting::setValue('auto_backup_interval_weeks', $request->interval_weeks, 'integer');
        \App\Models\Setting::setValue('auto_backup_interval_days', $request->interval_days, 'integer');
        \App\Models\Setting::setValue('auto_backup_interval_hours', $request->interval_hours, 'integer');
        \App\Models\Setting::setValue('auto_backup_interval_minutes', $request->interval_minutes, 'integer');
        \App\Models\Setting::setValue('auto_backup_max_files', $request->max_files, 'integer');

        // Calculate next run if enabled
        if ($enabled) {
            $totalMinutes = ($request->interval_months * 30 * 24 * 60)
                + ($request->interval_weeks * 7 * 24 * 60)
                + ($request->interval_days * 24 * 60)
                + ($request->interval_hours * 60)
                + $request->interval_minutes;

            if ($totalMinutes > 0) {
                $lastRun = \App\Models\Setting::getValue('auto_backup_last_run');
                $baseTime = $lastRun ? \Carbon\Carbon::parse($lastRun) : now();
                $nextRun = $baseTime->copy()->addMinutes($totalMinutes);

                // If next run is in the past, set it to now + interval
                if ($nextRun->lt(now())) {
                    $nextRun = now()->addMinutes($totalMinutes);
                }

                \App\Models\Setting::setValue('auto_backup_next_run', $nextRun->toDateTimeString());
            }
        }

        \App\Models\ActivityLog::log('settings', 'Mengubah pengaturan auto backup');

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan auto backup berhasil disimpan.',
            'next_run' => \App\Models\Setting::getValue('auto_backup_next_run'),
        ]);
    }
}

