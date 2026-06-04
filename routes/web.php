<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NguyenLieuController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\DonHangNVController;
use App\Http\Controllers\GiaiTrinhController;
use App\Http\Controllers\KiemKeController;
use App\Http\Controllers\KiemKeDinhKyController;
use App\Http\Controllers\KiemKeNgayController;
use App\Http\Controllers\PhieuXuatController;
use App\Http\Controllers\XuatKhoController;
use App\Http\Controllers\XuatHuyController;


// 1. Đăng nhập/Đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// 2. TẤT CẢ ĐÃ ĐĂNG NHẬP THÌ ĐỀU VÀO ĐƯỢC DASHBOARD
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// 3. RIÊNG CHT (Chỉ CHT mới vào được trang tài khoản và nguyên liệu)
Route::middleware(['auth', 'can:isCuaHangTruong'])->group(function () {
    Route::resource('nguyen-lieu', NguyenLieuController::class);
    Route::resource('tai-khoan', TaiKhoanController::class);
});

// 4. Route cho Quản lý
Route::middleware(['auth', 'can:isQuanLy'])->group(function () {
    Route::get('/don-hang', [DonHangController::class, 'index'])->name('don-hang.index');
    Route::get('/xuat-kho', [XuatKhoController::class, 'index'])->name('xuat-kho.index');
    Route::get('/xuat-huy', [XuatHuyController::class, 'index'])->name('xuat-huy.index');
    Route::get('/kiem-ke', [KiemKeController::class, 'index'])->name('kiem-ke.index');
    Route::get('/giai-trinh', [GiaiTrinhController::class, 'index'])->name('giai-trinh.index');
});

// 5. Route cho Nhân viên
Route::middleware(['auth', 'can:isNhanVien'])->group(function () {
    Route::get('/phieu-xuat', [PhieuXuatController::class, 'index'])->name('phieu-xuat.index');
    Route::get('/ds-don-hang', [DonHangNVController::class, 'index'])->name('ds-don-hang.index');
    Route::get('/kiem-ke-ngay', [KiemKeNgayController::class, 'index'])->name('kiem-ke-ngay.index');
    Route::get('/kiem-ke-dinh-ky', [KiemKeDinhKyController::class, 'index'])->name('kiem-ke-dinh-ky.index');
});