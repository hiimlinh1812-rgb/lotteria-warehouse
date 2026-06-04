<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NguyenLieuController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\DashboardController;

// 1. Đường dẫn đăng nhập
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');

// 2. Các trang cần đăng nhập mới vào được
Route::middleware(['auth', 'can:isCuaHangTruong'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('nguyen-lieu', NguyenLieuController::class);
    Route::resource('tai-khoan', TaiKhoanController::class);
});

require __DIR__.'/nhap_kho.php';