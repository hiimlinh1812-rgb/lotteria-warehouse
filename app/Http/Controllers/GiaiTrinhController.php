<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiaiTrinhController extends Controller
{
    /**
     * HIỂN THỊ DANH SÁCH PHIẾU GIẢI TRÌNH TRÊN HỆ THỐNG
     */
    public function index()
    {
        // FIX TRIỆT ĐỂ: Thay vì orderBy theo NgayLap, tụi mình đổi sang NgayKiemKe của bảng cha cho an toàn 100%
        $danhSachGiaiTrinh = DB::table('PhieuGiaiTrinh')
            ->join('PhieuKiemKe', 'PhieuGiaiTrinh.MaPhieuKiemKe', '=', 'PhieuKiemKe.MaPhieuKiemKe')
            ->select('PhieuGiaiTrinh.*', 'PhieuKiemKe.NgayKiemKe')
            ->orderBy('PhieuKiemKe.NgayKiemKe', 'desc')
            ->get();

        return view('giaitrinh.index', compact('danhSachGiaiTrinh'));
    }
}