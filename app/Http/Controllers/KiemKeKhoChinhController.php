<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KiemKeKhoChinhController extends Controller
{
    /**
     * HĐ1 - HĐ3: MÀN HÌNH NHÂN VIÊN KHO LẬP PHIẾU (ĐỌC LIVE DATA TỪ SQL THẬT)
     */
    public function index()
    {
        // Truy vấn kết nối bảng Lô Hàng với bảng Nguyên Liệu để bốc dữ liệu thật 100%
        $loHangsDb = DB::table('LoHang')
            ->join('NguyenLieu', 'LoHang.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
            ->select('LoHang.*', 'NguyenLieu.TenNguyenLieu')
            ->get();

        $phiuKiemKeDienTu = [];
        foreach ($loHangsDb as $lo) {
            // Thuật toán tự động quét ngày tháng hệ thống để bắn Cảnh báo HSD [HĐ2]
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
                'so_sach' => $lo->SoLuongConLai // Số sổ sách hệ thống chính là lượng con lại trong lô
            ];
        }

        return view('kiemke.kho_chinh_form', compact('phiuKiemKeDienTu'));
    }

    /**
     * HĐ4 - HĐ8: NHÂN VIÊN GỬI BÁO CÁO - ĐỐI SOÁT VÀ LƯU DATABASE THẬT
     */
    public function store(Request $request)
    {
        $request->validate(['kiem_ke' => 'required|array']);
        
        $maPhieu = 'PKK' . rand(1000, 9999);
        $coChenhLech = false;

        DB::transaction(function () use ($request, $maPhieu, &$coChenhLech) {
            // Lưu thông tin phiếu kiểm kê định kỳ kho chính vào bảng PhieuKiemKe
            DB::table('PhieuKiemKe')->insert([
                'MaPhieuKiemKe' => $maPhieu,
                'NgayKiemKe' => now()->toDateString(),
                'LoaiKiemKe' => 'Cuối kỳ',
                'TrangThai' => 'Chờ duyệt',
                'GhiChu' => 'Kiểm kê định kỳ tổng kho chính',
                'MaTaiKhoan' => 'QL001'
            ]);

            foreach ($request->kiem_ke as $maLo => $data) {
                $loDb = DB::table('LoHang')->where('MaLoHang', $maLo)->first();
                if ($loDb) {
                    $soLuongHeThong = $loDb->SoLuongConLai;
                    $soLuongThucTe = intval($data['thuc_te']);
                    $chenhLech = $soLuongThucTe - $soLuongHeThong;

                    if ($chenhLech != 0) {
                        $coChenhLech = true;
                    }

                    // Khớp chuẩn theo ràng buộc CHECK (Thiếu / Đủ) của file SQL định nghĩa
                    $tinhTrangDb = ($chenhLech >= 0) ? 'Đủ' : 'Thiếu';

                    DB::table('ChiTietPhieuKiemKeDinhKy')->insert([
                        'MaPhieuKiemKe' => $maPhieu,
                        'MaLoHang' => $maLo,
                        'SoLuongHeThong' => $soLuongHeThong,
                        'SoLuongThucTe' => $soLuongThucTe,
                        'ChenhLech' => $chenhLech,
                        'TinhTrang' => $tinhTrangDb
                    ]);
                }
            }
        });

        $msg = $coChenhLech 
            ? 'Phát hiện sai lệch dữ liệu đối soát thực tế! Hệ thống tự động gửi cảnh báo chênh lệch cho Quản lý [HĐ8].'
            : 'Số liệu khớp hoàn toàn! Hệ thống tự động xuất phiếu thống kê tồn kho tổng hợp gửi Cửa hàng trưởng [HĐ6].';

        return "<script>alert('" . $msg . "'); window.location.href='" . route('quanly.khochinh.duyet') . "';</script>";
    }

    /**
     * HĐ10: MÀN HÌNH DUYỆT ĐỘNG CỦA QUẢN LÝ (ĐỌC DỮ LIỆU THẬT VÀ TRA CỨU BIẾN ĐỔI CHÊNH LỆCH)
     */
    public function danhSachDuyet(Request $request)
    {
        // Lấy tất cả các phiếu kiểm kê định kỳ kho chính từ SQL
        $phieus = DB::table('PhieuKiemKe')->where('LoaiKiemKe', 'Cuối kỳ')->orderBy('NgayKiemKe', 'desc')->get();
        $fixedItems = session('fixed_items', []); // Đọc lượt nhớ nút bấm đã sửa từ Session

        $danhSachPhiu = [];
        foreach ($phieus as $p) {
            $detailsDb = DB::table('ChiTietPhieuKiemKeDinhKy')->where('MaPhieuKiemKe', $p->MaPhieuKiemKe)->get();
            $giaiTrinh = DB::table('PhieuGiaiTrinh')->where('MaPhieuKiemKe', $p->MaPhieuKiemKe)->first();
            
            $biLech = false;
            $details = [];

            foreach ($detailsDb as $d) {
                // Kiểm tra xem dòng lô hàng này đã từng bấm nút Sửa lô lần nào chưa
                $isEdited = isset($fixedItems[$p->MaPhieuKiemKe][$d->MaLoHang]);
                
                if ($d->ChenhLech != 0 && $p->TrangThai == 'Chờ duyệt') {
                    $biLech = true; // Phiếu kiểm kê vẫn còn tồn tại sai sót
                }

                // Dịch từ dữ liệu thô trong SQL sang trạng thái hiển thị logic trên giao diện Quản lý
                if ($d->ChenhLech == 0) {
                    $tinhTrangHienThi = 'Khớp';
                } elseif ($d->ChenhLech < 0) {
                    $tinhTrangHienThi = 'Thất thoát';
                } else {
                    $tinhTrangHienThi = 'Thừa hàng';
                }

                $details[] = (object)[
                    'MaLoHang' => $d->MaLoHang,
                    'SoLuongHeThong' => $d->SoLuongHeThong,
                    'SoLuongThucTe' => $d->SoLuongThucTe,
                    'ChenhLech' => $d->ChenhLech,
                    'TinhTrang' => $tinhTrangHienThi,
                    'isEdited' => $isEdited
                ];
            }

            $danhSachPhiu[] = [
                'MaPhieuKiemKe' => $p->MaPhieuKiemKe,
                'NgayKiemKe' => $p->NgayKiemKe,
                'TrangThai' => $p->TrangThai,
                'GhiChu' => $p->GhiChu,
                'biLech' => $biLech,
                'GiaiTrinh' => $giaiTrinh,
                'Details' => $details
            ];
        }

        return view('kiemke.kho_chinh_manager', compact('danhSachPhiu'));
    }

    /**
     * HĐ10: QUẢN LÝ HIỆU CHỈNH SỐ LIỆU ĐẾM SAI TRÊN DÒNG LÔ HÀNG THẬT
     */
    public function hieuChinhPhieu(Request $request, $maPhieu)
    {
        $request->validate([
            'ma_lo' => 'required|string',
            'thuc_te_moi' => 'required|integer|min:0'
        ]);

        $maLo = $request->ma_lo;
        $thucTeMoi = intval($request->thuc_te_moi);

        DB::transaction(function () use ($maPhieu, $maLo, $thucTeMoi) {
            $currentDetail = DB::table('ChiTietPhieuKiemKeDinhKy')
                ->where('MaPhieuKiemKe', $maPhieu)
                ->where('MaLoHang', $maLo)
                ->first();

            if ($currentDetail) {
                $chenhLechMoi = $thucTeMoi - $currentDetail->SoLuongHeThong;
                $tinhTrangDb = ($chenhLechMoi >= 0) ? 'Đủ' : 'Thiếu';

                // Cập nhật số liệu đã hiệu chỉnh trực tiếp vào bảng chi tiết thật
                DB::table('ChiTietPhieuKiemKeDinhKy')
                    ->where('MaPhieuKiemKe', $maPhieu)
                    ->where('MaLoHang', $maLo)
                    ->update([
                        'SoLuongThucTe' => $thucTeMoi,
                        'ChenhLech' => $chenhLechMoi,
                        'TinhTrang' => $tinhTrangDb
                    ]);

                // Khóa nút bấm sửa của dòng này lại
                $fixedItems = session('fixed_items', []);
                $fixedItems[$maPhieu][$maLo] = true;
                session(['fixed_items' => $fixedItems]);
            }

            // Kiểm tra xem sau khi sửa, phiếu kiểm kê này có còn dòng nào bị lệch số không
            $conLech = DB::table('ChiTietPhieuKiemKeDinhKy')
                ->where('MaPhieuKiemKe', $maPhieu)
                ->where('ChenhLech', '!=', 0)
                ->exists();

            // NẾU SỐ LIỆU KHỚP HẾT: Ẩn form giải trình, duyệt phiếu luôn và đồng bộ số thực tế vào thẻ kho lô hàng
            if (!$conLech) {
                DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update(['TrangThai' => 'Đã duyệt']);
                
                $allDetails = DB::table('ChiTietPhieuKiemKeDinhKy')->where('MaPhieuKiemKe', $maPhieu)->get();
                foreach ($allDetails as $dt) {
                    DB::table('LoHang')->where('MaLoHang', $dt->MaLoHang)->update(['SoLuongConLai' => $dt->SoLuongThucTe]);
                }
            }
        });

        return redirect()->route('quanly.khochinh.duyet');
    }

    /**
     * HĐ11 - HĐ12: TẠO PHIẾU GIẢI TRÌNH THẤT THOÁT THỰC TẾ GHI VÀO CSDL THẬT
     */
    public function taoGiaiTrinh(Request $request, $maPhieu)
    {
        $request->validate([
            'noi_dung' => 'required|string|max:255',
            'nguyen_nhan' => 'required|string|max:255'
        ]);

        DB::transaction(function () use ($request, $maPhieu) {
            $maGiaiTrinh = 'PGT' . rand(1000, 9999);

            // Ghi trực tiếp thông tin vào bảng PhieuGiaiTrinh thật trong MySQL của nhóm
            DB::table('PhieuGiaiTrinh')->insert([
                'MaPhieuGiaiTrinh' => $maGiaiTrinh,
                'NgayTao' => now()->toDateString(),
                'NoiDung' => $request->noi_dung,
                'NguyenNhan' => $request->nguyen_nhan,
                'MaTaiKhoan' => 'QL001',
                'MaPhieuKiemKe' => $maPhieu
            ]);

            // Duyệt phiếu kiểm kê gốc và chốt số lượng thực tế sau thất thoát vào hệ thống [HĐ7]
            DB::table('PhieuKiemKe')->where('MaPhieuKiemKe', $maPhieu)->update([
                'TrangThai' => 'Đã duyệt',
                'GhiChu' => 'Đã thẩm định và đính kèm phiếu giải trình gửi lên Cửa hàng trưởng.'
            ]);
            
            $allDetails = DB::table('ChiTietPhieuKiemKeDinhKy')->where('MaPhieuKiemKe', $maPhieu)->get();
            foreach ($allDetails as $dt) {
                DB::table('LoHang')->where('MaLoHang', $dt->MaLoHang)->update(['SoLuongConLai' => $dt->SoLuongThucTe]);
            }
        });

        return redirect()->route('quanly.khochinh.duyet');
    }
}