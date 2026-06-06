<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckVaiTroNhapKho
 *
 * Phân quyền cho module Tiếp nhận, Đổi trả & Nhập kho.
 * Cách dùng trong routes/nhap_kho.php:
 *   Route::middleware(['check.vai.tro.nhap.kho:quan_ly'])->group(...)
 *   Route::middleware(['check.vai.tro.nhap.kho:nhan_vien'])->group(...)
 *
 * Đăng ký trong bootstrap/app.php (Laravel 11) hoặc Kernel.php (Laravel 10):
 *   'check.vai.tro.nhap.kho' => \App\Http\Middleware\CheckVaiTroNhapKho::class,
 */
class CheckVaiTroNhapKho
{
    // Map alias → danh sách vai trò được phép
    private const VAI_TRO_MAP = [
        'quan_ly' => [
            'Cua hang truong',
            'Cửa hàng trưởng',
            'Quan ly',
            'Quản lý',
            'Quản lý cửa hàng',
        ],
        'nhan_vien' => [
            'Cua hang truong',
            'Cửa hàng trưởng',
            'Quan ly',
            'Quản lý',
            'Quản lý cửa hàng',
            'Nhan vien',
            'Nhân viên',
        ],
    ];

    public function handle(Request $request, Closure $next, string $level = 'nhan_vien'): Response
    {
        // Ưu tiên vai trò của tài khoản đang đăng nhập, fallback về session cũ nếu có.
        $vaiTro = auth()->user()->VaiTro ?? session('vai_tro');

        if (!$vaiTro) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $allowed = self::VAI_TRO_MAP[$level] ?? self::VAI_TRO_MAP['nhan_vien'];

        if (!in_array($vaiTro, $allowed)) {
            abort(403, 'Bạn không có quyền thực hiện chức năng này.');
        }

        return $next($request);
    }
}
