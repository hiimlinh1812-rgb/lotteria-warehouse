<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NguyenLieu;

class NguyenLieuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy toàn bộ dữ liệu từ bảng nguyen_lieus
        $danhSachNL = NguyenLieu::all();

        // Truyền dữ liệu đó sang file giao diện
        return view('nguyenlieu.index', compact('danhSachNL'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nguyenlieu.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'MaNguyenLieu' => ['required', 'string', 'max:10', 'unique:NguyenLieu,MaNguyenLieu'],
            'TenNguyenLieu' => ['required', 'string', 'max:100'],
            'DonViTinh' => ['required', 'string', 'max:20'],
            'NhomHang' => ['required', 'string', 'max:50'],
            'MoTa' => ['nullable', 'string', 'max:255'],
        ], [
            'MaNguyenLieu.unique' => 'Không được trùng mã nguyên liệu.',
        ]);

        \App\Models\NguyenLieu::create([
            'MaNguyenLieu' => $validated['MaNguyenLieu'],
            'TenNguyenLieu' => $validated['TenNguyenLieu'],
            'DonViTinh' => $validated['DonViTinh'],
            'NhomHang' => $validated['NhomHang'],
            'SoLuongTonKho' => 0,
            'MoTa' => $validated['MoTa'] ?? null,
        ]);

        // Lưu xong thì quay tự động quay trở lại trang danh sách
        return redirect('/nguyen-lieu')->with('success', 'Đã thêm nguyên liệu mới thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    // Hàm mở form Sửa nguyên liệu
    public function edit($id)
    {
        $nl = \App\Models\NguyenLieu::findOrFail($id);
        return view('NguyenLieu.edit', compact('nl'));
    }

    // Hàm nhận dữ liệu từ form Sửa và cập nhật vào Database
    public function update(\Illuminate\Http\Request $request, $id)
    {
        $nl = \App\Models\NguyenLieu::findOrFail($id);
        $nl->update([
            'TenNguyenLieu' => $request->input('TenNguyenLieu'),
            'DonViTinh' => $request->input('DonViTinh'),
            'NhomHang' => $request->input('NhomHang'),
            'MoTa' => $request->input('MoTa'),
        ]);
        return redirect('/nguyen-lieu');
    }

    // Hàm Xóa nguyên liệu
    public function destroy($id)
    {
        try {
            $nl = \App\Models\NguyenLieu::findOrFail($id);
            $nl->delete();
            return redirect('/nguyen-lieu')->with('success', 'Đã xóa nguyên liệu thành công.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Kiểm tra lỗi vi phạm ràng buộc khóa ngoại (Integrity constraint violation)
            if ($e->getCode() == "23000") {
                return redirect()->back()->with('error', 'Không thể xóa do nguyên liệu đang được sử dụng trong hệ thống.');
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }
}
