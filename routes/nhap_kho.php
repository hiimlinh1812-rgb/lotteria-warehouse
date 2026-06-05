<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhieuNhanHangController;
use App\Http\Controllers\PhieuDoiTraController;
use App\Http\Controllers\PhieuNhapKhoController;

/*
|--------------------------------------------------------------------------
| Routes - Module: Tiếp nhận, Đổi trả & Nhập kho thực tế
|--------------------------------------------------------------------------
| Đăng ký file này trong routes/web.php:
|   require __DIR__.'/nhap_kho.php';
|
| Middleware 'check.vai.tro.nhap.kho' cần đăng ký trong:
|   - Laravel 11: bootstrap/app.php  →  withMiddleware(...)
|   - Laravel 10: app/Http/Kernel.php  →  $routeMiddleware
|--------------------------------------------------------------------------
*/

// ── Phiếu Nhận Hàng ──────────────────────────────────────────────────────
// Nhân viên trở lên đều truy cập được
Route::prefix('phieu-nhan-hang')->name('phieu-nhan-hang.')
     ->middleware(['check.vai.tro.nhap.kho:nhan_vien'])
     ->group(function () {
    Route::get('/',                    [PhieuNhanHangController::class, 'index'])       ->name('index');
    Route::get('/{id}',                [PhieuNhanHangController::class, 'show'])        ->name('show');
    Route::get('/{id}/nhap-so-luong',  [PhieuNhanHangController::class, 'nhapSoLuong'])->name('nhap-so-luong');
    Route::post('/{id}/nhap-so-luong', [PhieuNhanHangController::class, 'luuSoLuong']) ->name('luu-so-luong');
});

// ── Phiếu Đổi Trả ────────────────────────────────────────────────────────
// Chỉ Quản lý / Cửa hàng trưởng
Route::prefix('phieu-doi-tra')->name('phieu-doi-tra.')
     ->middleware(['check.vai.tro.nhap.kho:quan_ly'])
     ->group(function () {
    Route::get('/',                     [PhieuDoiTraController::class, 'index'])       ->name('index');
    Route::get('/tao/{maPhieuNhan}',    [PhieuDoiTraController::class, 'create'])      ->name('create');
    Route::post('/tao/{maPhieuNhan}',   [PhieuDoiTraController::class, 'store'])       ->name('store');
    Route::get('/{id}',                 [PhieuDoiTraController::class, 'show'])        ->name('show');
    Route::post('/{id}/cap-nhat-xu-ly', [PhieuDoiTraController::class, 'capNhatXuLy'])->name('cap-nhat-xu-ly');
});

// ── Phiếu Nhập Kho ───────────────────────────────────────────────────────
// Chỉ Quản lý / Cửa hàng trưởng
Route::prefix('phieu-nhap-kho')->name('phieu-nhap-kho.')
     ->middleware(['check.vai.tro.nhap.kho:quan_ly'])
     ->group(function () {
    Route::get('/',                  [PhieuNhapKhoController::class, 'index'])  ->name('index');
    Route::get('/tao/{maPhieuNhan}', [PhieuNhapKhoController::class, 'create']) ->name('create');
    Route::post('/tao/{maPhieuNhan}',[PhieuNhapKhoController::class, 'store'])  ->name('store');
    Route::get('/{id}',              [PhieuNhapKhoController::class, 'show'])   ->name('show');
    Route::get('/{id}/bao-cao',      [PhieuNhapKhoController::class, 'baoCao'])->name('bao-cao');
});
