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

    // --- BÊ CODE CỦA BẠN VÀO ĐÂY ---
    Route::prefix('quan-ly')->group(function () {
        // Màn hình danh sách phiếu xuất
        Route::get('/phieu-xuat', [XuatKhoController::class, 'index'])->name('xuatkho.index');
        // Màn hình khởi tạo yêu cầu xuất kho đầu ngày
        Route::get('/tao-phieu-xuat', [XuatKhoController::class, 'create'])->name('xuatkho.create');
        // Xử lý logic khi Quản lý bấm nút "Xác Nhận Xuất Kho"
        Route::post('/tao-phieu-xuat', [XuatKhoController::class, 'store'])->name('xuatkho.store');
        // Xử lý Xem Phiếu Xuất (Đã bỏ chữ /quan-ly dư thừa vì đã có prefix ở ngoài)
        Route::get('/chi-tiet-phieu/{id}', [XuatKhoController::class, 'quanLyShow'])->name('quanly.chitiet');
    });
    // -------------------------------

    Route::get('/xuat-huy', [XuatHuyController::class, 'index'])->name('xuat-huy.index');
    Route::get('/kiem-ke', [KiemKeController::class, 'index'])->name('kiem-ke.index');
    Route::get('/giai-trinh', [GiaiTrinhController::class, 'index'])->name('giai-trinh.index');
});

// 5. Route cho Nhân viên
Route::middleware(['auth', 'can:isNhanVien'])->group(function () {

    // --- BÊ CODE CỦA BẠN VÀO ĐÂY ---
    Route::prefix('nhan-vien')->group(function () {
        // Màn hình tiếp nhận danh sách phiếu chờ xuất
        Route::get('/tiep-nhan-phieu', [XuatKhoController::class, 'nhanVienIndex'])->name('nhanvien.phieuxuat');
        // Màn hình chi tiết để nhân viên điền số lượng thực lấy
        Route::get('/chi-tiet-phieu/{id}', [XuatKhoController::class, 'show'])->name('nhanvien.chitiet');
        // Xử lý logic trừ kho FIFO khi nhân viên bấm "Hoàn tất"
        Route::post('/hoan-tat-phieu/{id}', [XuatKhoController::class, 'hoanTatXuatKho'])->name('nhanvien.hoantat');
    });
    // -------------------------------

    Route::get('/ds-don-hang', [DonHangNVController::class, 'index'])->name('ds-don-hang.index');
    Route::get('/kiem-ke-ngay', [KiemKeNgayController::class, 'index'])->name('kiem-ke-ngay.index');
    Route::get('/kiem-ke-dinh-ky', [KiemKeDinhKyController::class, 'index'])->name('kiem-ke-dinh-ky.index');
});
