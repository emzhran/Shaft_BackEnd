<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CustomerController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::get('cars', [CarController::class, 'index']);
Route::get('cars/{id}', [CarController::class, 'show']);
Route::get('maps', [MapController::class, 'index']);
Route::get('maps/{id}', [MapController::class, 'show']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('user-profile', [AuthController::class, 'userProfile']);

    Route::prefix('customer')->middleware('check_customer_ownership')->group(function () {
        Route::post('upload-identitas', [CustomerController::class, 'uploadIdentitas']);
        Route::get('profile', [CustomerController::class, 'getMyProfile']);

        // Rute untuk Pemesanan Customer
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders', [OrderController::class, 'myOrders']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::put('orders/{id}', [OrderController::class, 'update']);

        // Rute untuk Pesan Customer
        Route::post('messages/send', [MessageController::class, 'store']);
        Route::get('messages/my-messages', [MessageController::class, 'myMessages']);
    });

    Route::prefix('admin')->middleware('is_admin')->group(function () {
        Route::post('cars', [CarController::class, 'store']);
        Route::put('cars/{id}', [CarController::class, 'update']);
        Route::delete('cars/{id}', [CarController::class, 'destroy']);

        Route::post('maps', [MapController::class, 'store']);
        Route::put('maps/{id}', [MapController::class, 'update']);
        Route::delete('maps/{id}', [MapController::class, 'destroy']);

        Route::put('customers/{userId}/status', [CustomerController::class, 'updateStatusAkun']);
        Route::get('customers/{id}', [CustomerController::class, 'show']);
        Route::get('customers', [CustomerController::class, 'index']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::put('orders/{id}/status', [OrderController::class, 'updateOrderStatus']);

        Route::get('messages', [MessageController::class, 'index']);
        Route::post('messages/reply', [MessageController::class, 'store']);
    });
});