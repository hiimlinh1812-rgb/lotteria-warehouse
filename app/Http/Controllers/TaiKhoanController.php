<?php

namespace App\Http\Controllers;

use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TaiKhoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dsTaiKhoan = TaiKhoan::all();
        return view('taikhoan.index', compact('dsTaiKhoan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('taikhoan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Chỉ cần validate một lần duy nhất
    $request->validate([
        'HoTen' => 'required',
        'SoDienThoai' => 'required|unique:taikhoan,SoDienThoai|digits:10',
        'MatKhau' => 'required|min:6',
        'VaiTro' => 'required',
    ]);

        // 1. Xác định tiền tố dựa trên vai trò
        $prefix = match($request->VaiTro) {
            'Cửa hàng trưởng' => 'CHT',
            'Quản lý' => 'QL',
            'Nhân viên' => 'NV',
            default => 'TK',
        };

        // 2. Tìm mã lớn nhất theo tiền tố (VD: NV001, NV002...)
        $lastUser = TaiKhoan::where('MaTaiKhoan', 'like', $prefix . '%')
                            ->orderBy('MaTaiKhoan', 'desc')
                            ->first();

        // 3. Tính toán số thứ tự tiếp theo
        $number = $lastUser ? (int)substr($lastUser->MaTaiKhoan, strlen($prefix)) + 1 : 1;
        $newId = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);

        // 4. Lưu dữ liệu
        TaiKhoan::create([
            'MaTaiKhoan' => $newId,
            'HoTen' => $request->HoTen,
            'SoDienThoai' => $request->SoDienThoai,
            'VaiTro' => $request->VaiTro,
            'MatKhau' => Hash::make($request->MatKhau),
        ]);

        return redirect()->route('tai-khoan.index')->with('success', 'Thêm nhân viên thành công!');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $MaTaiKhoan)
    {
        $taiKhoan = TaiKhoan::findOrFail($MaTaiKhoan);
        return view('taikhoan.edit', compact('taiKhoan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $MaTaiKhoan)
    {
        $taiKhoan = TaiKhoan::findOrFail($MaTaiKhoan); // Tìm theo MaTaiKhoan
        
        $taiKhoan->update([
            'HoTen' => $request->HoTen,
            'VaiTro' => $request->VaiTro,
            // Chỉ cập nhật mật khẩu nếu có nhập mật khẩu mới
            'MatKhau' => $request->MatKhau ? Hash::make($request->MatKhau) : $taiKhoan->MatKhau,
        ]);

        return redirect()->route('tai-khoan.index')->with('success', 'Cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $MaTaiKhoan)
    {
        TaiKhoan::findOrFail($MaTaiKhoan)->delete(); // Tìm và xóa
        return redirect()->route('tai-khoan.index')->with('success', 'Đã xóa!');
    }
}
