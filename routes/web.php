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
    $icon192 = \App\Models\Setting::get('app_icon', '/icon-192.png');
    $icon512 = \App\Models\Setting::get('app_icon', '/icon-512.png');
    $name = \App\Models\Setting::get('app_name', 'EstaAqui');
    
    return response()->json([
        "name" => $name,
        "short_name" => $name,
        "start_url" => "/",
        "display" => "standalone",
        "background_color" => "#ea580c",
        "theme_color" => "#ea580c",
        "icons" => [
            [
                "src" => $icon192,
                "sizes" => "192x192",
                "type" => "image/png",
                "purpose" => "any"
            ],
            [
                "src" => $icon512,
                "sizes" => "512x512",
                "type" => "image/png",
                "purpose" => "any"
            ],
            [
                "src" => $icon192,
                "sizes" => "192x192",
                "type" => "image/png",
                "purpose" => "maskable"
            ]
        ]
    ])->header('Content-Type', 'application/manifest+json');
});

Route::get('/{any?}', function () {
    return view('spa');
})->where('any', '.*');
