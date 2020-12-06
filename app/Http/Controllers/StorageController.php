<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function download(Request $request)
    {
        $pathFile = $request->get('path');
        if ($pathFile) {
            if (Storage::exists($pathFile)) {
                return Storage::download($pathFile);
            }
        }
        abort(404);
    }
}
