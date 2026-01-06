<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class AutoBackupSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'auto_backup_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'backup',
                'label' => 'Auto Backup Aktif',
                'description' => 'Aktifkan atau nonaktifkan auto backup database',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_interval_months',
                'value' => '0',
                'type' => 'integer',
                'group' => 'backup',
                'label' => 'Interval Bulan',
                'description' => 'Interval backup dalam bulan',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_interval_weeks',
                'value' => '0',
                'type' => 'integer',
                'group' => 'backup',
                'label' => 'Interval Minggu',
                'description' => 'Interval backup dalam minggu',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_interval_days',
                'value' => '1',
                'type' => 'integer',
                'group' => 'backup',
                'label' => 'Interval Hari',
                'description' => 'Interval backup dalam hari',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_interval_hours',
                'value' => '0',
                'type' => 'integer',
                'group' => 'backup',
                'label' => 'Interval Jam',
                'description' => 'Interval backup dalam jam',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_interval_minutes',
                'value' => '0',
                'type' => 'integer',
                'group' => 'backup',
                'label' => 'Interval Menit',
                'description' => 'Interval backup dalam menit',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_last_run',
                'value' => null,
                'type' => 'string',
                'group' => 'backup',
                'label' => 'Backup Terakhir',
                'description' => 'Waktu backup terakhir dijalankan',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_next_run',
                'value' => null,
                'type' => 'string',
                'group' => 'backup',
                'label' => 'Backup Berikutnya',
                'description' => 'Waktu backup berikutnya dijadwalkan',
                'is_public' => false,
            ],
            [
                'key' => 'auto_backup_max_files',
                'value' => '10',
                'type' => 'integer',
                'group' => 'backup',
                'label' => 'Maksimal File Backup',
                'description' => 'Jumlah maksimal file backup yang disimpan (otomatis hapus yang lama)',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
