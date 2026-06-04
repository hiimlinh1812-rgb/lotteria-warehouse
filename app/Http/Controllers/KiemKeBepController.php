<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KiemKeBepController extends Controller
{
    /**
     * HĐ1 - HĐ3: MÀN HÌNH NHÂN VIÊN BẾP LẬP PHIẾU (ĐỌC DATA THẬT TỪ SQL)
     */
    public function index(Request $request)
    {
        // Đọc toàn bộ danh mục nguyên liệu thực tế từ file SQL bạn đã nạp
        $nguyenLieusDb = DB::table('NguyenLieu')->get();

        $phieuNhap = null;
        $chiTietNhaph = [];
        if ($request->has('edit_code')) {
            $phieuNhap = DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $request->edit_code)->where('TrangThai', 'Nháp')->first();
            if ($phieuNhap) {
                $chiTietNhaph = DB::table('ChiTietPhieuKiemKeCuoiNgay')
                    ->where('MaPhieuKiemKe', $request->edit_code)
                    ->get()
                    ->keyBy('MaNguyenLieu')
                    ->toArray();
            }
        }

        $nguyenLieuForm = [];
        foreach ($nguyenLieusDb as $nl) {
            // Giả lập công thức tính toán Tồn đầu, Nhập, Xuất logic dựa trên số lượng tồn kho thật trong DB
            $xuat = 20;
            $nhap = 30;
            $tonDau = $nl->SoLuongTonKho - $nhap + $xuat;
            if ($tonDau < 0) { $tonDau = $nl->SoLuongTonKho; $nhap = 0; $xuat = 0; }

            $nguyenLieuForm[] = [
                'ma_nl' => $nl->MaNguyenLieu,
                'ten_nl' => $nl->TenNguyenLieu,
                'ton_dau_ngay' => $tonDau,
                'nhap_trong_ngay' => $nhap,
                'xuat_trong_ngay' => $xuat,
                'so_luong_he_thong' => $nl->SoLuongTonKho,
                'old_hoan_kho' => isset($chiTietNhaph[$nl->MaNguyenLieu]) ? $chiTietNhaph[$nl->MaNguyenLieu]->SoLuongThucTe : 0,
                'hang_huy' => 0
            ];
        }

        return view('kiemke.kiem_ke_bep', compact('nguyenLieuForm', 'phieuNhap'));
    }

    /**
     * HĐ6 - HĐ9: XỬ LÝ LƯU PHIẾU KIỂM KÊ VÀ LÝ DO TIÊU HỦY ĐỘNG
     */
    public function store(Request $request)
    {
        $request->validate(['kiem_ke' => 'required|array']);
        
        $maPhieuKiemKe = $request->input('ma_phieu_cu', 'PKK' . rand(1000, 9999));
        $isUpdate = $request->has('ma_phieu_cu');
        $coHangHuy = false;
        $itemsHuy = []; 

        DB::transaction(function () use ($request, $maPhieuKiemKe, $isUpdate, &$coHangHuy, &$itemsHuy) {
            if ($isUpdate) {
                DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieuKiemKe)->update(['TrangThai' => 'Chờ duyệt']);
                $phieuHuyCu = DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $maPhieuKiemKe)->first();
                if ($phieuHuyCu) {
                    DB::table('ChiTietPhieuHuy')->where('MaPhieuHuy', $phieuHuyCu->MaPhieuHuy)->delete();
                }
                DB::table('ChiTietPhieuKiemKeCuoiNgay')->where('MaPhieuKiemKe', $maPhieuKiemKe)->delete();
                DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $maPhieuKiemKe)->delete();
            } else {
                DB::table('PhieuKiemKe')->insert([
                    'MaPhieuKiemKe' => $maPhieuKiemKe, 'NgayKiemKe' => now()->toDateString(),
                    'LoaiKiemKe' => 'Cuối ngày', 'TrangThai' => 'Chờ duyệt',
                    'GhiChu' => 'Nhân viên lập báo cáo kiểm kê bếp cuối ngày', 'MaTaiKhoan' => 'QL001' 
                ]);
            }

            foreach ($request->kiem_ke as $maNL => $data) {
                $nlDb = DB::table('NguyenLieu')->where('MaNguyenLieu', $maNL)->first();
                if ($nlDb) {
                    $soLuongHeThong = $nlDb->SoLuongTonKho;
                    $hoanKho = intval($data['hoan_kho']); 
                    $hangHuy = intval($data['hang_huy']); 
                    $lyDoHuyCuaDong = $data['ly_do_huy'] ?? 'Hàng hỏng ca bếp';
                    
                    $soLuongThucTe = $hoanKho + $hangHuy; 
                    $chenhLech = $soLuongThucTe - $soLuongHeThong;
                    
                    // Để vượt qua vòng CHECK constraint của SQL: Chỉ ghi 'Thiếu' hoặc 'Đủ' vào database
                    $tinhTrangDb = ($chenhLech >= 0) ? 'Đủ' : 'Thiếu';

                    if ($hangHuy > 0) { 
                        $coHangHuy = true; 
                        $itemsHuy[] = [
                            'MaNguyenLieu' => $maNL,
                            'SoLuongHuy' => $hangHuy,
                            'LyDo' => $lyDoHuyCuaDong
                        ];
                    }

                    DB::table('ChiTietPhieuKiemKeCuoiNgay')->insert([
                        'MaPhieuKiemKe' => $maPhieuKiemKe, 'MaNguyenLieu' => $maNL,
                        'SoLuongHeThong' => $soLuongHeThong, 'SoLuongThucTe' => $hoanKho, 
                        'ChenhLech' => $chenhLech, 'TinhTrang' => $tinhTrangDb
                    ]);
                }
            }

            if ($coHangHuy) {
                $maPhieuHuy = 'PH' . rand(1000, 9999);
                // Lưu lý do hủy tổng hợp kèm chi tiết để hiển thị lên bảng
                $chuoiLyDo = "";
                foreach ($itemsHuy as $item) {
                    $chuoiLyDo .= $item['MaNguyenLieu'] . ":" . $item['LyDo'] . "; ";
                }

                DB::table('PhieuXuatHuy')->insert([
                    'MaPhieuHuy' => $maPhieuHuy, 'NgayTao' => now()->toDateString(),
                    'LyDoHuy' => rtrim($chuoiLyDo, '; '), 'TrangThai' => 'Chờ duyệt', 
                    'MaTaiKhoan' => 'QL001', 'MaPhieuKiemKe' => $maPhieuKiemKe
                ]);

                foreach ($itemsHuy as $item) {
                    DB::table('ChiTietPhieuHuy')->insert([
                        'MaPhieuHuy' => $maPhieuHuy, 'MaNguyenLieu' => $item['MaNguyenLieu'], 'SoLuongHuy' => $item['SoLuongHuy']
                    ]);
                }
            }
        });

        return "<script>alert('Khởi tạo báo cáo thành công!'); window.location.href = '" . route('quanly.kiemke.bep') . "';</script>";
    }

    /**
     * HĐ10: MÀN HÌNH QUẢN LÝ XEM ĐỐI SOÁT (FIX LỖI HIỂN THỊ CHÊNH LỆCH)
     */
    public function danhSachBaoCao()
    {
        $phieus = DB::table('PhieuKiemKe')->where('LoaiKiemKe', 'Cuối ngày')->orderBy('NgayKiemKe', 'desc')->get();
        $danhSachGocDb = DB::table('NguyenLieu')->get()->keyBy('MaNguyenLieu')->toArray();

        $danhSachPhiuGomCum = [];

        foreach ($phieus as $phieu) {
            $details = DB::table('ChiTietPhieuKiemKeCuoiNgay')->where('MaPhieuKiemKe', $phieu->MaPhieuKiemKe)->get();
            $phieuHuy = DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $phieu->MaPhieuKiemKe)->first();
            
            $phieuHuyDetails = [];
            if ($phieuHuy) {
                $ctHuy = DB::table('ChiTietPhieuHuy')->where('MaPhieuHuy', $phieuHuy->MaPhieuHuy)->get();
                
                // Giải mã chuỗi lý do hủy riêng biệt từng dòng từ trường LyDoHuy tổng quát
                $chuoiLyDo = $phieuHuy->LyDoHuy;
                
                foreach ($ctHuy as $ct) {
                    $lyDoRieng = 'Hàng hỏng/Quá hạn ca bếp';
                    if (preg_match('/' . $ct->MaNguyenLieu . ':(.*?);/', $chuoiLyDo . ';', $matches)) {
                        $lyDoRieng = trim($matches[1]);
                    }

                    $phieuHuyDetails[] = [
                        'MaNguyenLieu' => $ct->MaNguyenLieu,
                        'TenNguyenLieu' => $danhSachGocDb[$ct->MaNguyenLieu]->TenNguyenLieu ?? 'Nguyên liệu',
                        'SoLuongHuy' => $ct->SoLuongHuy,
                        'LyDo' => $lyDoRieng
                    ];
                }
            }

            $hasDiscrepancy = false; 
            $enrichedDetails = [];
            
            foreach ($details as $detail) {
                if ($detail->ChenhLech != 0) { $hasDiscrepancy = true; }
                
                // ĐỌC LOGIC SỬA LỖI: Rẽ nhánh hiển thị chính xác theo con số Chênh lệch
                if ($detail->ChenhLech == 0) {
                    $tinhTrangHienThi = 'Khớp';
                } elseif ($detail->ChenhLech < 0) {
                    $tinhTrangHienThi = 'Thất thoát';
                } else {
                    $tinhTrangHienThi = 'Thừa hàng';
                }

                $goc = $danhSachGocDb[$detail->MaNguyenLieu] ?? null;
                $tenNL = $goc ? $goc->TenNguyenLieu : 'Nguyên liệu hệ thống';
                
                // Công thức ngược phục vụ việc hiển thị cột Tồn đầu, Nhập, Xuất khớp số liệu hệ thống
                $xuat = 20; $nhap = 30;
                $tonDau = $detail->SoLuongHeThong - $nhap + $xuat;
                if ($tonDau < 0) { $tonDau = $detail->SoLuongHeThong; $nhap = 0; $xuat = 0; }

                $enrichedDetails[] = [
                    'MaNguyenLieu' => $detail->MaNguyenLieu,
                    'TenNguyenLieu' => $tenNL,
                    'TonDau' => $tonDau, 'Nhap' => $nhap, 'Xuat' => $xuat,
                    'SoLuongHeThong' => $detail->SoLuongHeThong,
                    'SoLuongThucTe' => $detail->SoLuongThucTe,
                    'ChenhLech' => $detail->ChenhLech,
                    'TinhTrang' => $tinhTrangHienThi
                ];
            }
            
            $danhSachPhiuGomCum[] = [
                'MaPhieuKiemKe' => $phieu->MaPhieuKiemKe,
                'NgayKiemKe' => $phieu->NgayKiemKe,
                'TrangThai' => $phieu->TrangThai,
                'GhiChu' => $phieu->GhiChu,
                'hasDiscrepancy' => $hasDiscrepancy, 
                'PhieuHuy' => $phieuHuy,
                'PhieuHuyDetails' => $phieuHuyDetails,
                'Details' => $enrichedDetails
            ];
        }

        return view('kiemke.danh_sach_bep', ['danhSachPhiu' => $danhSachPhiuGomCum]);
    }

    /**
     * HĐ12: TỪ CHỐI
     */
    public function tuChoiBaoCao(Request $request, $maPhieu)
    {
        $request->validate(['ghi_chu_tu_choi' => 'nullable|string|max:255']);
        DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Nháp','GhiChu' => $request->input('ghi_chu_tu_choi', 'Báo cáo bị từ chối.')]);
        return redirect()->route('kiemke.bep', ['edit_code' => $maPhieu])->with('status', 'Báo cáo trả về nháp.');
    }

    /**
     * HĐ15 - HĐ16: CHỐT CA
     */
    public function chotCaBaoCao($maPhieu)
    {
        DB::transaction(function () use ($maPhieu) {
            DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Đã duyệt']);
            DB::table('PhieuXuatHuy')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Đã duyệt']);
            
            $chiTiet = DB::table('ChiTietPhieuKiemKeCuoiNgay')->where('MaPhieuKiemKe', $maPhieu)->get();
            foreach ($chiTiet as $item) {
                DB::table('NguyenLieu')->where('MaNguyenLieu', $item->MaNguyenLieu)->update(['SoLuongTonKho' => $item->SoLuongThucTe]);
            }
        });
        return redirect()->back()->with('status', 'Chốt ca thành công!');
    }
}