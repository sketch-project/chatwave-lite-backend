<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaticAssetController extends Controller
{
    public function index(Request $request, $path): StreamedResponse
    {
        if (!Storage::exists($path)) {
            abort(404);
        }

        if ($request->query('download')) {
            return Storage::download($path);
        }

        return Storage::response($path);
    }
}
