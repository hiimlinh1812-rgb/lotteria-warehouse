<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * HIỂN THỊ TRANG DASHBOARD TỔNG QUAN VỚI SỐ LIỆU ĐỘNG ĐẾM TỪ DATABASE
     */
    public function index()
    {
        // 1. Đếm số phiếu kiểm kê định kỳ kho chính đang ở trạng thái 'Chờ duyệt'
        $countChoDuyet = DB::table('PhieuKiemKe')
            ->where('LoaiKiemKe', 'Định kỳ')
            ->where('TrangThai', 'Chờ duyệt')
            ->count();

        // 2. Đếm số phiếu xuất hủy (Check phòng hờ cả 2 cách đặt tên bảng XuatHuy hoặc PhieuXuatHuy)
        $countXuatHuy = 0;
        if (Schema::hasTable('XuatHuy')) {
            $countXuatHuy = DB::table('XuatHuy')->count();
        } elseif (Schema::hasTable('PhieuXuatHuy')) {
            $countXuatHuy = DB::table('PhieuXuatHuy')->count();
        }

        // 3. Đếm số phiếu thống kê tồn kho định kỳ đã được phê duyệt chốt ca thành công
        $countThongKe = DB::table('PhieuKiemKe')
            ->where('LoaiKiemKe', 'Định kỳ')
            ->where('TrangThai', 'Đã duyệt')
            ->count();

        // 4. Đếm số phiếu giải trình thất thoát vật lý thực tế đã được lập trên hệ thống
        $countGiaiTrinh = 0;
        if (Schema::hasTable('PhieuGiaiTrinh')) {
            $countGiaiTrinh = DB::table('PhieuGiaiTrinh')->count();
        }

        // Đổ toàn bộ các biến đếm số lượng này ra ngoài giao diện view
        return view('dashboard', compact('countChoDuyet', 'countXuatHuy', 'countThongKe', 'countGiaiTrinh'));
    }
}