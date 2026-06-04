<?php

namespace App\Http\Controllers;

use App\Models\PhieuNhapKho;
use App\Models\PhieuNhanHang;
use App\Models\LoHang;
use App\Models\NguyenLieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhieuNhapKhoController extends Controller
{
    /**
     * Danh sách phiếu nhập kho
     */
    public function index(Request $request)
    {
        $query = PhieuNhapKho::with(['phieuNhanHang.donDatHang', 'taiKhoan']);

        if ($request->filled('trang_thai')) {
            $query->where('TrangThai', $request->trang_thai);
        }

        if ($request->filled('search')) {
            $query->where('MaPhieuNhap', 'like', '%' . $request->search . '%');
        }

        $phieuNhapKhos = $query->orderByDesc('NgayNhap')->paginate(10);

        return view('phieu-nhap-kho.index', compact('phieuNhapKhos'));
    }

    /**
     * Form xác nhận nhập kho (từ phiếu nhận hàng đã nhận đủ)
     */
    public function create($maPhieuNhan)
    {
        $phieuNhan = PhieuNhanHang::with([
            'donDatHang.chiTietDonDatHangs.nguyenLieu',
            'loHangs.nguyenLieu',
        ])->findOrFail($maPhieuNhan);

        if ($phieuNhan->TrangThai !== PhieuNhanHang::TRANG_THAI_DA_NHAN) {
            return redirect()->route('phieu-nhan-hang.show', $maPhieuNhan)
                ->with('error', 'Chỉ có thể nhập kho khi phiếu nhận ở trạng thái "Đã nhận hàng".');
        }

        // Kiểm tra đã có phiếu nhập kho chưa
        $daCoPhieu = PhieuNhapKho::where('MaPhieuNhan', $maPhieuNhan)->exists();
        if ($daCoPhieu) {
            return redirect()->route('phieu-nhan-hang.show', $maPhieuNhan)
                ->with('warning', 'Phiếu nhận hàng này đã được tạo phiếu nhập kho.');
        }

        $loHangTheoNL = $phieuNhan->loHangs->groupBy('MaNguyenLieu');

        return view('phieu-nhap-kho.create', compact('phieuNhan', 'loHangTheoNL'));
    }

    /**
     * Xác nhận nhập kho: tạo phiếu nhập, cập nhật tồn kho
     */
    public function store(Request $request, $maPhieuNhan)
    {
        $phieuNhan = PhieuNhanHang::with('loHangs.nguyenLieu')->findOrFail($maPhieuNhan);

        $request->validate([
            'ghi_chu' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $maPhieuNhap = 'PNK' . strtoupper(substr(uniqid(), -5));

            $phieuNhapKho = PhieuNhapKho::create([
                'MaPhieuNhap'  => $maPhieuNhap,
                'NgayNhap'     => Carbon::today(),
                'GhiChu'       => $request->ghi_chu,
                'TrangThai'    => PhieuNhapKho::TRANG_THAI_DA_NHAP,
                'MaTaiKhoan'   => session('tai_khoan_id', 'TK001'),
                'MaPhieuNhan'  => $maPhieuNhan,
            ]);

            // Cập nhật lô hàng: gán MaPhieuNhap và cập nhật tồn kho nguyên liệu
            foreach ($phieuNhan->loHangs as $loHang) {
                $loHang->update(['MaPhieuNhap' => $maPhieuNhap]);

                // Cộng dồn vào SoLuongTonKho của nguyên liệu
                NguyenLieu::where('MaNguyenLieu', $loHang->MaNguyenLieu)
                    ->increment('SoLuongTonKho', $loHang->SoLuongNhap);
            }

            // Cập nhật trạng thái phiếu nhận và đơn đặt hàng
            $phieuNhan->update(['TrangThai' => PhieuNhanHang::TRANG_THAI_HOAN_TAT]);
            $phieuNhan->donDatHang()->update(['TrangThai' => 'Đã nhập kho']);

            DB::commit();
            return redirect()->route('phieu-nhap-kho.show', $maPhieuNhap)
                ->with('success', 'Xác nhận nhập kho thành công! Tồn kho đã được cập nhật.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Xem chi tiết phiếu nhập kho
     */
    public function show($id)
    {
        $phieuNhapKho = PhieuNhapKho::with([
            'phieuNhanHang.donDatHang.chiTietDonDatHangs.nguyenLieu',
            'loHangs.nguyenLieu',
            'taiKhoan',
        ])->findOrFail($id);

        $loHangTheoNL = $phieuNhapKho->loHangs->groupBy('MaNguyenLieu');

        return view('phieu-nhap-kho.show', compact('phieuNhapKho', 'loHangTheoNL'));
    }

    /**
     * Xuất báo cáo nhập kho (in phiếu)
     */
    public function baoCao($id)
    {
        $phieuNhapKho = PhieuNhapKho::with([
            'phieuNhanHang.donDatHang.chiTietDonDatHangs.nguyenLieu',
            'loHangs.nguyenLieu',
            'taiKhoan',
        ])->findOrFail($id);

        $loHangTheoNL = $phieuNhapKho->loHangs->groupBy('MaNguyenLieu');

        return view('phieu-nhap-kho.bao-cao', compact('phieuNhapKho', 'loHangTheoNL'));
    }
}
