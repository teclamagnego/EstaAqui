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

Route::get('/manifest.json', function () {
    $icon = \App\Models\Setting::get('app_icon', '/icon-192.png');
    $name = \App\Models\Setting::get('app_name', 'EstaAqui');
    
    return response()->json([
        "name" => $name,
        "short_name" => $name,
        "start_url" => "/",
        "display" => "standalone",
        "background_color" => "#0a0a0a",
        "theme_color" => "#ff3d00",
        "icons" => [
            [
                "src" => $icon,
                "sizes" => "192x192",
                "type" => "image/png",
                "purpose" => "any maskable"
            ],
            [
                "src" => $icon,
                "sizes" => "512x512",
                "type" => "image/png",
                "purpose" => "any maskable"
            ]
        ]
    ]);
});

Route::get('/{any?}', function () {
    return view('spa');
})->where('any', '.*');
