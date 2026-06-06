<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonHangNVController;
use App\Http\Controllers\GiaiTrinhController;
use App\Http\Controllers\KiemKeBepController;
use App\Http\Controllers\KiemKeController;
use App\Http\Controllers\KiemKeDinhKyController;
use App\Http\Controllers\KiemKeKhoChinhController;
use App\Http\Controllers\KiemKeNgayController;
use App\Http\Controllers\NguyenLieuController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\XuatKhoController;
use App\Http\Controllers\XuatHuyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->VaiTro) {
        'Quan ly', 'Quản lý', 'Cua hang truong', 'Cửa hàng trưởng' => redirect()->route('dashboard'),
        'Nhan vien', 'Nhân viên' => redirect()->route('nhanvien.phieuxuat'),
        default => redirect()->route('purchase-orders.index'),
    };
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

Route::middleware(['auth', 'can:isManagementUser'])->group(function () {
    Route::get('/dashboard/thong-ke-ton-kho', [KiemKeKhoChinhController::class, 'thongKeTonKho'])->name('cht.khochinh.thongke');
    Route::get('/giai-trinh', [GiaiTrinhController::class, 'index'])->name('giai-trinh.index');
    Route::get('/xuat-huy', [XuatHuyController::class, 'index'])->name('xuat-huy.index');
});

Route::middleware(['auth', 'can:isCuaHangTruong'])->group(function () {
    Route::resource('nguyen-lieu', NguyenLieuController::class);
    Route::resource('tai-khoan', TaiKhoanController::class);
    Route::get('/xuat-huy/{id}', [XuatHuyController::class, 'show'])->name('xuat-huy.show');
});

require __DIR__ . '/nhap_kho.php';
// 4. Route cho Quản lý
Route::middleware(['auth', 'can:isQuanLy'])->group(function () {
    Route::prefix('don-hang')->name('don-hang.')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/tao-don', [PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{order}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::get('/{order}/sua', [PurchaseOrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [PurchaseOrderController::class, 'update'])->name('update');
        Route::post('/{order}/huy', [PurchaseOrderController::class, 'cancel'])->name('cancel');
        Route::get('/{order}/doi-tra', [PurchaseOrderController::class, 'returnForm'])->name('return.create');
        Route::post('/{order}/doi-tra', [PurchaseOrderController::class, 'storeReturn'])->name('return.store');
        Route::get('/{order}/nhap-kho', [PurchaseOrderController::class, 'stockForm'])->name('stock.create');
        Route::post('/{order}/nhap-kho', [PurchaseOrderController::class, 'stockFromForm'])->name('stock.store');
    });

    Route::prefix('quan-ly')->group(function () {
        Route::get('/phieu-xuat', [XuatKhoController::class, 'index'])->name('xuatkho.index');
        Route::get('/tao-phieu-xuat', [XuatKhoController::class, 'create'])->name('xuatkho.create');
        Route::post('/tao-phieu-xuat', [XuatKhoController::class, 'store'])->name('xuatkho.store');
        Route::get('/chi-tiet-phieu/{id}', [XuatKhoController::class, 'quanLyShow'])->name('quanly.chitiet');
    });

    Route::get('/kiem-ke', [KiemKeController::class, 'index'])->name('kiem-ke.index');

    Route::get('/quan-ly/kiem-ke-bep', [KiemKeBepController::class, 'danhSachBaoCao'])->name('quanly.kiemke.bep');
    Route::post('/quan-ly/kiem-ke-bep/tu-choi/{maPhieu}', [KiemKeBepController::class, 'tuChoiBaoCao'])->name('quanly.kiemke.tuchoi');
    Route::post('/quan-ly/kiem-ke-bep/chot-ca/{maPhieu}', [KiemKeBepController::class, 'chotCaBaoCao'])->name('quanly.chotca');

    Route::get('/quan-ly/kho-chinh/duyet', [KiemKeKhoChinhController::class, 'danhSachDuyet'])->name('quanly.khochinh.duyet');
    Route::post('/quan-ly/kho-chinh/hieu-chinh/{maPhieu}', [KiemKeKhoChinhController::class, 'hieuChinhPhieu'])->name('quanly.khochinh.hieuchinh');
    Route::post('/quan-ly/kho-chinh/duyet-truc-tiep/{maPhieu}', [KiemKeKhoChinhController::class, 'duyetPhieuTrucCtiep'])->name('quanly.khochinh.duyetXacNhan');
    Route::post('/quan-ly/kho-chinh/chuyen-huong-giai-trinh/{maPhieu}', [KiemKeKhoChinhController::class, 'chuyenHuongGiaiTrinh'])->name('quanly.khochinh.chuyenHuongGiaiTrinh');
    Route::get('/quan-ly/kho-chinh/giai-trinh-form/{maPhieu}', [KiemKeKhoChinhController::class, 'giaiTrinhForm'])->name('quanly.khochinh.giaiTrinhForm');
    Route::post('/quan-ly/kho-chinh/giai-trinh/store/{maPhieu}', [KiemKeKhoChinhController::class, 'taoGiaiTrinh'])->name('quanly.khochinh.giaitrinh');
});

Route::middleware(['auth', 'can:isNhanVien'])->group(function () {
    Route::prefix('nhan-vien')->group(function () {
        Route::get('/tiep-nhan-phieu', [XuatKhoController::class, 'nhanVienIndex'])->name('nhanvien.phieuxuat');
        Route::get('/chi-tiet-phieu/{id}', [XuatKhoController::class, 'show'])->name('nhanvien.chitiet');
        Route::post('/hoan-tat-phieu/{id}', [XuatKhoController::class, 'hoanTatXuatKho'])->name('nhanvien.hoantat');
    });

    Route::get('/ds-don-hang', [DonHangNVController::class, 'index'])->name('ds-don-hang.index');
    Route::get('/ds-don-hang/{order}', [DonHangNVController::class, 'show'])->name('ds-don-hang.show');
    Route::post('/ds-don-hang/{order}', [DonHangNVController::class, 'store'])->name('ds-don-hang.store');
    Route::get('/kiem-ke-ngay', [KiemKeNgayController::class, 'index'])->name('kiem-ke-ngay.index');
    Route::get('/kiem-ke-dinh-ky', [KiemKeDinhKyController::class, 'index'])->name('kiem-ke-dinh-ky.index');

    Route::get('/kiem-ke-bep', [KiemKeBepController::class, 'index'])->name('kiemke.bep');
    Route::post('/kiem-ke-bep/store', [KiemKeBepController::class, 'store'])->name('kiemke.bep.store');

    Route::get('/kho-chinh/kiem-ke', [KiemKeKhoChinhController::class, 'index'])->name('khochinh.kiemke');
    Route::post('/kho-chinh/kiem-ke/store', [KiemKeKhoChinhController::class, 'store'])->name('khochinh.kiemke.store');
});
