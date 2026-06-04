<?php

namespace App\Http\Controllers;

use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TaiKhoanController extends Controller
{
    private const ROLE_PREFIXES = [
        'Cua hang truong' => 'CHT',
        'Cửa hàng trưởng' => 'CHT',
        'Quan ly' => 'QL',
        'Quản lý' => 'QL',
        'Nhan vien' => 'NV',
        'Nhân viên' => 'NV',
    ];

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
        $request->validate([
            'HoTen' => 'required',
            'SoDienThoai' => 'required|unique:TaiKhoan,SoDienThoai|digits:10',
            'MatKhau' => 'required|min:6',
            'VaiTro' => 'required',
        ]);

        $prefix = self::ROLE_PREFIXES[$request->VaiTro] ?? 'TK';

        $lastUser = TaiKhoan::where('MaTaiKhoan', 'like', $prefix . '%')
                            ->orderBy('MaTaiKhoan', 'desc')
                            ->first();

        $number = $lastUser ? (int)substr($lastUser->MaTaiKhoan, strlen($prefix)) + 1 : 1;
        $newId = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);

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
        $taiKhoan = TaiKhoan::findOrFail($MaTaiKhoan);
        
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
