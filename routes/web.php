<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/fotos/{idcomercio}/{filename}', function ($idcomercio, $filename) {
    if (!preg_match('/^[0-9]+$/', $idcomercio) || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        abort(404);
    }
    
    $path = "fotos/{$idcomercio}/{$filename}";
    
    if (!Storage::disk('local')->exists($path)) {
        abort(404);
    }

    return response()->file(storage_path("app/{$path}"));
});

Route::get('/{any?}', function () {
    return view('spa');
})->where('any', '.*');
