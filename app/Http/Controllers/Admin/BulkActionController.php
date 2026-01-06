<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\User;
use App\Models\Booking;
use App\Models\Flora;
use App\Models\Fauna;
use App\Models\Gallery;
use App\Models\Article;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    protected $models = [
        'destinations' => Destination::class,
        'users' => User::class,
        'bookings' => Booking::class,
        'flora' => Flora::class,
        'fauna' => Fauna::class,
        'gallery' => Gallery::class,
        'articles' => Article::class,
    ];

    public function delete(Request $request)
    {
        $request->validate([
            'model' => 'required|string|in:' . implode(',', array_keys($this->models)),
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $modelClass = $this->models[$request->model];
        $count = $modelClass::whereIn('id', $request->ids)->delete();

        ActivityLog::log('delete', "Bulk delete {$count} {$request->model}");

        return response()->json([
            'success' => true,
            'message' => "{$count} item berhasil dihapus.",
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'model' => 'required|string|in:' . implode(',', array_keys($this->models)),
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'status' => 'required|string',
            'field' => 'required|string|in:status,is_active,is_featured',
        ]);

        $modelClass = $this->models[$request->model];
        $value = $request->field === 'status' ? $request->status : ($request->status === '1' || $request->status === 'true');
        
        $count = $modelClass::whereIn('id', $request->ids)->update([
            $request->field => $value,
        ]);

        ActivityLog::log('update', "Bulk update {$request->field} for {$count} {$request->model}");

        return response()->json([
            'success' => true,
            'message' => "{$count} item berhasil diperbarui.",
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'model' => 'required|string|in:' . implode(',', array_keys($this->models)),
            'ids' => 'required|array|min:1',
        ]);

        $modelClass = $this->models[$request->model];
        $items = $modelClass::whereIn('id', $request->ids)->get();

        $filename = $request->model . '_export_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            if ($items->isNotEmpty()) {
                fputcsv($file, array_keys($items->first()->toArray()));
                foreach ($items as $item) {
                    fputcsv($file, $item->toArray());
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
