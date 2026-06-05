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
use App\Http\Controllers\KiemKeBepController;
use App\Http\Controllers\KiemKeKhoChinhController;

// 1. Đăng nhập/Đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');


// ============================================================
// 2. TẤT CẢ TÀI KHOẢN ĐÃ ĐĂNG NHẬP ĐỀU VÀO ĐƯỢC
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/giai-trinh', [GiaiTrinhController::class, 'index'])->name('giai-trinh.index');
    
    // Đmax THÊM: Route xem báo cáo lịch sử thống kê tồn kho sau khi đã duyệt chốt cho CHT và Quản lý
    Route::get('/dashboard/thong-ke-ton-kho', [KiemKeKhoChinhController::class, 'thongKeTonKho'])->name('cht.khochinh.thongke');
});


// ============================================================
// 3. NHÓM QUYỀN: CỬA HÀNG TRƯỞNG (CHT)
// ============================================================
Route::middleware(['auth', 'can:isCuaHangTruong'])->group(function () {
    Route::resource('nguyen-lieu', NguyenLieuController::class);
    Route::resource('tai-khoan', TaiKhoanController::class);
});


// ============================================================
// 4. NHÓM QUYỀN: QUẢN LÝ CA (Duyệt đối soát hệ thống)
// ============================================================
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

    // ─── PHÂN HỆ KIỂM KÊ KHO BẾP DÀNH CHO QUẢN LÝ ───
    Route::get('/quan-ly/kiem-ke-bep', [KiemKeBepController::class, 'danhSachBaoCao'])->name('quanly.kiemke.bep');
    Route::post('/quan-ly/kiem-ke-bep/tu-choi/{maPhieu}', [KiemKeBepController::class, 'tuChoiBaoCao'])->name('quanly.kiemke.tuchoi');
    Route::post('/quan-ly/kiem-ke-bep/chot-ca/{maPhieu}', [KiemKeBepController::class, 'chotCaBaoCao'])->name('quanly.chotca');

    // ─── PHÂN HỆ ĐỐI SOÁT KHO CHÍNH DÀNH CHO QUẢN LÝ ───
    Route::get('/quan-ly/kho-chinh/duyet', [KiemKeKhoChinhController::class, 'danhSachDuyet'])->name('quanly.khochinh.duyet');
    Route::post('/quan-ly/kho-chinh/hieu-chinh/{maPhieu}', [KiemKeKhoChinhController::class, 'hieuChinhPhieu'])->name('quanly.khochinh.hieuchinh');
    Route::post('/quan-ly/kho-chinh/duyet-truc-tiep/{maPhieu}', [KiemKeKhoChinhController::class, 'duyetPhieuTrucCtiep'])->name('quanly.khochinh.duyetXacNhan');
    Route::post('/quan-ly/kho-chinh/chuyen-huong-giai-trinh/{maPhieu}', [KiemKeKhoChinhController::class, 'chuyenHuongGiaiTrinh'])->name('quanly.khochinh.chuyenHuongGiaiTrinh');
    Route::get('/quan-ly/kho-chinh/giai-trinh-form/{maPhieu}', [KiemKeKhoChinhController::class, 'giaiTrinhForm'])->name('quanly.khochinh.giaiTrinhForm');
    Route::post('/quan-ly/kho-chinh/giai-trinh/store/{maPhieu}', [KiemKeKhoChinhController::class, 'taoGiaiTrinh'])->name('quanly.khochinh.giaitrinh');
});


// ============================================================
// 5. NHÓM QUYỀN: NHÂN VIÊN (Lập phiếu báo cáo)
// ============================================================
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

    // ─── LẬP BÁO CÁO KHO BẾP DÀNH CHO NHÂN VIÊN ───
    Route::get('/kiem-ke-bep', [KiemKeBepController::class, 'index'])->name('kiemke.bep');
    Route::post('/kiem-ke-bep/store', [KiemKeBepController::class, 'store'])->name('kiemke.bep.store');

    // ─── LẬP BÁO CÁO KHO CHÍNH DÀNH CHO NHÂN VIÊN ───
    Route::get('/kho-chinh/kiem-ke', [KiemKeKhoChinhController::class, 'index'])->name('khochinh.kiemke');
    Route::post('/kho-chinh/kiem-ke/store', [KiemKeKhoChinhController::class, 'store'])->name('khochinh.kiemke.store');
});
