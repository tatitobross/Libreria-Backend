<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'active.user'])
    ->group(function () {
        Route::get('/user', [AuthController::class, 'me'])
            ->middleware('abilities:profile:read');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [CatalogController::class, 'dashboard']);

        foreach (['authors', 'categories', 'books'] as $catalog) {
            Route::get("/$catalog", [CatalogController::class, 'index'])->defaults('type', $catalog);
            Route::get("/$catalog/{id}", [CatalogController::class, 'show'])->defaults('type', $catalog);
            Route::middleware('role:Administrador')->group(function () use ($catalog) {
                Route::post("/$catalog", [CatalogController::class, 'store'])->defaults('type', $catalog);
                Route::put("/$catalog/{id}", [CatalogController::class, 'update'])->defaults('type', $catalog);
                Route::patch("/$catalog/{id}", [CatalogController::class, 'update'])->defaults('type', $catalog);
                Route::delete("/$catalog/{id}", [CatalogController::class, 'destroy'])->defaults('type', $catalog);
            });
        }

        // El vendedor consulta clientes únicamente para asociarlos a una venta.
        // Las altas, cambios y eliminaciones son funciones administrativas.
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::middleware('role:Administrador')->group(function () {
            Route::post('/customers', [CustomerController::class, 'store']);
            Route::put('/customers/{customer}', [CustomerController::class, 'update']);
            Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
        });
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
        Route::post('/sales', [SaleController::class, 'store']);
        Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->middleware('role:Administrador');
    });
