<?php
// Debug - Test ACTUAL import from OLD backup with better error handling
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$sqlFile = 'backup_2025-12-24_193921.sql';
$sqlContent = file_get_contents($sqlFile);

$service = app(\App\Services\Admin\DatabaseImportService::class);
$reflectionClass = new ReflectionClass($service);
$parseMethod = $reflectionClass->getMethod('parseSQLStatements');
$parseMethod->setAccessible(true);
$filterMethod = $reflectionClass->getMethod('filterInsertColumns');
$filterMethod->setAccessible(true);

$statements = $parseMethod->invoke($service, $sqlContent);

echo "=== Testing Import from OLD Backup (Dec 24) ===\n\n";

// Count before
echo "Before import:\n";
$tables = ['admins', 'users', 'destinations', 'articles', 'banners'];
foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        echo "  $t: " . DB::table($t)->count() . " records\n";
    }
}

$insertCount = 0;
$skippedCount = 0;
$errors = [];
$systemTables = ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

echo "\n=== Processing INSERT Statements ===\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');

foreach ($statements as $statement) {
    $statement = trim($statement);

    if (empty($statement))
        continue;
    if (str_starts_with($statement, '--') || str_starts_with($statement, '/*'))
        continue;

    // Only process INSERT statements
    $isInsertStatement = (stripos($statement, 'INSERT INTO') !== false) ||
        (stripos($statement, 'INSERT IGNORE') !== false);

    if (!$isInsertStatement) {
        continue;
    }

    // Extract table name
    if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?/i', $statement, $matches)) {
        $tableName = $matches[1];

        // Skip system tables
        if (in_array($tableName, $systemTables)) {
            $skippedCount++;
            continue;
        }

        // Check if table exists
        if (!Schema::hasTable($tableName)) {
            $skippedCount++;
            continue;
        }

        // Get current table columns
        $tableColumns = Schema::getColumnListing($tableName);

        // Filter statement
        $filteredStatement = $filterMethod->invoke($service, $statement, $tableColumns);

        if ($filteredStatement) {
            try {
                DB::unprepared($filteredStatement);
                $insertCount++;
            } catch (\Exception $e) {
                $errors[] = "Table {$tableName}: " . substr($e->getMessage(), 0, 100);
                $skippedCount++;
            }
        } else {
            $skippedCount++;
        }
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "Insert count: {$insertCount}\n";
echo "Skipped count: {$skippedCount}\n";

if (!empty($errors)) {
    echo "\nFirst 5 errors:\n";
    foreach (array_slice($errors, 0, 5) as $e) {
        echo "  - $e\n";
    }
}

echo "\nAfter import:\n";
foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        echo "  $t: " . DB::table($t)->count() . " records\n";
    }
}
