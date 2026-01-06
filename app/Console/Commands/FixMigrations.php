<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixMigrations extends Command
{
    protected $signature = 'migrations:fix {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Add hasTable checks to all create table migrations for import compatibility';

    public function handle()
    {
        $migrationsPath = database_path('migrations');
        $files = File::glob($migrationsPath . '/*.php');
        $fixed = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $content = File::get($file);
            $basename = basename($file);

            // Skip if already has hasTable check for create
            if (str_contains($content, 'Schema::hasTable')) {
                $this->line("Skipped (already fixed): {$basename}");
                $skipped++;
                continue;
            }

            // Only process files with Schema::create
            if (!str_contains($content, 'Schema::create(')) {
                $this->line("Skipped (no Schema::create): {$basename}");
                $skipped++;
                continue;
            }

            // Fix: wrap each Schema::create with hasTable check
            $newContent = $this->addHasTableChecks($content);

            if ($newContent !== $content) {
                if ($this->option('dry-run')) {
                    $this->info("Would fix: {$basename}");
                } else {
                    File::put($file, $newContent);
                    $this->info("Fixed: {$basename}");
                }
                $fixed++;
            } else {
                $this->line("Skipped (no changes needed): {$basename}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Summary: Fixed {$fixed} files, Skipped {$skipped} files");

        return self::SUCCESS;
    }

    protected function addHasTableChecks(string $content): string
    {
        // Pattern to match Schema::create('table_name', function ...
        $pattern = '/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*function\s*\(\s*Blueprint\s+\$(\w+)\s*\)\s*\{/';

        // Replace each Schema::create with hasTable check
        $newContent = preg_replace_callback($pattern, function ($matches) {
            $tableName = $matches[1];
            $varName = $matches[2];
            return "if (!Schema::hasTable('{$tableName}')) {\n            Schema::create('{$tableName}', function (Blueprint \${$varName}) {";
        }, $content);

        // Add closing bracket for the if statement after each Schema::create block's closing
        // This is more complex, so we'll use a different approach
        // For each replacement, we need to add a closing } after the create block

        // Count number of Schema::create replaced
        preg_match_all($pattern, $content, $matches);
        $createCount = count($matches[0]);

        if ($createCount > 0) {
            // Find all });  that close Schema::create and add closing braces for if
            // This is tricky, let's just add them at the end of each create block

            // Pattern: find "});" that follows after table definitions
            // We need to be careful to not break nested structures

            // Simpler approach: replace "}); at end of Schema::create blocks
            // Look for pattern like:
            //     $table->...;
            // });

            // For now, let's use a simpler replacement that wraps properly
            $newContent = $this->wrapSchemaCreates($content);
        }

        return $newContent;
    }

    protected function wrapSchemaCreates(string $content): string
    {
        // More reliable approach using line-by-line processing
        $lines = explode("\n", $content);
        $newLines = [];
        $inSchemaCreate = false;
        $braceDepth = 0;
        $createStartLine = -1;
        $currentTableName = '';

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Check if this line starts a Schema::create
            if (preg_match('/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
                $currentTableName = $matches[1];

                // Check if already wrapped with hasTable
                if ($i > 0 && str_contains($lines[$i - 1], 'Schema::hasTable')) {
                    $newLines[] = $line;
                    continue;
                }

                // Get indentation
                preg_match('/^(\s*)/', $line, $indentMatch);
                $indent = $indentMatch[1] ?? '        ';

                // Add hasTable check before this line
                $newLines[] = $indent . "if (!Schema::hasTable('{$currentTableName}')) {";
                $inSchemaCreate = true;
                $createStartLine = $i;
                $braceDepth = 0;
            }

            $newLines[] = $line;

            // Track opening braces in Schema::create
            if ($inSchemaCreate) {
                $braceDepth += substr_count($line, '{');
                $braceDepth -= substr_count($line, '}');

                // If we're back to 0, we've closed the Schema::create
                if ($braceDepth <= 0 && str_contains($line, '});')) {
                    preg_match('/^(\s*)/', $line, $indentMatch);
                    $indent = $indentMatch[1] ?? '        ';
                    $newLines[] = $indent . "}";
                    $inSchemaCreate = false;
                    $currentTableName = '';
                }
            }
        }

        return implode("\n", $newLines);
    }
}
