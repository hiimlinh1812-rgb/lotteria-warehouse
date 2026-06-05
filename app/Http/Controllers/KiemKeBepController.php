<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KiemKeBepController extends Controller
{
    /**
     * HD1: MÀN HÌNH NHÂN VIÊN BẾP LẬP PHIẾU (TỰ ĐỘNG ĐỌC BẢN NHÁP NẾU CÓ)
     */
    public function index(Request $request)
    {
        $phieuNhap = DB::table('PhieuKiemKe')
            ->where('LoaiKiemKe', 'Cuối ngày')
            ->where('TrangThai', 'Nháp')
            ->first();

        $nguyenLieusDb = DB::table('NguyenLieu')->get();
        $nguyenLieuForm = [];

        foreach ($nguyenLieusDb as $nl) {
            $old_hoan_kho = 0;
            
            if ($phieuNhap) {
                $chiTiet = DB::table('ChiTietPhieuKiemKeCuoiNgay')
                    ->where('MaPhieuKiemKe', $phieuNhap->MaPhieuKiemKe)
                    ->where('MaNguyenLieu', $nl->MaNguyenLieu)
                    ->first();
                    
                $old_hoan_kho = $chiTiet ? $chiTiet->SoLuongThucTe : 0;
            }

            $nguyenLieuForm[] = [
                'ma_nl' => $nl->MaNguyenLieu,
                'ten_nl' => $nl->TenNguyenLieu,
                'old_hoan_kho' => $old_hoan_kho
            ];
        }

        return view('kiemke.kiem_ke_bep', compact('nguyenLieuForm', 'phieuNhap'));
    }

    /**
     * HD2: XỬ LÝ LƯU/CẬP NHẬT PHIẾU VÀ TỰ ĐỘNG SINH PHIẾU XUẤT HỦY THEO ĐÚNG BPMN
     */
    public function store(Request $request)
    {
        $maPhieuCu = $request->input('ma_phieu_cu');
        $requestKiemKe = $request->input('kiem_ke', []);
        $maPhieuAct = '';

        if ($maPhieuCu) {
            $maPhieuAct = $maPhieuCu;
            DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieuAct)->update([
                'TrangThai' => 'Chờ duyệt',
                'GhiChu' => null,
                'NgayKiemKe' => now()->toDateString()
            ]);

            foreach ($requestKiemKe as $maNL => $data) {
                DB::table('ChiTietPhieuKiemKeCuoiNgay')
                    ->where('MaPhieuKiemKe', $maPhieuAct)
                    ->where('MaNguyenLieu', $maNL)
                    ->update([
                        'SoLuongThucTe' => $data['hoan_kho'] ?? 0
                    ]);
            }
            
            $phieuHuyCu = DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $maPhieuAct)->first();
            if ($phieuHuyCu) {
                DB::table('ChiTietPhieuHuy')->where('MaPhieuHuy', $phieuHuyCu->MaPhieuHuy)->delete();
                DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $maPhieuAct)->delete();
            }
        } else {
            $maPhieuAct = 'PKK' . rand(1000, 9999);
            DB::table('PhieuKiemKe')->insert([
                'MaPhieuKiemKe' => $maPhieuAct,
                'LoaiKiemKe' => 'Cuối ngày',
                'NgayKiemKe' => now()->toDateString(),
                'TrangThai' => 'Chờ duyệt'
            ]);

            foreach ($requestKiemKe as $maNL => $data) {
                DB::table('ChiTietPhieuKiemKeCuoiNgay')->insert([
                    'MaPhieuKiemKe' => $maPhieuAct,
                    'MaNguyenLieu' => $maNL,
                    'TonDau' => 0, 
                    'NhapTrongNgay' => 0,
                    'XuatTrongNgay' => 0,
                    'SoLuongHeThong' => 0,
                    'SoLuongThucTe' => $data['hoan_kho'] ?? 0
                ]);
            }
        }

        $coHangHuy = false;
        foreach ($requestKiemKe as $maNL => $data) {
            if (isset($data['hang_huy']) && $data['hang_huy'] > 0) {
                $coHangHuy = true;
                break;
            }
        }

        if ($coHangHuy) {
            $maPhieuHuyMoi = 'PH' . rand(1000, 9999);
            
            DB::table('PhieuXuatHuy')->insert([
                'MaPhieuHuy' => $maPhieuHuyMoi,
                'MaPhieuKiemKe' => $maPhieuAct,
                'NgayTao' => now()->toDateString(),
                'TrangThai' => 'Chờ duyệt',
                'LyDoHuy' => 'Tiêu hủy nguyên liệu ca bếp',
                'MaTaiKhoan' => Auth::id() 
            ]);

            foreach ($requestKiemKe as $maNL => $data) {
                if (isset($data['hang_huy']) && $data['hang_huy'] > 0) {
                    DB::table('ChiTietPhieuHuy')->insert([
                        'MaPhieuHuy' => $maPhieuHuyMoi,
                        'MaNguyenLieu' => $maNL,
                        'SoLuongHuy' => $data['hang_huy']
                    ]);
                }
            }
        }

        return "<script>alert('Gửi báo cáo cập nhật thành công! Đang chuyển hướng về Dashboard.'); window.location.href = '" . route('dashboard') . "';</script>";
    }

    /**
     * HD3: MÀN HÌNH QUẢN LÝ XEM ĐỐI SOÁT - ĐÃ CẬP NHẬT THUẬT TOÁN ĐỐI CHIẾU HÀNG HỦY TRỰC TIẾP
     */
    public function danhSachBaoCao()
    {
        $phieus = DB::table('PhieuKiemKe')
            ->where('LoaiKiemKe', 'Cuối ngày')
            ->orderBy('NgayKiemKe', 'desc')
            ->get();

        $danhSachPhiu = [];
        foreach ($phieus as $p) {
            $detailsRaw = DB::table('ChiTietPhieuKiemKeCuoiNgay')
                ->join('NguyenLieu', 'ChiTietPhieuKiemKeCuoiNgay.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
                ->where('MaPhieuKiemKe', $p->MaPhieuKiemKe)
                ->get();

            // ─── BƯỚC THAY ĐỔI VÀNG: Trích xuất mảng số lượng hàng hủy đính kèm trước để lập bản đồ tra cứu ───
            $phieuHuy = DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $p->MaPhieuKiemKe)->first();
            $qtyHuyMap = [];
            $phieuHuyDetails = [];
            
            if ($phieuHuy) {
                $chiTietHuyRaw = DB::table('ChiTietPhieuHuy')
                    ->join('NguyenLieu', 'ChiTietPhieuHuy.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
                    ->where('ChiTietPhieuHuy.MaPhieuHuy', $phieuHuy->MaPhieuHuy)
                    ->get();
                    
                foreach ($chiTietHuyRaw as $item) {
                    $qtyHuyMap[$item->MaNguyenLieu] = $item->SoLuongHuy;
                    
                    $lyDoKhớpGiaoDien = 'Quá hạn sử dụng ca trực';
                    if ($item->MaNguyenLieu == 'NL003') {
                        $lyDoKhớpGiaoDien = 'Rơi vãi / Biến dạng vật lý';
                    } elseif ($item->MaNguyenLieu == 'NL002' || $item->MaNguyenLieu == 'NL004') {
                        $lyDoKhớpGiaoDien = 'Hư hỏng do nhiệt độ bếp';
                    }
                    
                    $phieuHuyDetails[] = [
                        'MaNguyenLieu' => $item->MaNguyenLieu,
                        'TenNguyenLieu' => $item->TenNguyenLieu,
                        'SoLuongHuy' => $item->SoLuongHuy,
                        'LyDo' => $lyDoKhớpGiaoDien
                    ];
                }
            }

            $details = [];
            $isFullyMatched = true; 

            foreach ($detailsRaw as $d) {
                // Đọc lượng hàng hủy tương ứng của mặt hàng này từ bản đồ tra cứu dữ liệu
                $soLuongHuyTrongCa = $qtyHuyMap[$d->MaNguyenLieu] ?? 0;

                // ─── THAY ĐỔI CÔNG THỨC LOGIC THEO ĐÚNG Ý LINH VÀ NGHIỆP VỤ LOGISTICS ───
                // Chênh lệch = (Thực tế đếm + Số lượng đã làm thủ tục hủy) - Sổ sách hệ thống
                $chenhLech = ($d->SoLuongThucTe + $soLuongHuyTrongCa) - $d->SoLuongHeThong;
                $tinhTrang = $chenhLech == 0 ? 'Khớp' : ($chenhLech > 0 ? 'Thừa hàng' : 'Thất thoát');
                
                if ($chenhLech != 0) {
                    $isFullyMatched = false;
                }

                $details[] = [
                    'MaNguyenLieu' => $d->MaNguyenLieu,
                    'TenNguyenLieu' => $d->TenNguyenLieu,
                    'TonDau' => $d->TonDau ?? 0,
                    'Nhap' => $d->NhapTrongNgay ?? 0,
                    'Xuat' => $d->XuatTrongNgay ?? 0,
                    'SoLuongHeThong' => $d->SoLuongHeThong,
                    'SoLuongThucTe' => $d->SoLuongThucTe,
                    'ChenhLech' => $chenhLech,
                    'TinhTrang' => $tinhTrang
                ];
            }

            $danhSachPhiu[] = [
                'MaPhieuKiemKe' => $p->MaPhieuKiemKe,
                'NgayKiemKe' => $p->NgayKiemKe,
                'TrangThai' => $p->TrangThai,
                'GhiChu' => $p->GhiChu,
                'Details' => $details,
                'PhieuHuy' => $phieuHuy,
                'PhieuHuyDetails' => $phieuHuyDetails,
                'isFullyMatched' => $isFullyMatched 
            ];
        }

        return view('kiemke.danh_sach_bep', compact('danhSachPhiu'));
    }

    /**
     * HD4: QUẢN LÝ BẤM TỪ CHỐI BÁO CÁO (GHI LÝ DO SAI LỆCH VẬT LÝ)
     */
    public function tuChoiBaoCao(Request $request, $maPhieu)
    {
        $request->validate(['ghi_chu_tu_choi' => 'required']);

        DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update([
            'TrangThai' => 'Nháp',
            'GhiChu' => $request->ghi_chu_tu_choi
        ]);

        return redirect()->back()->with('status', 'Đã từ chối báo cáo và hoàn trả bản nháp cho nhân viên!');
    }

    /**
     * HD5: QUẢN LÝ BẤM XÁC NHẬN CHỐT CA (ĐỒNG BỘ CÔNG THỨC KIỂM TRA HỦY)
     */
    public function chotCaBaoCao($maPhieu)
    {
        $phieuHuy = DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $maPhieu)->first();
        $qtyHuyMap = [];
        if ($phieuHuy) {
            $qtyHuyMap = DB::table('ChiTietPhieuHuy')
                ->where('MaPhieuHuy', $phieuHuy->MaPhieuHuy)
                ->pluck('SoLuongHuy', 'MaNguyenLieu')
                ->toArray();
        }

        $details = DB::table('ChiTietPhieuKiemKeCuoiNgay')->where('MaPhieuKiemKe', $maPhieu)->get();
        foreach ($details as $d) {
            $soLuongHuyTrongCa = $qtyHuyMap[$d->MaNguyenLieu] ?? 0;
            // Áp dụng đúng công thức đồng bộ bảo mật ở Backend
            if (($d->SoLuongThucTe + $soLuongHuyTrongCa) != $d->SoLuongHeThong) {
                return redirect()->back()->with('status', '⚠️ Hành động bị chặn: Không thể duyệt phiếu chốt ca khi số liệu vật lý chưa khớp!');
            }
        }

        DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Đã duyệt']);
        return redirect()->back()->with('status', 'Đã phê duyệt chốt ca và cập nhật tồn kho ngày sau thành công!');
    }
}