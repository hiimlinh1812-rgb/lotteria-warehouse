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
            'HoTen' => 'required|string|max:100',
            'SoDienThoai' => 'required|string|regex:/^0[0-9]{9}$/|unique:TaiKhoan,SoDienThoai',
            'MatKhau' => 'required|string|min:6',
            'VaiTro' => 'required|string',
        ], [
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'SoDienThoai.unique' => 'Số điện thoại này đã được sử dụng.',
            'MatKhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
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

        $request->validate([
            'HoTen' => 'required|string|max:100',
            'SoDienThoai' => 'required|string|regex:/^0[0-9]{9}$/|unique:TaiKhoan,SoDienThoai,' . $MaTaiKhoan . ',MaTaiKhoan',
            'MatKhau' => 'nullable|string|min:6',
            'VaiTro' => 'required|string',
        ], [
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'SoDienThoai.unique' => 'Số điện thoại này đã được sử dụng.',
            'MatKhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $taiKhoan->update([
            'HoTen' => $request->HoTen,
            'SoDienThoai' => $request->SoDienThoai,
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
        try {
            TaiKhoan::findOrFail($MaTaiKhoan)->delete();
            return redirect()->route('tai-khoan.index')->with('success', 'Đã xóa tài khoản thành công!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->back()->with('error', 'Không thể xóa do tài khoản này đang được sử dụng trong hệ thống (đơn hàng, phiếu nhập/xuất...).');
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }
}
