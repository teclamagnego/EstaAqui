<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ComercioAuthController;
use App\Http\Controllers\ComercioAdminController;
use App\Http\Middleware\ComercioAuth;
use Illuminate\Support\Facades\Route;

// ─── Endpoints Públicos ─────────────────────────────────────────
Route::get('/articulos', [ApiController::class, 'articulos']);
Route::get('/comercios', [ApiController::class, 'comercios']);
Route::get('/comercios/{slug}', [ApiController::class, 'comercioDetalle']);
Route::get('/categorias', [ApiController::class, 'categorias']);

// ─── Auth Comercio ──────────────────────────────────────────────
Route::post('/comercio/register', [ComercioAuthController::class, 'register']);
Route::post('/comercio/login', [ComercioAuthController::class, 'login']);
Route::post('/comercio/logout', [ComercioAuthController::class, 'logout']);
Route::get('/comercio/me', [ComercioAuthController::class, 'me']);

// ─── Panel Admin Comercio (protegido) ───────────────────────────
Route::middleware(ComercioAuth::class)->prefix('comercio')->group(function () {
    Route::get('/articulos', [ComercioAdminController::class, 'index']);
    Route::post('/articulos', [ComercioAdminController::class, 'store']);
    Route::put('/articulos/{id}', [ComercioAdminController::class, 'update']);
    Route::delete('/articulos/{id}', [ComercioAdminController::class, 'destroy']);
    Route::put('/perfil', [ComercioAdminController::class, 'updateProfile']);
});
