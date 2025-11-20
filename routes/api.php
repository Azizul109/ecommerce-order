<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/debug', function () {
        return response()->json([
            'message' => 'API V1 is working!',
            'timestamp' => now(),
            'version' => '1.0'
        ]);
    });

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/{product}', [ProductController::class, 'show']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/debug-protected', function (Request $request) {
            return response()->json([
                'message' => 'Protected route is working!',
                'user' => $request->user(),
                'timestamp' => now()
            ]);
        });

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);

        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::post('products/import', [ProductController::class, 'import']);
        Route::get('products/low-stock/alerts', [ProductController::class, 'lowStock']);

        Route::apiResource('orders', OrderController::class);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    });
});