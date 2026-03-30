<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ComercioAuthController;
use App\Http\Controllers\ComercioAdminController;
use App\Http\Middleware\ComercioAuth;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ─── Endpoints Públicos ─────────────────────────────────────────
Route::get('/articulos', [ApiController::class, 'articulos']);
Route::get('/comercios', [ApiController::class, 'comercios']);
Route::get('/comercios/{slug}', [ApiController::class, 'comercioDetalle']);
Route::get('/categorias', [ApiController::class, 'categorias']);
Route::get('/settings', [AdminController::class, 'getSettings']);

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
    Route::delete('/articulos/{articuloId}/imagenes/{imagenId}', [ComercioAdminController::class, 'deleteImagen']);
    Route::put('/perfil', [ComercioAdminController::class, 'updateProfile']);
    
    // Excel Import endpoints
    Route::get('/articulos/ejemplo-excel', [ComercioAdminController::class, 'downloadExcelExample']);
    Route::post('/articulos/importar-excel', [ComercioAdminController::class, 'importExcel']);
    
    // Informes
    Route::get('/informes', [ComercioAdminController::class, 'informes']);
});

// ─── Tracking ──────────────────────────────────────────────
Route::post('/track-click', [ApiController::class, 'trackClick']);

// ─── Super Admin (Web Guard) ────────────────────────────────────
use App\Http\Middleware\AdminAuth;

Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout']);
Route::get('/admin/me', [AdminController::class, 'me']);

Route::middleware(AdminAuth::class)->prefix('admin')->group(function () {
    Route::get('/comercios', [AdminController::class, 'comercios']);
    Route::post('/comercios/{id}/toggle-status', [AdminController::class, 'toggleComercioStatus']);
    Route::delete('/comercios/{id}', [AdminController::class, 'deleteComercio']);
    Route::post('/comercios/{id}/reset-password', [AdminController::class, 'resetComercioPassword']);
    Route::post('/comercios/{id}/update-orden', [AdminController::class, 'updateOrden']);
    Route::get('/comercios/{id}/informes', [AdminController::class, 'comercioInformes']);
    Route::get('/comercios/{id}/articulos', [AdminController::class, 'comercioArticulos']);
    Route::get('/settings', [AdminController::class, 'getSettings']);
    Route::post('/settings', [AdminController::class, 'updateSettings']);
    Route::post('/settings/icon', [AdminController::class, 'updateIcon']);
});
