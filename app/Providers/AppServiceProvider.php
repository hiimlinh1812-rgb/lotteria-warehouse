<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate cho Cửa hàng trưởng
        Gate::define('isCuaHangTruong', function ($user) {
            return $user->VaiTro === 'Cửa hàng trưởng';
        });

        // Bổ sung Gate cho Quản lý
        Gate::define('isQuanLy', function ($user) {
            return $user->VaiTro === 'Quản lý';
        });

        // Bổ sung Gate cho Nhân viên
        Gate::define('isNhanVien', function ($user) {
            return $user->VaiTro === 'Nhân viên';
        });
    }
}
