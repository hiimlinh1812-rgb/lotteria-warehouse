<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class XuatHuyController extends Controller
{
    public function index(): View
    {
        $headerTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuHuy', 'chitietphieuhuy']);
        $inspectionTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $accountTable = $this->resolveExistingTable(['TaiKhoan', 'taikhoan']);

        $summary = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'total_quantity' => 0,
        ];
        $recentPhieuHuy = collect();

        if ($headerTable !== null) {
            $summary['total'] = DB::table($headerTable)->count();
            $summary['pending'] = DB::table($headerTable)->where('TrangThai', 'Chờ duyệt')->count();
            $summary['approved'] = DB::table($headerTable)->where('TrangThai', 'Đã duyệt')->count();
            $summary['rejected'] = DB::table($headerTable)->where('TrangThai', 'Từ chối')->count();

            $query = DB::table($headerTable . ' as pxh')
                ->select(
                    'pxh.MaPhieuHuy',
                    'pxh.MaPhieuKiemKe',
                    'pxh.NgayTao',
                    'pxh.TrangThai',
                    'pxh.LyDoHuy'
                );

            if ($accountTable !== null) {
                $query->leftJoin($accountTable . ' as tk', 'tk.MaTaiKhoan', '=', 'pxh.MaTaiKhoan')
                    ->addSelect('tk.HoTen as NguoiTao');
            }

            if ($inspectionTable !== null) {
                $query->leftJoin($inspectionTable . ' as pkk', 'pkk.MaPhieuKiemKe', '=', 'pxh.MaPhieuKiemKe')
                    ->addSelect('pkk.LoaiKiemKe', 'pkk.NgayKiemKe');
            }

            if ($detailTable !== null) {
                $detailSummary = DB::table($detailTable)
                    ->select(
                        'MaPhieuHuy',
                        DB::raw('COUNT(*) as SoDongNguyenLieu'),
                        DB::raw('COALESCE(SUM(SoLuongHuy), 0) as TongSoLuongHuy')
                    )
                    ->groupBy('MaPhieuHuy');

                $query->leftJoinSub($detailSummary, 'cth', function ($join) {
                    $join->on('cth.MaPhieuHuy', '=', 'pxh.MaPhieuHuy');
                })->addSelect(
                    DB::raw('COALESCE(cth.SoDongNguyenLieu, 0) as SoDongNguyenLieu'),
                    DB::raw('COALESCE(cth.TongSoLuongHuy, 0) as TongSoLuongHuy')
                );
            } else {
                $query->addSelect(
                    DB::raw('0 as SoDongNguyenLieu'),
                    DB::raw('0 as TongSoLuongHuy')
                );
            }

            $recentPhieuHuy = $query
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
    public function show($id)
    {
        $headerTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuHuy', 'chitietphieuhuy']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'nguyenlieu']);

        if ($headerTable === null) {
            return redirect()->route('xuat-huy.index')->with('error', 'Không tìm thấy bảng phiếu xuất hủy trong cơ sở dữ liệu.');
        }

        // 1. Lấy thông tin chung của phiếu hủy
        $phieuHuy = DB::table($headerTable)->where('MaPhieuHuy', $id)->first();

        if (!$phieuHuy) {
            return redirect()->route('xuat-huy.index')->with('error', 'Không tìm thấy phiếu xuất hủy này.');
        }

        // 2. Lấy danh sách nguyên liệu bên trong phiếu hủy đó
        if ($detailTable === null) {
            $chiTietHuy = collect();
        } else {
            $query = DB::table($detailTable . ' as ct')
                ->where('ct.MaPhieuHuy', $id)
                ->select('ct.*');

            if ($ingredientTable !== null) {
                $query->leftJoin($ingredientTable . ' as nl', 'ct.MaNguyenLieu', '=', 'nl.MaNguyenLieu')
                    ->addSelect('nl.TenNguyenLieu', 'nl.DonViTinh');
            }

            $chiTietHuy = $query->get();
        }

        return view('xuat-huy.show', compact('phieuHuy', 'chiTietHuy'));
    }

    private function resolveExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }
}
