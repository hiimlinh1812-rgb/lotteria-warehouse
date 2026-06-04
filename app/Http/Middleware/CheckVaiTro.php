<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVaiTro
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Kiểm tra xem user đã đăng nhập chưa
    if (!auth()->check()) {
        return redirect('login');
    }

    // Kiểm tra vai trò
    // Nếu $roles trống, nghĩa là không yêu cầu quyền đặc biệt (tùy cậu thiết kế)
    if (!empty($roles) && !in_array(auth()->user()->VaiTro, $roles)) {
        return redirect('/')->with('error', 'Bạn không có quyền truy cập!');
    }

    return $next($request);
    }
}
