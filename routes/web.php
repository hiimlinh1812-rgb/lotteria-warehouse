<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NguyenLieuController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\TaiKhoanController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::redirect('/', '/purchase-orders');

Route::resource('purchase-orders', PurchaseOrderController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

Route::post('purchase-orders/{order}/process', [PurchaseOrderController::class, 'process'])
    ->name('purchase-orders.process');

Route::post('purchase-orders/{order}/approve', [PurchaseOrderController::class, 'approve'])
    ->name('purchase-orders.approve');

Route::post('purchase-orders/{order}/reject', [PurchaseOrderController::class, 'reject'])
    ->name('purchase-orders.reject');

Route::post('purchase-orders/{order}/cancel', [PurchaseOrderController::class, 'cancel'])
    ->name('purchase-orders.cancel');

Route::post('purchase-orders/{order}/receive', [PurchaseOrderController::class, 'receive'])
    ->name('purchase-orders.receive');

Route::post('purchase-orders/{order}/stock', [PurchaseOrderController::class, 'stock'])
    ->name('purchase-orders.stock');

Route::middleware(['auth', 'can:isCuaHangTruong'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('nguyen-lieu', NguyenLieuController::class);
    Route::resource('tai-khoan', TaiKhoanController::class);
});
