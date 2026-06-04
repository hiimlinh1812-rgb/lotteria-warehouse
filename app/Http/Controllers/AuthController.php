<?php

namespace App\Http\Controllers;

use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
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

        return redirect()->intended('/purchase-orders');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
