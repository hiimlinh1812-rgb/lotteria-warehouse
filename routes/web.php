<?php

use App\Http\Controllers\XuatKhoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('xuat_kho.tao_phieu');
});
//CHỨC NĂNG CỦA QUẢN LÝ
Route::prefix('quan-ly')->group(function () {
    // Màn hình danh sách phiếu xuất
    Route::get('/phieu-xuat', [XuatKhoController::class, 'index'])->name('xuatkho.index');

    // Màn hình khởi tạo yêu cầu xuất kho đầu ngày (Giao diện bạn gửi ảnh)
    Route::get('/tao-phieu-xuat', [XuatKhoController::class, 'create'])->name('xuatkho.create');

    // Xử lý logic khi Quản lý bấm nút "Xác Nhận Xuất Kho"
    Route::post('/tao-phieu-xuat', [XuatKhoController::class, 'store'])->name('xuatkho.store');
    //Xử lý Xem Phiếu Xuất
    Route::get('/quan-ly/chi-tiet-phieu/{id}', [App\Http\Controllers\XuatKhoController::class, 'quanLyShow'])->name('quanly.chitiet');
});

// --- NHÓM CHỨC NĂNG CỦA NHÂN VIÊN ---
Route::prefix('nhan-vien')->group(function () {
    // Màn hình tiếp nhận danh sách phiếu chờ xuất
    Route::get('/tiep-nhan-phieu', [XuatKhoController::class, 'nhanVienIndex'])->name('nhanvien.phieuxuat');

    // Màn hình chi tiết để nhân viên điền số lượng thực lấy
    Route::get('/chi-tiet-phieu/{id}', [XuatKhoController::class, 'show'])->name('nhanvien.chitiet');

    // Xử lý logic trừ kho FIFO khi nhân viên bấm "Hoàn tất"
    Route::post('/hoan-tat-phieu/{id}', [XuatKhoController::class, 'hoanTatXuatKho'])->name('nhanvien.hoantat');
});
