<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký alias 'role' cho Middleware vừa tạo
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckVaiTro::class,
        ]);

        // ĐÓNG NGOẶC CHUẨN XÁC: Đóng mảng ] và đóng hàm );
        $middleware->validateCsrfTokens(except: [
            'kiem-ke-bep/store',
            'kho-chinh/kiem-ke/store'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
