<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class KiemKeKhoChinhController extends Controller
{
    /**
     * HĐ2: MÀN HÌNH NHÂN VIÊN ĐẾM KHO CHÍNH (BLIND COUNTING - GIẤU SỔ SÁCH)
     */
    public function index()
    {
        $loHangsDb = DB::table('LoHang')
            ->join('NguyenLieu', 'LoHang.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
            ->select('LoHang.*', 'NguyenLieu.TenNguyenLieu')
            ->get();

        $phiuKiemKeDienTu = [];
        foreach ($loHangsDb as $lo) {
            $ngayHienTai = now();
            $ngayHsd = \Carbon\Carbon::parse($lo->HanSuDung);
            $soNgayConLai = $ngayHienTai->diffInDays($ngayHsd, false);

            if ($soNgayConLai < 0) {
                $canhBaoHsd = 'HẾT HẠN SỬ DỤNG';
            } elseif ($soNgayConLai <= 7) {
                $canhBaoHsd = 'CẬN HẠN (Còn ' . floor($soNgayConLai) . ' ngày)';
            } else {
                $canhBaoHsd = 'An toàn';
            }

            $phiuKiemKeDienTu[] = [
                'ma_lo' => $lo->MaLoHang,
                'ten_nl' => $lo->TenNguyenLieu,
                'hsd' => $lo->HanSuDung,
                'canh_bao_hsd' => $canhBaoHsd,
                'so_sach' => $lo->SoLuongConLai
            ];
        }

        return view('kiemke.kho_chinh_form', compact('phiuKiemKeDienTu'));
    }

    /**
     * HĐ3: NHÂN VIÊN GỬI BÁO CÁO KHO CHÍNH
     */
    public function store(Request $request)
    {
        $requestKiemKe = $request->input('kiem_ke', []);
        
        if (empty($requestKiemKe)) {
            return "<script>alert('Không có dữ liệu kiểm kê!'); window.location.href='" . route('khochinh.kiemke') . "';</script>";
        }

        $maPhieuMoi = 'PKK' . rand(1000, 9999);
        
        DB::table('PhieuKiemKe')->insert([
            'MaPhieuKiemKe' => $maPhieuMoi,
            'LoaiKiemKe' => 'Định kỳ',
            'NgayKiemKe' => now()->toDateString(),
            'TrangThai' => 'Chờ duyệt',
            'MaTaiKhoan' => Auth::id() 
        ]);

        foreach ($requestKiemKe as $maLo => $data) {
            $thucTe = $data['thuc_te'] ?? 0;
            
            $loHang = DB::table('LoHang')->where('MaLoHang', $maLo)->first();
            $soSach = $loHang ? $loHang->SoLuongConLai : 0;
            $chenhLech = $thucTe - $soSach;
            $tinhTrang = $chenhLech == 0 ? 'Khớp' : ($chenhLech > 0 ? 'Thừa hàng' : 'Thất thoát');

            DB::table('ChiTietPhieuKiemKeDinhKy')->insert([
                'MaPhieuKiemKe' => $maPhieuMoi,
                'MaLoHang' => $maLo,
                'SoLuongHeThong' => $soSach,
                'SoLuongThucTe' => $thucTe,
                'ChenhLech' => $chenhLech,
                'TinhTrang' => $tinhTrang 
            ]);
        }

        return "<script>alert('Gửi dữ liệu kiểm kê kho chính thành công!'); window.location.href='" . route('khochinh.kiemke') . "';</script>";
    }

    /**
     * HĐ9: MÀN HÌNH QUẢN LÝ CA DUYỆT ĐỐI SOÁT KHO CHÍNH
     */
    public function danhSachDuyet(Request $request)
    {
        $phieus = DB::table('PhieuKiemKe')->where('LoaiKiemKe', 'Định kỳ')->get();
        $danhSachPhiu = [];

        foreach ($phieus as $p) {
            $details = $this->normalizedAuditDetails($p->MaPhieuKiemKe);
            $biLech = false;
            $editedLotsOfPhieu = $this->getEditedLots($p->MaPhieuKiemKe);

            foreach ($details as $d) {
                if ($d->ChenhLech != 0) {
                    $biLech = true;
                }
                $d->isEdited = in_array($d->MaLoHang, $editedLotsOfPhieu);
            }

            $giaiTrinh = DB::table('PhieuGiaiTrinh')->where('MaPhieuKiemKe', $p->MaPhieuKiemKe)->first();

            $danhSachPhiu[] = [
                'MaPhieuKiemKe' => $p->MaPhieuKiemKe,
                'NgayKiemKe' => $p->NgayKiemKe,
                'TrangThai' => $p->TrangThai,
                'biLech' => $biLech,
                'Details' => $details,
                'GiaiTrinh' => $giaiTrinh
            ];
        }

        return view('kiemke.kho_chinh_manager', compact('danhSachPhiu'));
    }

    /**
     * HĐ10: QUẢN LÝ HIỆU CHỈNH SỬ SỐ LIỆU LÔ HÀNG VÀ CHỐT KHÓA NÚT SỬA LẦN ĐẦU
     */
    public function hieuChinhPhieu(Request $request, $maPhieu)
    {
        $request->validate([
            'ma_lo' => 'required', 
            'thuc_te_moi' => 'required|integer|min:0' 
        ]);
        
        $maLo = $request->ma_lo;
        $thucTeMoi = $request->thuc_te_moi;
        $phieu = DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->first();

        if (! $phieu || $phieu->TrangThai !== 'Chờ duyệt') {
            return redirect()->back()->with('status', 'Chỉ phiếu đang ở trạng thái Chờ duyệt mới được phép hiệu chỉnh.');
        }

        $editedLots = $this->getEditedLots($maPhieu);
        if (in_array($maLo, $editedLots)) {
            return redirect()->back()->with('status', 'Lô hàng này đã được hiệu chỉnh một lần, không thể sửa lại.');
        }

        $loHang = DB::table('LoHang')->where('MaLoHang', $maLo)->first();
        $soSach = $loHang ? $loHang->SoLuongConLai : 0;
        
        $chenhLechMoi = $thucTeMoi - $soSach;
        $tinhTrangMoi = $chenhLechMoi == 0 ? 'Khớp' : ($chenhLechMoi > 0 ? 'Thừa hàng' : 'Thất thoát');

        DB::table('ChiTietPhieuKiemKeDinhKy')
            ->where('MaPhieuKiemKe', $maPhieu)
            ->where('MaLoHang', $maLo)
            ->update([
                'SoLuongThucTe' => $thucTeMoi,
                'ChenhLech' => $chenhLechMoi,
                'TinhTrang' => $tinhTrangMoi 
            ]);

        $editedLots[] = $maLo;
        $editedLots = array_values(array_unique($editedLots));
        Cache::forever($this->editedLotsCacheKey($maPhieu), $editedLots);
        session()->put("edited_lots.{$maPhieu}", $editedLots);

        return redirect()->back()->with('status', 'Đã hiệu chỉnh số liệu thực tế lô hàng thành công!');
    }

    /**
     * HĐ10.5: CHUYỂN HƯỚNG SANG BIỂU MẪU GIẢI TRÌNH & ĐỔI TRẠNG THÁI PHIẾU KIỂM KÊ
     */
    public function chuyenHuongGiaiTrinh($maPhieu)
    {
        DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Đã duyệt']);
        return redirect()->route('quanly.khochinh.giaiTrinhForm', $maPhieu);
    }

    /**
     * HĐ10.6: GIAO DIỆN BIỂU MẪU NHẬP GIẢI TRÌNH ĐỘC LẬP
     */
    public function giaiTrinhForm($maPhieu)
    {
        $detailsLech = collect($this->normalizedAuditDetails($maPhieu))
            ->filter(fn ($detail) => (int) $detail->ChenhLech !== 0)
            ->values();

        return view('kiemke.giai_trinh_form', compact('maPhieu', 'detailsLech'));
    }

    /**
     * HĐ11: XÁC NHẬN GỬI PHIẾU GIẢI TRÌNH
     */
    public function taoGiaiTrinh(Request $request, $maPhieu)
    {
        $request->validate([
            'noi_dung' => 'required|string|min:5',
            'nguyen_nhan' => 'required|string|min:5',
            'bang_chung' => 'required|string'
        ], [
            'noi_dung.required' => 'Vui lòng điền nội dung giải trình thất thoát!',
            'nguyen_nhan.required' => 'Vui lòng nêu rõ nguyên nhân xảy ra chênh lệch!',
            'bang_chung.required' => 'Bắt buộc cung cấp bằng chứng liên quan phục vụ kiểm toán!'
        ]);

        DB::table('PhieuGiaiTrinh')->insert([
            'MaPhieuGiaiTrinh' => 'PGT' . rand(1000, 9999),
            'MaPhieuKiemKe' => $maPhieu,
            'NoiDung' => $request->noi_dung . ' [Bằng chứng: ' . $request->bang_chung . ']',
            'NguyenNhan' => $request->nguyen_nhan,
            'NgayTao' => now(),
            'MaTaiKhoan' => Auth::id() 
        ]);

        $details = DB::table('ChiTietPhieuKiemKeDinhKy')->where('MaPhieuKiemKe', $maPhieu)->get();
        foreach ($details as $d) {
            DB::table('LoHang')->where('MaLoHang', $d->MaLoHang)->update([
                'SoLuongConLai' => $d->SoLuongThucTe
            ]);
        }

        return redirect()->route('quanly.khochinh.duyet')->with('status', 'Tạo phiếu giải trình thành công và đã quay lại danh sách duyệt kiểm kho chính.');
    }

    /**
     * HĐ12: DUYỆT TRỰC TIẾP KHI SỐ LIỆU ĐÃ KHỚP TOÀN BỘ 100%
     */
    public function duyetPhieuTrucCtiep($maPhieu)
    {
        DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Đã duyệt']);
        
        $details = DB::table('ChiTietPhieuKiemKeDinhKy')->where('MaPhieuKiemKe', $maPhieu)->get();
        foreach ($details as $d) {
            DB::table('LoHang')->where('MaLoHang', $d->MaLoHang)->update([
                'SoLuongConLai' => $d->SoLuongThucTe
            ]);
        }

        return redirect()->back()->with('status', 'Xác nhận đối soát thành công! Đã cập nhật tồn kho thực tế và đổi trạng thái phiếu thành Đã duyệt.');
    }

    private function normalizedAuditDetails(string $maPhieu): array
    {
        $lotTable = $this->resolveExistingTable(['LoHang', 'lohang', 'lo_hang']);
        $details = DB::table('ChiTietPhieuKiemKeDinhKy')
            ->where('MaPhieuKiemKe', $maPhieu)
            ->get();

        foreach ($details as $detail) {
            $soSach = (int) ($detail->SoLuongHeThong ?? 0);
            if ($lotTable !== null) {
                $soSachTuDb = DB::table($lotTable)
                    ->where('MaLoHang', $detail->MaLoHang)
                    ->value('SoLuongConLai');

                if ($soSachTuDb !== null) {
                    $soSach = (int) $soSachTuDb;
                }
            }

            $thucTe = (int) ($detail->SoLuongThucTe ?? 0);
            $chenhLech = $thucTe - $soSach;
            $tinhTrang = $chenhLech === 0 ? 'Khớp' : ($chenhLech > 0 ? 'Thừa hàng' : 'Thất thoát');

            if (
                (int) ($detail->SoLuongHeThong ?? 0) !== $soSach ||
                (int) $detail->ChenhLech !== $chenhLech ||
                (string) $detail->TinhTrang !== $tinhTrang
            ) {
                DB::table('ChiTietPhieuKiemKeDinhKy')
                    ->where('MaPhieuKiemKe', $maPhieu)
                    ->where('MaLoHang', $detail->MaLoHang)
                    ->update([
                        'SoLuongHeThong' => $soSach,
                        'ChenhLech' => $chenhLech,
                        'TinhTrang' => $tinhTrang,
                    ]);
            }

            $detail->SoLuongHeThong = $soSach;
            $detail->ChenhLech = $chenhLech;
            $detail->TinhTrang = $tinhTrang;
        }

        return $details->all();
    }

    private function resolveExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (Schema::hasTable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function getEditedLots(string $maPhieu): array
    {
        $sessionLots = session()->get("edited_lots.{$maPhieu}", []);
        $cachedLots = Cache::get($this->editedLotsCacheKey($maPhieu), []);

        return array_values(array_unique(array_merge($sessionLots, $cachedLots)));
    }

    private function editedLotsCacheKey(string $maPhieu): string
    {
        return 'kiemke_kho_chinh.edited_lots.' . $maPhieu;
    }

    /**
     * =========================================================================
     * ĐA THÊM MỚI: CHỨC NĂNG BÁO CÁO THỐNG KÊ TỒN KHO SAU KIỂM KÊ (CHỈ XEM PHIẾU ĐÃ DUYỆT)
     * =========================================================================
     */
    public function thongKeTonKho()
    {
        // Thực hiện quét nghiêm ngặt: Chỉ lấy phiếu loại định kỳ kho chính ĐÃ DUYỆT
        $phieusDaDuyet = DB::table('PhieuKiemKe')
            ->where('LoaiKiemKe', 'Định kỳ')
            ->where('TrangThai', 'Đã duyệt')
            ->orderBy('NgayKiemKe', 'desc')
            ->get();

        $lichSuThongKe = [];
        foreach ($phieusDaDuyet as $p) {
            // Lấy chi tiết số liệu đối soát đính kèm của phiếu đó
            $details = DB::table('ChiTietPhieuKiemKeDinhKy')
                ->join('LoHang', 'ChiTietPhieuKiemKeDinhKy.MaLoHang', '=', 'LoHang.MaLoHang')
                ->join('NguyenLieu', 'LoHang.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
                ->where('ChiTietPhieuKiemKeDinhKy.MaPhieuKiemKe', $p->MaPhieuKiemKe)
                ->select('ChiTietPhieuKiemKeDinhKy.*', 'NguyenLieu.TenNguyenLieu')
                ->get();

            // Lấy kèm phiếu giải trình đính kèm nếu có để sếp đối chiếu lý do tại chỗ
            $giaiTrinh = DB::table('PhieuGiaiTrinh')->where('MaPhieuKiemKe', $p->MaPhieuKiemKe)->first();

            $lichSuThongKe[] = [
                'MaPhieuKiemKe' => $p->MaPhieuKiemKe,
                'NgayKiemKe' => $p->NgayKiemKe,
                'Details' => $details,
                'GiaiTrinh' => $giaiTrinh
            ];
        }

        return view('kiemke.thong_ke_ton_kho', compact('lichSuThongKe'));
    }
}
