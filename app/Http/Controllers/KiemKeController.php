<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class KiemKeController extends Controller
{
    public function index(): View
    {
        $headerTable = Schema::hasTable('PhieuKiemKe') ? 'PhieuKiemKe' : (Schema::hasTable('phieukiemke') ? 'phieukiemke' : null);

        $summary = [
            'total' => 0,
            'end_of_day' => 0,
            'periodic' => 0,
            'pending' => 0,
            'approved' => 0,
        ];
        $recentPhieuKiemKe = collect();

        if ($headerTable !== null) {
            $summary['total'] = DB::table($headerTable)->count();
            $summary['end_of_day'] = DB::table($headerTable)->where('LoaiKiemKe', 'Cuối ngày')->count();
            $summary['periodic'] = DB::table($headerTable)->where('LoaiKiemKe', 'Định kỳ')->count();
            $summary['pending'] = DB::table($headerTable)->where('TrangThai', 'Chờ duyệt')->count();
            $summary['approved'] = DB::table($headerTable)->where('TrangThai', 'Đã duyệt')->count();

            $recentPhieuKiemKe = DB::table($headerTable)
                ->select('MaPhieuKiemKe', 'LoaiKiemKe', 'NgayKiemKe', 'TrangThai', 'GhiChu')
                ->orderByDesc('NgayKiemKe')
                ->orderByDesc('MaPhieuKiemKe')
                ->limit(12)
                ->get();
        }

        return view('kiemke.index', compact('summary', 'recentPhieuKiemKe'));
    }
}
