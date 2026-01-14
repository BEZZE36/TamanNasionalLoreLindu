<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemHealthService
{
    public function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'active', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection Failed'];
        }
    }

    public function checkStorage()
    {
        try {
            // Check if we can write to storage
            Storage::disk('public')->put('health_check.txt', 'OK');
            Storage::disk('public')->delete('health_check.txt');
            return ['status' => 'active', 'message' => 'Writable'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Not Writable'];
        }
    }

    public function checkPaymentGateway()
    {
        // Simulate Midtrans check or check configuration
        if (config('services.midtrans.server_key')) {
            return ['status' => 'active', 'message' => 'Configured'];
        }
        return ['status' => 'warning', 'message' => 'Not Configured'];
    }

    public function checkDiskSpace()
    {
        try {
            $free = disk_free_space(base_path());
            $total = disk_total_space(base_path());
            $percentageObj = ($free / $total) * 100;
            $format = $this->formatBytes($free);

            return [
                'status' => $percentageObj > 10 ? 'active' : 'warning',
                'message' => $format . ' Free',
                'raw' => $percentageObj
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Unknown'];
        }
    }

    public function checkMemory()
    {
        try {
            $memory = memory_get_usage(true);
            $img = $this->formatBytes($memory);
            return ['status' => 'active', 'message' => $img . ' Used'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Unknown'];
        }
    }

    private function formatBytes($bytes, $precision = 1)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Calculate bytes 
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getSystemStatus()
    {
        $db = $this->checkDatabase();
        $storage = $this->checkStorage();
        $payment = $this->checkPaymentGateway();
        $disk = $this->checkDiskSpace();
        $memory = $this->checkMemory();

        $allOperational = $db['status'] === 'active' && $storage['status'] === 'active';

        return [
            'database' => $db,
            'storage' => $storage,
            'payment' => $payment,
            'disk' => $disk,
            'memory' => $memory,
            'overall' => $allOperational ? 'Operational' : 'Issues Detected',
            'overall_color' => $allOperational ? 'emerald' : 'red'
        ];
    }
}
