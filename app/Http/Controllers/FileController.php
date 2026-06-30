<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function produk($filename)
    {
        $path = 'produk/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function bukti($filename)
    {
        $path = 'bukti_pembayaran/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Access-Control-Allow-Origin', '*');
    }
}
