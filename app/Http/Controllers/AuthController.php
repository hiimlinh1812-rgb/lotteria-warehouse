<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login'); // Trỏ đúng về file giao diện login của cậu
    }
    public function login(Request $request)
    {
        // Xác thực cơ bản
    $request->validate([
        'SoDienThoai' => 'required',
        'MatKhau' => 'required',
    ]);

    // Lấy thông tin tài khoản từ database trước
    $user = \App\Models\TaiKhoan::where('SoDienThoai', $request->SoDienThoai)->first();

    // Kiểm tra nếu user tồn tại VÀ mật khẩu khớp
    if ($user && \Illuminate\Support\Facades\Hash::check($request->MatKhau, $user->MatKhau)) {
        Auth::login($user); // Đăng nhập thủ công
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'SoDienThoai' => 'Số điện thoại hoặc mật khẩu không đúng!',
    ]);
    }
}