<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CancelOrderApprovedController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('user', [AuthController::class, 'getUser']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('order')->group(function () {
        Route::middleware('permission:show_orders')->get('show/{orderId}', [OrderController::class, 'show']);
        Route::middleware('permission:show_orders')->get('list', [OrderController::class, 'list']);
        Route::middleware('permission:store_orders')->post('store', [OrderController::class, 'store']);
        Route::middleware('permission:update_orders')->put('update', [OrderController::class, 'update']);
        Route::middleware('permission:delete_orders')->delete('delete', [OrderController::class, 'delete']);
        Route::middleware('role:admin')->put('update-status', [OrderController::class, 'updateStatus']);
    });

    Route::prefix('cancel-order')->group(function () {
        Route::middleware('permission:show_cancel_orders')->get('show/{id}', [CancelOrderApprovedController::class, 'show']);
        Route::middleware('permission:show_cancel_orders')->get('list', [CancelOrderApprovedController::class, 'list']);
        Route::middleware('permission:store_cancel_orders')->post('store', [CancelOrderApprovedController::class, 'store']);
        Route::middleware('role:admin')->put('update-status', [CancelOrderApprovedController::class, 'updateStatus']);
    });
});
