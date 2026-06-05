<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $countChoDuyet = 0;
        $countThongKe = 0;

        if (Schema::hasTable('PhieuKiemKe')) {
            $countChoDuyet = DB::table('PhieuKiemKe')
                ->where('LoaiKiemKe', 'Định kỳ')
                ->where('TrangThai', 'Chờ duyệt')
                ->count();

            $countThongKe = DB::table('PhieuKiemKe')
                ->where('LoaiKiemKe', 'Định kỳ')
                ->where('TrangThai', 'Đã duyệt')
                ->count();
        }

        $countXuatHuy = 0;
        if (Schema::hasTable('XuatHuy')) {
            $countXuatHuy = DB::table('XuatHuy')->count();
        } elseif (Schema::hasTable('PhieuXuatHuy')) {
            $countXuatHuy = DB::table('PhieuXuatHuy')->count();
        }

        $countGiaiTrinh = 0;
        if (Schema::hasTable('PhieuGiaiTrinh')) {
            $countGiaiTrinh = DB::table('PhieuGiaiTrinh')->count();
        }

        return view('dashboard.index', compact('countChoDuyet', 'countXuatHuy', 'countThongKe', 'countGiaiTrinh'));
    }

    public function module(string $module): View
    {
        $pages = [
            'xuat-kho' => [
                'title' => 'Xuất kho',
                'description' => 'Theo dõi và xử lý các phiếu xuất kho cho cửa hàng, ca làm và bộ phận liên quan.',
                'highlight' => 'Quản lý xuất hàng theo yêu cầu đã duyệt.',
            ],
            'xuat-huy' => [
                'title' => 'Xuất hủy',
                'description' => 'Tổng hợp các phiếu hủy nguyên liệu, hàng lỗi hoặc quá hạn cần xử lý.',
                'highlight' => 'Theo dõi nguyên liệu hủy và lý do hủy chi tiết.',
            ],
            'kiem-ke' => [
                'title' => 'Kiểm kê',
                'description' => 'Kiểm tra tồn kho thực tế, phát hiện chênh lệch và ghi nhận kết quả kiểm kê.',
                'highlight' => 'Đối chiếu số liệu tồn kho giữa hệ thống và thực tế.',
            ],
            'giai-trinh' => [
                'title' => 'Giải trình',
                'description' => 'Theo dõi các phiếu giải trình thất thoát, chênh lệch hoặc vấn đề phát sinh trong kho.',
                'highlight' => 'Tập trung các phiếu chờ phản hồi và cần xác nhận.',
            ],
        ];

        abort_unless(isset($pages[$module]), 404);

        return view('dashboard.module', [
            'moduleKey' => $module,
            'page' => $pages[$module],
        ]);
    }
}
