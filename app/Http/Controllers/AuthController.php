<?php

namespace App\Http\Controllers;

use App\Models\TaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'SoDienThoai' => ['required'],
            'MatKhau' => ['required'],
        ]);

        $user = TaiKhoan::where('SoDienThoai', $request->SoDienThoai)->first();

        $passwordMatches = $user
            && (
                Hash::check($request->MatKhau, $user->MatKhau)
                || hash_equals((string) $user->MatKhau, (string) $request->MatKhau)
            );

        if (! $passwordMatches) {
            return back()
                ->withErrors([
                    'SoDienThoai' => 'Số điện thoại hoặc mật khẩu không đúng!',
                ])
                ->onlyInput('SoDienThoai');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $defaultRoute = match ($user->VaiTro) {
            'Cua hang truong', 'Cửa hàng trưởng' => route('dashboard'),
            'Quan ly', 'Quản lý' => route('don-hang.index'),
            'Nhan vien', 'Nhân viên' => route('nhanvien.phieuxuat'),
            default => route('purchase-orders.index'),
        };

        return redirect()->intended($defaultRoute);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
