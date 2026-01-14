<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileManagerController extends Controller
{
    public function index()
    {
        $files = Upload::latest()->paginate(20);
        return Inertia::render('Admin/FileManager/Index', ['files' => $files]);
    }

    public function destroy(Upload $upload)
    {
        // Try to delete from storage if possible
        // Note: Our ImageService stores in DB, but might have backups or we just delete record
        $upload->delete();

        return back()->with('success', 'File berhasil dihapus');
    }
}
