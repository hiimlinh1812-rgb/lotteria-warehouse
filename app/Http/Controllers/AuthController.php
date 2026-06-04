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
    // 1. Xác thực input
    $request->validate([
        'SoDienThoai' => 'required',
        'MatKhau' => 'required',
    ]);

    // 2. Tìm user trong DB
    $user = \App\Models\TaiKhoan::where('SoDienThoai', $request->SoDienThoai)->first();

    // 3. Kiểm tra user và mật khẩu (Sử dụng Hash::check để khớp với cách lưu pass của cậu)
    if ($user && \Illuminate\Support\Facades\Hash::check($request->MatKhau, $user->MatKhau)) {
        
        Auth::login($user); // Đăng nhập thủ công
        $request->session()->regenerate();

        // 4. PHÂN QUYỀN CHUYỂN HƯỚNG NGAY TẠI ĐÂY
        if ($user->VaiTro === 'Cửa hàng trưởng') {
            return redirect()->route('dashboard');
        } elseif ($user->VaiTro === 'Quản lý') {
            return redirect()->route('don-hang.index');
        } elseif ($user->VaiTro === 'Nhân viên') {
            return redirect()->route('phieu-xuat.index');
        }
    }

    // Nếu sai
    return back()->withErrors([
        'SoDienThoai' => 'Số điện thoại hoặc mật khẩu không đúng!',
    ]);
}
}