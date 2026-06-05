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
    public function store(\Illuminate\Http\Request $request)
    {
        \App\Models\NguyenLieu::create([
            'MaNguyenLieu' => $request->input('MaNguyenLieu'),
            'TenNguyenLieu' => $request->input('TenNguyenLieu'),
            'DonViTinh' => $request->input('DonViTinh'),
            'NhomHang' => $request->input('NhomHang'),
            'SoLuongTonKho' => 0,
            'MoTa' => $request->input('MoTa'),
        ]);
        
        // Lưu xong thì quay tự động quay trở lại trang danh sách
        return redirect('/nguyen-lieu');
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
        return view('nguyenlieu.edit', compact('nl'));
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
        $nl = \App\Models\NguyenLieu::findOrFail($id);
        $nl->delete();
        return redirect('/nguyen-lieu');
    }
}
