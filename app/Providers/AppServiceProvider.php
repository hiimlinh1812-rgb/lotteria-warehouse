<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('isCuaHangTruong', function ($user) {
            return in_array($user->VaiTro, ['Cua hang truong', 'Cửa hàng trưởng'], true);
        });

        Gate::define('isQuanLy', function ($user) {
            return in_array($user->VaiTro, ['Quan ly', 'Quản lý'], true);
        });

        Gate::define('isNhanVien', function ($user) {
            return in_array($user->VaiTro, ['Nhan vien', 'Nhân viên'], true);
        });

        Gate::define('isManagementUser', function ($user) {
            return in_array($user->VaiTro, ['Quan ly', 'Quản lý', 'Cua hang truong', 'Cửa hàng trưởng'], true);
        });
    }
}
