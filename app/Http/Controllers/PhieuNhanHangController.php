<?php

namespace App\Http\Controllers;

use App\Models\PhieuNhanHang;
use App\Models\DonDatHang;
use App\Models\ChiTietDonDatHang;
use App\Models\LoHang;
use App\Models\NguyenLieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PhieuNhanHangController extends Controller
{
    /**
     * Danh sách phiếu nhận hàng (Chờ nhận hàng / Đang xử lý đổi trả)
     */
    public function index(Request $request)
    {
        $query = PhieuNhanHang::with(['donDatHang', 'taiKhoan'])
            ->whereIn('TrangThai', [
                PhieuNhanHang::TRANG_THAI_CHO_NHAN,
                PhieuNhanHang::TRANG_THAI_DANG_DOI_TRA,
                PhieuNhanHang::TRANG_THAI_CHO_XU_LY,
                PhieuNhanHang::TRANG_THAI_DA_NHAN,
                PhieuNhanHang::TRANG_THAI_HOAN_TAT,
            ]);

        // Lọc theo trạng thái nếu có
        if ($request->filled('trang_thai')) {
            $query->where('TrangThai', $request->trang_thai);
        }

        // Tìm kiếm theo mã phiếu
        if ($request->filled('search')) {
            $query->where('MaPhieuNhan', 'like', '%' . $request->search . '%');
        }

        $phieuNhanHangs = $query->orderByDesc('NgayNhan')->paginate(10);

        return view('phieu-nhan-hang.index', compact('phieuNhanHangs'));
    }

    /**
     * Xem chi tiết phiếu nhận hàng
     */
    public function show($id)
    {
        $phieuNhan = PhieuNhanHang::with([
            'donDatHang.chiTietDonDatHangs.nguyenLieu',
            'loHangs.nguyenLieu',
            'taiKhoan',
            'phieuDoiTras',
        ])->findOrFail($id);

        // Lấy chi tiết đơn đặt hàng (số lượng đặt)
        $chiTietDon = ChiTietDonDatHang::with('nguyenLieu')
            ->where('MaDonDatHang', $phieuNhan->MaDonDatHang)
            ->get();

        // Nhóm lô hàng đã nhận theo nguyên liệu
        $loHangTheoNL = $phieuNhan->loHangs->groupBy('MaNguyenLieu');

        return view('phieu-nhan-hang.show', compact('phieuNhan', 'chiTietDon', 'loHangTheoNL'));
    }

    /**
     * Form nhập số lượng thực nhận
     */
    public function nhapSoLuong($id)
    {
        $phieuNhan = PhieuNhanHang::with([
            'donDatHang.chiTietDonDatHangs.nguyenLieu',
            'loHangs.nguyenLieu',
        ])->findOrFail($id);

        if (!in_array($phieuNhan->TrangThai, [
            PhieuNhanHang::TRANG_THAI_CHO_NHAN,
            PhieuNhanHang::TRANG_THAI_DANG_DOI_TRA,
        ])) {
            return redirect()->route('phieu-nhan-hang.show', $id)
                ->with('error', 'Phiếu này không ở trạng thái có thể nhập số lượng.');
        }

        $chiTietDon = ChiTietDonDatHang::with('nguyenLieu')
            ->where('MaDonDatHang', $phieuNhan->MaDonDatHang)
            ->get();

        return view('phieu-nhan-hang.nhap-so-luong', compact('phieuNhan', 'chiTietDon'));
    }

    /**
     * Lưu số lượng thực nhận và tạo lô hàng
     */
    public function luuSoLuong(Request $request, $id)
    {
        $phieuNhan = PhieuNhanHang::with('donDatHang.chiTietDonDatHangs')->findOrFail($id);

        $request->validate([
            'nguyen_lieu'                   => 'required|array',
            'nguyen_lieu.*.so_luong_nhan'   => 'required|integer|min:0',
            'nguyen_lieu.*.nsx'             => 'required|date',
            'nguyen_lieu.*.hsd'             => 'required|date|after:nguyen_lieu.*.nsx',
        ], [
            'nguyen_lieu.*.so_luong_nhan.required' => 'Vui lòng nhập số lượng thực nhận.',
            'nguyen_lieu.*.so_luong_nhan.min'      => 'Số lượng không được âm.',
            'nguyen_lieu.*.nsx.required'           => 'Vui lòng nhập ngày sản xuất.',
            'nguyen_lieu.*.hsd.required'           => 'Vui lòng nhập hạn sử dụng.',
            'nguyen_lieu.*.hsd.after'              => 'Hạn sử dụng phải sau ngày sản xuất.',
        ]);

        DB::beginTransaction();
        try {
            $chiTietDon = ChiTietDonDatHang::where('MaDonDatHang', $phieuNhan->MaDonDatHang)->get()->keyBy('MaNguyenLieu');
            $coSaiLech = false;

            // Xóa lô hàng cũ của phiếu nhận này (nếu nhập lại)
            LoHang::where('MaPhieuNhan', $phieuNhan->MaPhieuNhan)->delete();

            foreach ($request->nguyen_lieu as $maNL => $data) {
                $soLuongDat   = $chiTietDon[$maNL]->SoLuongDat ?? 0;
                $soLuongNhan  = (int) $data['so_luong_nhan'];
                $maLoHang     = 'LH' . strtoupper(uniqid());

                // Xác định trạng thái lô hàng
                $hsd = Carbon::parse($data['hsd']);
                $now = Carbon::now();
                $trangThaiLo = 'Còn hạn';
                if ($hsd->lt($now)) {
                    $trangThaiLo = 'Hết hạn';
                } elseif ($hsd->diffInDays($now) <= 3) {
                    $trangThaiLo = 'Cận hạn';
                }

                LoHang::create([
                    'MaLoHang'      => substr($maLoHang, 0, 10),
                    'NgaySanXuat'   => $data['nsx'],
                    'HanSuDung'     => $data['hsd'],
                    'SoLuongNhap'   => $soLuongNhan,
                    'SoLuongConLai' => $soLuongNhan,
                    'TrangThai'     => $trangThaiLo,
                    'MaNguyenLieu'  => $maNL,
                    'MaPhieuNhan'   => $phieuNhan->MaPhieuNhan,
                    'MaPhieuDoiTra' => '',
                    'MaPhieuNhap'   => '',
                ]);

                if ($soLuongNhan !== $soLuongDat) {
                    $coSaiLech = true;
                }
            }

            // Cập nhật trạng thái phiếu
            if ($coSaiLech) {
                $phieuNhan->update(['TrangThai' => PhieuNhanHang::TRANG_THAI_CHO_XU_LY]);
                DB::commit();
                return redirect()->route('phieu-nhan-hang.show', $id)
                    ->with('warning', 'Phát hiện sai lệch số lượng. Vui lòng tạo yêu cầu đổi/trả.');
            } else {
                $phieuNhan->update(['TrangThai' => PhieuNhanHang::TRANG_THAI_DA_NHAN]);
                DB::commit();
                return redirect()->route('phieu-nhan-hang.show', $id)
                    ->with('success', 'Đã lưu số lượng thực nhận. Số lượng khớp với đơn đặt hàng.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }
}
