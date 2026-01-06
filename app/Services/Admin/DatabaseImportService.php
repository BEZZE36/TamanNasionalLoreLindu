<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;
use App\Models\Destination;
use App\Models\User;
use App\Models\Flora;
use App\Models\Fauna;

/**
 * DatabaseImportService
 * Handles CSV import for various data types
 */
class DatabaseImportService
{
    /**
     * Template definitions for each import type
     */
    protected array $templates = [
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
        'users' => ['name', 'email', 'phone', 'address'],
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

    /**
     * Get template for a specific type
     */
    public function getTemplate(string $type): ?array
    {
        return $this->templates[$type] ?? null;
    }

    /**
     * Generate template CSV download response
     */
    public function downloadTemplate(string $type)
    {
        $template = $this->getTemplate($type);

        if (!$template) {
            abort(404);
        }

        $filename = "template_{$type}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($template) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $template);
            $sample = array_fill(0, count($template), 'contoh data');
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Parse CSV file to array
     */
    public function parseCSV($file): array
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

        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Import data based on type
     */
    public function import(string $type, array $data): array
    {
        $result = match ($type) {
            'destinations' => $this->importDestinations($data),
            'users' => $this->importUsers($data),
            'flora' => $this->importFlora($data),
            'fauna' => $this->importFauna($data),
            default => ['success' => 0, 'failed' => 0, 'errors' => ['Unknown type']]
        };

        ActivityLog::log('import', "Import {$result['success']} {$type} dari CSV");

        return $result;
    }

    /**
     * Import destinations
     */
    protected function importDestinations(array $data): array
    {
        return $this->processImport($data, [
            'validator' => ['name' => 'required|string|max:255', 'slug' => 'nullable|string|unique:destinations,slug'],
            'model' => Destination::class,
            'fields' => ['name', 'slug', 'city', 'short_description', 'description', 'address', 'latitude', 'longitude', 'ticket_price', 'operating_hours', 'is_active'],
            'defaults' => [
                'slug' => fn($row) => $row['slug'] ?? \Str::slug($row['name']),
                'ticket_price' => 0,
                'is_active' => fn($row) => ($row['is_active'] ?? '1') === '1',
            ]
        ]);
    }

    /**
     * Import users
     */
    protected function importUsers(array $data): array
    {
        return $this->processImport($data, [
            'validator' => ['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email'],
            'model' => User::class,
            'fields' => ['name', 'email', 'phone', 'address'],
            'extras' => ['password' => bcrypt('password123')]
        ]);
    }

    /**
     * Import flora
     */
    protected function importFlora(array $data): array
    {
        return $this->processImport($data, [
            'validator' => ['name' => 'required|string|max:255'],
            'model' => Flora::class,
            'fields' => ['name', 'scientific_name', 'description', 'habitat', 'conservation_status', 'is_active'],
            'defaults' => ['is_active' => fn($row) => ($row['is_active'] ?? '1') === '1']
        ]);
    }

    /**
     * Import fauna
     */
    protected function importFauna(array $data): array
    {
        return $this->processImport($data, [
            'validator' => ['name' => 'required|string|max:255'],
            'model' => Fauna::class,
            'fields' => ['name', 'scientific_name', 'description', 'habitat', 'conservation_status', 'is_active'],
            'defaults' => ['is_active' => fn($row) => ($row['is_active'] ?? '1') === '1']
        ]);
    }

    /**
     * Generic import processor
     */
    protected function processImport(array $data, array $config): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            $validator = Validator::make($row, $config['validator']);

            if ($validator->fails()) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                $insertData = [];
                foreach ($config['fields'] as $field) {
                    if (isset($config['defaults'][$field])) {
                        $default = $config['defaults'][$field];
                        $insertData[$field] = is_callable($default) ? $default($row) : $default;
                    } else {
                        $insertData[$field] = $row[$field] ?? null;
                    }
                }

                if (!empty($config['extras'])) {
                    $insertData = array_merge($insertData, $config['extras']);
                }

                $config['model']::create($insertData);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Import database from SQL backup file
     * This method will execute the SQL statements from the backup file
     * replacing existing data in the database
     */
    public function importSQL($file): array
    {
        $path = $file->getRealPath();
        $sqlContent = file_get_contents($path);

        if (empty($sqlContent)) {
            throw new \Exception('File SQL kosong atau tidak dapat dibaca.');
        }

        $tablesCount = 0;

        DB::beginTransaction();

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Split SQL content into individual statements
            // Handle multi-line statements properly
            $statements = $this->parseSQLStatements($sqlContent);

            foreach ($statements as $statement) {
                $statement = trim($statement);

                if (empty($statement)) {
                    continue;
                }

                // Skip comments
                if (str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
                    continue;
                }

                // Skip migration-related inserts - we'll rebuild this fresh
                if (stripos($statement, 'INSERT INTO `migrations`') !== false) {
                    continue;
                }

                // Count CREATE TABLE statements
                if (stripos($statement, 'CREATE TABLE') !== false) {
                    $tablesCount++;

                    // Extract table name and drop it first
                    if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                        $tableName = $matches[1];
                        DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                    }
                }

                // Execute the statement
                DB::unprepared($statement);
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            // Clear migrations table completely to ensure fresh migration tracking
            // This is critical for old backups that may have outdated migration records
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            if (\Schema::hasTable('migrations')) {
                DB::table('migrations')->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Run ALL migrations fresh to ensure schema is completely up-to-date
            // This adds any missing columns/tables/indexes from newer migrations
            $migrateOutput = \Artisan::call('migrate', ['--force' => true]);
            $migrateResult = \Artisan::output();

            ActivityLog::log('import', "Import database dari file SQL: {$tablesCount} tabel diproses, migrasi dijalankan");

            return [
                'tables_count' => $tablesCount,
                'migrations_run' => true,
                'migrate_output' => $migrateResult
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    /**
     * Import data only from SQL backup file (preserves current schema)
     * Only processes INSERT statements and skips CREATE TABLE, DROP TABLE, etc.
     * This is useful when importing old backups to get data without losing new schema
     */
    public function importSQLDataOnly($file, array $options = []): array
    {
        $path = $file->getRealPath();
        $sqlContent = file_get_contents($path);

        if (empty($sqlContent)) {
            throw new \Exception('File SQL kosong atau tidak dapat dibaca.');
        }

        $insertCount = 0;
        $skippedCount = 0;
        $errors = [];
        $clearExisting = $options['clear_existing'] ?? false;

        DB::beginTransaction();

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Split SQL content into individual statements
            $statements = $this->parseSQLStatements($sqlContent);

            // Get list of tables to clear if requested
            $tablesToClear = [];

            foreach ($statements as $statement) {
                $statement = trim($statement);

                if (empty($statement)) {
                    continue;
                }

                // Skip comments
                if (str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
                    continue;
                }

                // Only process INSERT statements - use stripos !== false for flexible matching
                // The statement might have been combined from multiple lines
                $isInsertStatement = (stripos($statement, 'INSERT INTO') !== false) ||
                    (stripos($statement, 'INSERT IGNORE') !== false);

                if ($isInsertStatement) {
                    // Extract table name
                    if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                        $tableName = $matches[1];

                        // Skip system tables
                        if (in_array($tableName, ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'])) {
                            $skippedCount++;
                            continue;
                        }

                        // Check if table exists
                        if (!\Schema::hasTable($tableName)) {
                            $skippedCount++;
                            \Log::warning("Data-only import: Table {$tableName} does not exist, skipping");
                            continue;
                        }

                        // Clear table data if requested (only once per table)
                        if ($clearExisting && !in_array($tableName, $tablesToClear)) {
                            DB::table($tableName)->truncate();
                            $tablesToClear[] = $tableName;
                        }

                        // Try to execute the INSERT, filtering columns that don't exist
                        try {
                            // Get current table columns
                            $tableColumns = \Schema::getColumnListing($tableName);

                            // Parse and filter the INSERT statement
                            $filteredStatement = $this->filterInsertColumns($statement, $tableColumns);

                            if ($filteredStatement) {
                                DB::unprepared($filteredStatement);
                                $insertCount++;
                            } else {
                                $skippedCount++;
                                \Log::warning("Data-only import: Could not filter columns for table {$tableName}");
                            }
                        } catch (\Exception $e) {
                            // Log error but continue with other inserts
                            $errors[] = "Table {$tableName}: " . $e->getMessage();
                            $skippedCount++;
                            \Log::error("Data-only import error for {$tableName}: " . $e->getMessage());
                        }
                    } else {
                        $skippedCount++;
                        \Log::warning("Data-only import: Could not parse INSERT statement: " . substr($statement, 0, 100));
                    }
                } else {
                    // Skip non-INSERT statements (CREATE TABLE, DROP, ALTER, etc.)
                    $skippedCount++;
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            ActivityLog::log('import', "Import data dari file SQL: {$insertCount} insert berhasil, {$skippedCount} dilewati");

            return [
                'insert_count' => $insertCount,
                'skipped_count' => $skippedCount,
                'tables_cleared' => $tablesToClear,
                'errors' => $errors,
                'mode' => 'data_only'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    /**
     * Filter INSERT statement to only include columns that exist in current table
     */
    protected function filterInsertColumns(string $statement, array $validColumns): ?string
    {
        // First, check if this is a simple INSERT INTO table VALUES format (no column list)
        // mysqldump often uses this format: INSERT INTO `table` VALUES (...)
        if (preg_match('/^INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?\s+VALUES\s+/i', $statement)) {
            // No column list specified, just convert to REPLACE INTO
            return preg_replace('/^INSERT\s+(IGNORE\s+)?INTO/i', 'REPLACE INTO', $statement);
        }

        // Parse INSERT INTO `table` (`col1`, `col2`, ...) VALUES (val1, val2, ...)
        if (!preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*(.+)/is', $statement, $matches)) {
            // Can't parse but has INSERT, convert to REPLACE just in case
            return preg_replace('/^INSERT\s+(IGNORE\s+)?INTO/i', 'REPLACE INTO', $statement);
        }

        $tableName = $matches[1];
        $columnsStr = $matches[2];
        $valuesStr = $matches[3];

        // Parse column names
        preg_match_all('/`?(\w+)`?/', $columnsStr, $columnMatches);
        $columns = $columnMatches[1];

        // Find which columns to keep
        $keepIndices = [];
        $validColumnsLower = array_map('strtolower', $validColumns);

        foreach ($columns as $index => $col) {
            if (in_array(strtolower($col), $validColumnsLower)) {
                $keepIndices[] = $index;
            }
        }

        if (empty($keepIndices)) {
            return null;
        }

        // If all columns are valid, still need to convert to REPLACE INTO for duplicate handling
        if (count($keepIndices) === count($columns)) {
            // Convert INSERT INTO to REPLACE INTO to handle duplicates
            // REPLACE will delete existing row and insert new if duplicate
            $convertedStatement = preg_replace('/^INSERT\s+(IGNORE\s+)?INTO/i', 'REPLACE INTO', $statement);
            return $convertedStatement;
        }

        // Build new column list
        $newColumns = array_map(fn($i) => '`' . $columns[$i] . '`', $keepIndices);
        $newColumnsStr = implode(', ', $newColumns);

        // Parse values - use proper parser that handles parentheses in strings
        $valueSets = $this->parseValueSets($valuesStr);

        if (empty($valueSets)) {
            return null;
        }

        $newValueSets = [];
        foreach ($valueSets as $valueSet) {
            // Parse individual values from this set
            $values = $this->parseValueSet($valueSet);

            if (count($values) !== count($columns)) {
                \Log::warning("Data-only import: Column count mismatch - expected " . count($columns) . ", got " . count($values));
                continue; // Skip malformed rows
            }

            $newValues = array_map(fn($i) => $values[$i], $keepIndices);
            $newValueSets[] = '(' . implode(', ', $newValues) . ')';
        }

        if (empty($newValueSets)) {
            return null;
        }

        return "REPLACE INTO `{$tableName}` ({$newColumnsStr}) VALUES " . implode(', ', $newValueSets) . ';';
    }

    /**
     * Parse a value set from INSERT statement, handling quoted strings with commas
     */
    protected function parseValueSet(string $valueSet): array
    {
        $values = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $prevChar = '';

        for ($i = 0; $i < strlen($valueSet); $i++) {
            $char = $valueSet[$i];

            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
            } elseif ($inString && $char === $stringChar && $prevChar !== '\\') {
                $inString = false;
                $current .= $char;
            } elseif (!$inString && $char === ',') {
                $values[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }

            $prevChar = $char;
        }

        if (trim($current) !== '') {
            $values[] = trim($current);
        }

        return $values;
    }

    /**
     * Parse VALUES (...), (...), ... into separate value sets
     * Handles nested parentheses inside strings correctly
     */
    protected function parseValueSets(string $valuesStr): array
    {
        $valueSets = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $prevChar = '';
        $parenDepth = 0;

        for ($i = 0; $i < strlen($valuesStr); $i++) {
            $char = $valuesStr[$i];

            // Handle string start/end
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar && $prevChar !== '\\') {
                $inString = false;
            }

            // Track parentheses only outside strings
            if (!$inString) {
                if ($char === '(') {
                    $parenDepth++;
                    if ($parenDepth === 1) {
                        // Start of a value set, don't include the opening paren
                        $prevChar = $char;
                        continue;
                    }
                } elseif ($char === ')') {
                    $parenDepth--;
                    if ($parenDepth === 0) {
                        // End of a value set
                        if (trim($current) !== '') {
                            $valueSets[] = trim($current);
                        }
                        $current = '';
                        $prevChar = $char;
                        continue;
                    }
                } elseif ($char === ',' && $parenDepth === 0) {
                    // Comma between value sets, skip it
                    $prevChar = $char;
                    continue;
                }
            }

            if ($parenDepth > 0 || $inString) {
                $current .= $char;
            }

            $prevChar = $char;
        }

        return $valueSets;
    }

    /**
     * Parse SQL content into individual statements
     * Handles multi-line statements properly
     */
    protected function parseSQLStatements(string $sql): array
    {
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';
        $prevChar = '';

        $lines = explode("\n", $sql);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '--')) {
                continue;
            }

            $currentStatement .= $line . ' ';

            // Check if statement ends with semicolon (not inside a string)
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];

                if (!$inString && ($char === '"' || $char === "'")) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($inString && $char === $stringChar && $prevChar !== '\\') {
                    $inString = false;
                }

                $prevChar = $char;
            }

            // If line ends with semicolon and we're not in a string, this is a complete statement
            if (!$inString && substr(rtrim($line), -1) === ';') {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }

        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return $statements;
    }
}
