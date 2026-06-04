<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\KiemKeBepController;

// ============================================================
// ĐỊNH TUYẾN PHÂN HỆ KIỂM KÊ KHO BẾP
// ============================================================
// Vai trò Nhân viên bếp
Route::get('/kiem-ke-bep', [KiemKeBepController::class, 'index'])->name('kiemke.bep');
Route::post('/kiem-ke-bep/store', [KiemKeBepController::class, 'store'])->name('kiemke.bep.store');

// Vai trò Quản lý ca bếp (Xem đối soát, Từ chối, Chốt ca)
Route::get('/quan-ly/kiem-ke-bep', [KiemKeBepController::class, 'danhSachBaoCao'])->name('quanly.kiemke.bep');
Route::post('/quan-ly/kiem-ke-bep/tu-choi/{maPhieu}', [KiemKeBepController::class, 'tuChoiBaoCao'])->name('quanly.kiemke.tuchoi');
Route::post('/quan-ly/kiem-ke-bep/chot-ca/{maPhieu}', [KiemKeBepController::class, 'chotCaBaoCao'])->name('quanly.chotca');

use App\Http\Controllers\KiemKeKhoChinhController;

// Tuyến đường dành cho Nhân viên lập phiếu kho chính
Route::get('/kho-chinh/kiem-ke', [KiemKeKhoChinhController::class, 'index'])->name('khochinh.kiemke');
Route::post('/kho-chinh/kiem-ke/store', [KiemKeKhoChinhController::class, 'store'])->name('khochinh.kiemke.store');

// Tuyến đường dành cho Quản lý duyệt và xử lý rẽ nhánh đối soát
Route::get('/quan-ly/kho-chinh/duyet', [KiemKeKhoChinhController::class, 'danhSachDuyet'])->name('quanly.khochinh.duyet');
Route::post('/quan-ly/kho-chinh/hieu-chinh/{maPhieu}', [KiemKeKhoChinhController::class, 'hieuChinhPhieu'])->name('quanly.khochinh.hieuchinh');
Route::post('/quan-ly/kho-chinh/giai-trinh/{maPhieu}', [KiemKeKhoChinhController::class, 'taoGiaiTrinh'])->name('quanly.khochinh.giaitrinh');