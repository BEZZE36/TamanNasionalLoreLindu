<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Log an activity
     */
    protected function logActivity(string $action, string $description, $model = null, $oldValues = null, $newValues = null)
    {
        return ActivityLog::log($action, $description, $model, $oldValues, $newValues);
    }

    /**
     * Log create action
     */
    protected function logCreate($model, string $modelName, string $itemName = null)
    {
        $description = "Membuat {$modelName}";
        if ($itemName) {
            $description .= ": {$itemName}";
        }
        return $this->logActivity('create', $description, $model);
    }

    /**
     * Log update action with old/new values
     */
    protected function logUpdate($model, string $modelName, $oldValues = null, $newValues = null, string $itemName = null)
    {
        $description = "Memperbarui {$modelName}";
        if ($itemName) {
            $description .= ": {$itemName}";
        }
        return $this->logActivity('update', $description, $model, $oldValues, $newValues);
    }

    /**
     * Log delete action
     */
    protected function logDelete($model, string $modelName, string $itemName = null)
    {
        $description = "Menghapus {$modelName}";
        if ($itemName) {
            $description .= ": {$itemName}";
        }
        return $this->logActivity('delete', $description, $model);
    }

    /**
     * Log toggle/status change action
     */
    protected function logToggle($model, string $modelName, string $field, $newValue, string $itemName = null)
    {
        $statusText = $newValue ? 'mengaktifkan' : 'menonaktifkan';
        $description = ucfirst($statusText) . " {$modelName}";
        if ($itemName) {
            $description .= ": {$itemName}";
        }
        return $this->logActivity('toggle', $description, $model, [$field => !$newValue], [$field => $newValue]);
    }

    /**
     * Log publish action
     */
    protected function logPublish($model, string $modelName, bool $isPublish, string $itemName = null)
    {
        $action = $isPublish ? 'publish' : 'unpublish';
        $actionText = $isPublish ? 'Mempublikasikan' : 'Membatalkan publikasi';
        $description = "{$actionText} {$modelName}";
        if ($itemName) {
            $description .= ": {$itemName}";
        }
        return $this->logActivity($action, $description, $model);
    }

    /**
     * Log export action
     */
    protected function logExport(string $modelName, string $format = 'CSV', int $count = null)
    {
        $description = "Mengekspor {$modelName} ke {$format}";
        if ($count) {
            $description .= " ({$count} data)";
        }
        return $this->logActivity('export', $description);
    }

    /**
     * Log send action (e.g., send email, notification)
     */
    protected function logSend($model, string $modelName, string $itemName = null)
    {
        $description = "Mengirim {$modelName}";
        if ($itemName) {
            $description .= ": {$itemName}";
        }
        return $this->logActivity('send', $description, $model);
    }

    /**
     * Log scan action (e.g., ticket scan)
     */
    protected function logScan($model, string $description)
    {
        return $this->logActivity('scan', $description, $model);
    }

    /**
     * Log check-in action
     */
    protected function logCheckin($model, string $description)
    {
        return $this->logActivity('checkin', $description, $model);
    }

    /**
     * Log backup action
     */
    protected function logBackup(string $description)
    {
        return $this->logActivity('backup', $description);
    }

    /**
     * Log restore action
     */
    protected function logRestore(string $description)
    {
        return $this->logActivity('restore', $description);
    }

    /**
     * Log settings change
     */
    protected function logSettings(string $settingName, $oldValues = null, $newValues = null)
    {
        $description = "Mengubah pengaturan: {$settingName}";
        return $this->logActivity('settings', $description, null, $oldValues, $newValues);
    }

    /**
     * Log bulk action
     */
    protected function logBulk(string $action, string $modelName, int $count)
    {
        $actionText = match ($action) {
            'delete' => 'Menghapus',
            'update' => 'Memperbarui',
            'publish' => 'Mempublikasikan',
            'unpublish' => 'Membatalkan publikasi',
            default => ucfirst($action),
        };
        $description = "{$actionText} {$count} {$modelName}";
        return $this->logActivity($action, $description);
    }
}
