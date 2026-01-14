<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\User;
use App\Models\Flora;
use App\Models\Fauna;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Import/Index');
    }

    public function template($type)
    {
        $templates = [
            'destinations' => [
                'name',
                'slug',
                'city',
                'short_description',
                'description',
                'address',
                'latitude',
                'longitude',
                'ticket_price',
                'operating_hours',
                'is_active'
            ],
            'users' => [
                'name',
                'email',
                'phone',
                'address'
            ],
            'flora' => [
                'name',
                'scientific_name',
                'description',
                'habitat',
                'conservation_status',
                'is_active'
            ],
            'fauna' => [
                'name',
                'scientific_name',
                'description',
                'habitat',
                'conservation_status',
                'is_active'
            ],
        ];

        if (!isset($templates[$type])) {
            abort(404);
        }

        $filename = "template_{$type}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($templates, $type) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $templates[$type]);
            // Sample row
            $sample = array_fill(0, count($templates[$type]), 'contoh data');
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'type' => 'required|in:destinations,users,flora,fauna',
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $type = $request->type;

        try {
            $data = $this->parseCSV($file);

            if (empty($data)) {
                return back()->with('error', 'File CSV kosong atau format tidak valid.');
            }

            $result = match ($type) {
                'destinations' => $this->importDestinations($data),
                'users' => $this->importUsers($data),
                'flora' => $this->importFlora($data),
                'fauna' => $this->importFauna($data),
            };

            ActivityLog::log('import', "Import {$result['success']} {$type} dari CSV");

            return back()->with(
                'success',
                "Import berhasil! {$result['success']} data ditambahkan, {$result['failed']} gagal."
            )->with('import_errors', $result['errors']);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    protected function parseCSV($file): array
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        $headers = fgetcsv($handle);
        if (!$headers)
            return [];

        // Clean headers
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    protected function importDestinations(array $data): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            $validator = Validator::make($row, [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|unique:destinations,slug',
                'city' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                Destination::create([
                    'name' => $row['name'],
                    'slug' => $row['slug'] ?? \Str::slug($row['name']),
                    'city' => $row['city'] ?? null,
                    'short_description' => $row['short_description'] ?? null,
                    'description' => $row['description'] ?? null,
                    'address' => $row['address'] ?? null,
                    'latitude' => $row['latitude'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                    'ticket_price' => $row['ticket_price'] ?? 0,
                    'operating_hours' => $row['operating_hours'] ?? null,
                    'is_active' => ($row['is_active'] ?? '1') === '1',
                ]);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    protected function importUsers(array $data): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            $validator = Validator::make($row, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'password' => bcrypt('password123'), // Default password
                ]);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    protected function importFlora(array $data): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            $validator = Validator::make($row, [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                Flora::create([
                    'name' => $row['name'],
                    'scientific_name' => $row['scientific_name'] ?? null,
                    'description' => $row['description'] ?? null,
                    'habitat' => $row['habitat'] ?? null,
                    'conservation_status' => $row['conservation_status'] ?? null,
                    'is_active' => ($row['is_active'] ?? '1') === '1',
                ]);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    protected function importFauna(array $data): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            $validator = Validator::make($row, [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                Fauna::create([
                    'name' => $row['name'],
                    'scientific_name' => $row['scientific_name'] ?? null,
                    'description' => $row['description'] ?? null,
                    'habitat' => $row['habitat'] ?? null,
                    'conservation_status' => $row['conservation_status'] ?? null,
                    'is_active' => ($row['is_active'] ?? '1') === '1',
                ]);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }
}
