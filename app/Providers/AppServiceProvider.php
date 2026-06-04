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
        Gate::define('isCuaHangTruong', function ($user) {
        // Chỉ trả về true nếu vai trò đúng từng chữ một
        return $user->VaiTro === 'Cửa hàng trưởng';
    });
    }
}
