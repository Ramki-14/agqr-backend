<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function downloadFile($filePath)
    {
        // Check if the file exists in the public disk
        if (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
            return response()->file($fullPath, [
                'Access-Control-Allow-Origin' => 'http://localhost:5173'
            ]);
        }
    
        return response()->json(['error' => 'File not found'], 404);
    }
}
