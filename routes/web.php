<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NguyenLieuController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\TaiKhoanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    return match ($user->VaiTro) {
        'Quan ly', 'Quản lý', 'Cua hang truong', 'Cửa hàng trưởng' => redirect()->route('dashboard'),
        default => redirect()->route('purchase-orders.index'),
    };
});

Route::middleware('auth')->group(function () {
    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

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
});

Route::middleware(['auth', 'can:isQuanLy'])->prefix('don-hang')->name('don-hang.')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/tao-don', [PurchaseOrderController::class, 'create'])->name('create');
    Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{order}', [PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{order}/doi-tra', [PurchaseOrderController::class, 'returnForm'])->name('return.create');
    Route::post('/{order}/doi-tra', [PurchaseOrderController::class, 'storeReturn'])->name('return.store');
    Route::get('/{order}/nhap-kho', [PurchaseOrderController::class, 'stockForm'])->name('stock.create');
    Route::post('/{order}/nhap-kho', [PurchaseOrderController::class, 'stockFromForm'])->name('stock.store');
});

Route::middleware(['auth', 'can:isManagementUser'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/xuat-kho', [DashboardController::class, 'module'])->defaults('module', 'xuat-kho')->name('xuatkho.index');
    Route::get('/xuat-huy', [DashboardController::class, 'module'])->defaults('module', 'xuat-huy')->name('xuathuy.index');
    Route::get('/kiem-ke', [DashboardController::class, 'module'])->defaults('module', 'kiem-ke')->name('kiemke.index');
    Route::get('/giai-trinh', [DashboardController::class, 'module'])->defaults('module', 'giai-trinh')->name('giaitrinh.index');
});

Route::middleware(['auth', 'can:isCuaHangTruong'])->group(function () {
    Route::resource('nguyen-lieu', NguyenLieuController::class);
    Route::resource('tai-khoan', TaiKhoanController::class);
});
