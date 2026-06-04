<?php

namespace App\Http\Controllers;

use App\Models\PhieuDoiTra;
use App\Models\PhieuNhanHang;
use App\Models\ChiTietDonDatHang;
use App\Models\LoHang;
use App\Models\NguyenLieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PhieuDoiTraController extends Controller
{
    /**
     * Danh sách phiếu đổi trả
     */
    public function index(Request $request)
    {
        $query = PhieuDoiTra::with(['phieuNhanHang', 'taiKhoan']);

        if ($request->filled('trang_thai')) {
            $query->where('TrangThaiXuLy', $request->trang_thai);
        }

        if ($request->filled('search')) {
            $query->where('MaPhieuDoiTra', 'like', '%' . $request->search . '%');
        }

        $phieuDoiTras = $query->orderByDesc('NgayTao')->paginate(10);

        return view('phieu-doi-tra.index', compact('phieuDoiTras'));
    }

    /**
     * Form tạo yêu cầu đổi/trả từ phiếu nhận hàng bị sai lệch
     */
    public function create($maPhieuNhan)
    {
        $phieuNhan = PhieuNhanHang::with([
            'donDatHang.chiTietDonDatHangs.nguyenLieu',
            'loHangs.nguyenLieu',
        ])->findOrFail($maPhieuNhan);

        if ($phieuNhan->TrangThai !== PhieuNhanHang::TRANG_THAI_CHO_XU_LY) {
            return redirect()->route('phieu-nhan-hang.show', $maPhieuNhan)
                ->with('error', 'Chỉ có thể tạo phiếu đổi/trả cho phiếu đang ở trạng thái "Chờ xử lý".');
        }

        $chiTietDon = ChiTietDonDatHang::with('nguyenLieu')
            ->where('MaDonDatHang', $phieuNhan->MaDonDatHang)
            ->get();

        // Tính sai lệch cho từng nguyên liệu
        $loHangTheoNL = $phieuNhan->loHangs->groupBy('MaNguyenLieu');
        $saiLechList  = [];

        foreach ($chiTietDon as $ct) {
            $soLuongDat  = $ct->SoLuongDat;
            $loHangs     = $loHangTheoNL->get($ct->MaNguyenLieu, collect());
            $soLuongNhan = $loHangs->sum('SoLuongNhap');
            $chenhLech   = $soLuongNhan - $soLuongDat;

            if ($chenhLech !== 0) {
                $saiLechList[] = [
                    'nguyenLieu'  => $ct->nguyenLieu,
                    'soLuongDat'  => $soLuongDat,
                    'soLuongNhan' => $soLuongNhan,
                    'chenhLech'   => $chenhLech,
                ];
            }
        }

        return view('phieu-doi-tra.create', compact('phieuNhan', 'saiLechList'));
    }

    /**
     * Lưu phiếu đổi/trả
     */
    public function store(Request $request, $maPhieuNhan)
    {
        $phieuNhan = PhieuNhanHang::findOrFail($maPhieuNhan);

        $request->validate([
            'nguyen_lieu'               => 'required|array|min:1',
            'nguyen_lieu.*.loai_xu_ly'  => 'required|in:Đổi hàng,Trả hàng',
            'nguyen_lieu.*.ly_do'       => 'required|string|max:255',
            'nguyen_lieu.*.so_luong'    => 'required|integer|min:1',
        ], [
            'nguyen_lieu.*.loai_xu_ly.required' => 'Vui lòng chọn loại xử lý.',
            'nguyen_lieu.*.ly_do.required'      => 'Vui lòng nhập lý do.',
            'nguyen_lieu.*.so_luong.min'        => 'Số lượng phải lớn hơn 0.',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->nguyen_lieu as $maNL => $data) {
                $maPhieuDT = 'DT' . strtoupper(substr(uniqid(), -6));

                $phieuDoiTra = PhieuDoiTra::create([
                    'MaPhieuDoiTra' => $maPhieuDT,
                    'NgayTao'       => Carbon::today(),
                    'LoaiXuLy'      => $data['loai_xu_ly'],
                    'LyDo'          => $data['ly_do'],
                    'TrangThaiXuLy' => PhieuDoiTra::TRANG_THAI_DANG_XU_LY,
                    'MaTaiKhoan'    => session('tai_khoan_id', 'TK001'),
                    'MaPhieuNhan'   => $maPhieuNhan,
                ]);

                // Liên kết lô hàng tương ứng với phiếu đổi trả
                LoHang::where('MaPhieuNhan', $maPhieuNhan)
                    ->where('MaNguyenLieu', $maNL)
                    ->update(['MaPhieuDoiTra' => $maPhieuDT]);
            }

            // Cập nhật trạng thái phiếu nhận
            $phieuNhan->update(['TrangThai' => PhieuNhanHang::TRANG_THAI_DANG_DOI_TRA]);

            DB::commit();
            return redirect()->route('phieu-doi-tra.index')
                ->with('success', 'Tạo yêu cầu đổi/trả thành công. Đang chờ kho tổng xử lý.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Xem chi tiết phiếu đổi/trả
     */
    public function show($id)
    {
        $phieuDoiTra = PhieuDoiTra::with([
            'phieuNhanHang.donDatHang',
            'loHangs.nguyenLieu',
            'taiKhoan',
        ])->findOrFail($id);

        return view('phieu-doi-tra.show', compact('phieuDoiTra'));
    }

    /**
     * Đánh dấu đã xử lý xong đổi/trả (kho tổng đã giao lại)
     */
    public function capNhatXuLy(Request $request, $id)
    {
        $phieuDoiTra = PhieuDoiTra::with('phieuNhanHang')->findOrFail($id);

        DB::beginTransaction();
        try {
            $phieuDoiTra->update(['TrangThaiXuLy' => PhieuDoiTra::TRANG_THAI_DA_XU_LY]);

            // Kiểm tra tất cả phiếu đổi trả của phiếu nhận đã xử lý hết chưa
            $conDoiTra = PhieuDoiTra::where('MaPhieuNhan', $phieuDoiTra->MaPhieuNhan)
                ->where('TrangThaiXuLy', PhieuDoiTra::TRANG_THAI_DANG_XU_LY)
                ->count();

            if ($conDoiTra === 0) {
                // Cho phép nhân viên nhập lại số lượng
                $phieuDoiTra->phieuNhanHang->update([
                    'TrangThai' => PhieuNhanHang::TRANG_THAI_DANG_DOI_TRA
                ]);
            }

            DB::commit();
            return redirect()->route('phieu-doi-tra.show', $id)
                ->with('success', 'Đã cập nhật trạng thái xử lý. Nhân viên có thể nhập lại số lượng thực nhận.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
