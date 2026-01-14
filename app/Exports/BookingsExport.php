<?php

namespace App\Exports;

class BookingsExport
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Generate Excel-compatible file (Tab-separated values with .xls extension)
     * This approach doesn't require PhpSpreadsheet
     */
    public function download(string $filename)
    {
        // Use .xls extension for better Excel compatibility
        $filename = str_replace('.xlsx', '.xls', $filename);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (!empty($this->data)) {
            // Write headers
            fputcsv($output, array_keys($this->data[0]), "\t");

            // Write data rows
            foreach ($this->data as $row) {
                fputcsv($output, array_values($row), "\t");
            }
        }

        fclose($output);
        exit;
    }
}
