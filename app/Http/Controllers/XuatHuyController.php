<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class XuatHuyController extends Controller
{
    public function index(): View
    {
        $headerTable = Schema::hasTable('PhieuXuatHuy') ? 'PhieuXuatHuy' : (Schema::hasTable('phieuxuathuy') ? 'phieuxuathuy' : null);
        $detailTable = Schema::hasTable('ChiTietPhieuHuy') ? 'ChiTietPhieuHuy' : (Schema::hasTable('chitietphieuhuy') ? 'chitietphieuhuy' : null);

        $summary = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'total_quantity' => 0,
        ];
        $recentPhieuHuy = collect();

        if ($headerTable !== null) {
            $summary['total'] = DB::table($headerTable)->count();
            $summary['pending'] = DB::table($headerTable)->where('TrangThai', 'Chờ duyệt')->count();
            $summary['approved'] = DB::table($headerTable)->where('TrangThai', 'Đã duyệt')->count();

            $recentPhieuHuy = DB::table($headerTable)
                ->select('MaPhieuHuy', 'MaPhieuKiemKe', 'NgayTao', 'TrangThai', 'LyDoHuy')
                ->orderByDesc('NgayTao')
                ->orderByDesc('MaPhieuHuy')
                ->limit(12)
                ->get();
        }

        if ($detailTable !== null) {
            $summary['total_quantity'] = (int) DB::table($detailTable)->sum('SoLuongHuy');
        }

        return view('xuat-huy.index', compact('summary', 'recentPhieuHuy'));
    }
}
