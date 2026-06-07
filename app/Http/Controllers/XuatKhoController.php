<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhieuXuatKho;
use App\Models\ChiTietPhieuXuat;
use App\Models\NguyenLieu;
use App\Models\LoHang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class XuatKhoController extends Controller
{
    /**
     * Màn hình "Danh sách phiếu xuất kho" (Giao diện Quản lý)
     * Hiển thị toàn bộ danh sách phiếu phục vụ mục tiêu theo dõi tổng quan
     */
    public function index(Request $request)
    {
        $danhSachPhieu = PhieuXuatKho::orderBy('NgayXuat', 'desc')
            ->orderBy('MaPhieuXuat', 'desc')
            ->get();

        return view('quanly.danh-sach-phieu', compact('danhSachPhieu'));
    }

    /**
     * Màn hình "Khởi tạo yêu cầu xuất kho đầu ngày" (Giao diện Quản lý)
     * Hỗ trợ tìm kiếm theo tên nguyên liệu và lọc theo trạng thái tồn kho thực tế
     */
    public function create(Request $request)
    {
        $query = NguyenLieu::query();
        // Xử lý tìm kiếm theo tên nguyên liệu dựa trên từ khóa nhập vào từ thanh search
        if ($request->has('search') && $request->search != '') {
            $query->where('TenNguyenLieu', 'LIKE', '%' . $request->search . '%');
        }

        // Lọc hiển thị nguyên liệu "Chỉ còn tồn kho" khi công tắc được kích hoạt
        if ($request->has('chi_con_ton') && $request->chi_con_ton == '1') {
            $query->where('SoLuongTonKho', '>', 0);
        }

        $danhSachNguyenLieu = $query->get();

        return view('quanly.tao-phieu', compact('danhSachNguyenLieu'));
    }

    /**
     * Kịch bản ca sử dụng: Tạo phiếu xuất kho (Quản lý bấm Xác nhận)
     * Tự động sinh mã phiếu, áp dụng FIFO để tính toán phân bổ vào các lô hàng tương ứng
     */
    public function store(Request $request)
    {
        $request->validate([
            'nguyen_lieu' => 'required|array',
        ], [
            'nguyen_lieu.required' => 'Vui lòng chọn ít nhất một nguyên liệu để xuất kho.',
        ]);

        // Check that at least one item has quantity > 0
        $hasValidItem = false;
        foreach ($request->input('nguyen_lieu', []) as $soLuong) {
            if ($soLuong > 0) {
                $hasValidItem = true;
                break;
            }
        }
        if (!$hasValidItem) {
            return back()->withErrors(['error' => 'Vui lòng nhập số lượng lớn hơn 0 cho ít nhất một nguyên liệu.']);
        }

        DB::beginTransaction();
        try {
            // Cơ chế tự động sinh mã phiếu xuất kho tăng dần liên tục (PX001, PX002, PX003,...)
            $lastPhieu = PhieuXuatKho::orderBy('MaPhieuXuat', 'desc')->first();
            $nextNumber = $lastPhieu ? ((int)substr($lastPhieu->MaPhieuXuat, 2)) + 1 : 1;
            $maPhieuXuat = 'PX' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Khởi tạo bản ghi phiếu xuất mới
            $phieuXuat = PhieuXuatKho::create([
                'MaPhieuXuat' => $maPhieuXuat,
                'NgayXuat'    => Carbon::today()->format('Y-m-d'),
                'TrangThai'   => 'Chờ xuất hàng',
                'MaTaiKhoan'  => auth()->check() ? auth()->user()->MaTaiKhoan : 'QL001' // Tự động lấy user đang đăng nhập
            ]);

            foreach ($request->input('nguyen_lieu') as $maNguyenLieu => $soLuongYeuCau) {
                if ($soLuongYeuCau <= 0) continue;

                $soLuongCanPhanBo = $soLuongYeuCau;
                $tenNguyenLieu = NguyenLieu::where('MaNguyenLieu', $maNguyenLieu)->value('TenNguyenLieu') ?? $maNguyenLieu;

                // THUẬT TOÁN FIFO
                $cacLoHang = LoHang::where('MaNguyenLieu', $maNguyenLieu)
                    ->where('SoLuongConLai', '>', 0)
                    ->where('TrangThai', '!=', 'Hết hạn')
                    ->orderBy('HanSuDung', 'asc')
                    ->get();

                if ($cacLoHang->isEmpty()) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'error' => 'Nguyên liệu ' . $tenNguyenLieu . ' hiện chưa có lô hàng khả dụng để xuất kho.'
                    ]);
                }

                foreach ($cacLoHang as $loHang) {
                    if ($soLuongCanPhanBo <= 0) break;

                    if ($loHang->SoLuongConLai >= $soLuongCanPhanBo) {
                        ChiTietPhieuXuat::create([
                            'MaPhieuXuat' => $maPhieuXuat,
                            'MaLoHang'    => $loHang->MaLoHang,
                            'SoLuongXuat' => $soLuongCanPhanBo
                        ]);
                        $soLuongCanPhanBo = 0;
                    } else {
                        ChiTietPhieuXuat::create([
                            'MaPhieuXuat' => $maPhieuXuat,
                            'MaLoHang'    => $loHang->MaLoHang,
                            'SoLuongXuat' => $loHang->SoLuongConLai
                        ]);
                        $soLuongCanPhanBo -= $loHang->SoLuongConLai;
                    }
                }

                if ($soLuongCanPhanBo > 0) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'error' => 'Số lượng tồn trong các lô của nguyên liệu ' . $tenNguyenLieu . ' không đủ để đáp ứng yêu cầu xuất kho.'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('xuatkho.index')->with('success', 'Khởi tạo thành công phiếu xuất kho ' . $maPhieuXuat . ' với trạng thái Chờ xuất hàng.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Không thể tạo phiếu xuất kho. Hệ thống đã hoàn tác dữ liệu. Chi tiết lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Màn hình "Tiếp nhận phiếu xuất kho" (Giao diện dành cho Nhân viên)
     */
    public function nhanVienIndex()
    {
        $danhSachChoXuat = PhieuXuatKho::where('TrangThai', 'Chờ xuất hàng')
            ->orderBy('NgayXuat', 'asc')
            ->get();

        return view('nhanvien.tiep-nhan-phieu', compact('danhSachChoXuat'));
    }

    /**
     * Màn hình "Chi tiết phiếu xuất kho" (Giao diện dành cho Nhân viên)
     */
    public function show(string $id)
    {
        $phieuXuat = PhieuXuatKho::where('MaPhieuXuat', $id)->firstOrFail();

        // Kết hợp dữ liệu (Join) để lấy thông tin trực quan đưa lên form
        $chiTietPhieu = ChiTietPhieuXuat::where('MaPhieuXuat', $id)
            ->join('LoHang', 'chitietphieuxuat.MaLoHang', '=', 'LoHang.MaLoHang')
            ->join('NguyenLieu', 'LoHang.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
            ->select(
                'NguyenLieu.MaNguyenLieu',
                'NguyenLieu.TenNguyenLieu',
                'NguyenLieu.DonViTinh',
                'chitietphieuxuat.MaLoHang',
                'chitietphieuxuat.SoLuongXuat'
            )
            ->get();
        $item = $phieuXuat;

        return view('nhanvien.chi-tiet-phieu', compact('phieuXuat', 'chiTietPhieu', 'item'));
    }

    /**
     * Kịch bản ca sử dụng: Xác nhận hoàn thành xuất hàng (Nhân viên bấm Hoàn tất)
     */
    public function hoanTatXuatKho(Request $request, string $id)
    {
        $request->validate([
            'thuc_lay' => 'required|array',
        ], [
            'thuc_lay.required' => 'Vui lòng điền số lượng thực lấy cho các nguyên liệu.',
        ]);

        DB::beginTransaction();
        try {
            $phieuXuat = PhieuXuatKho::where('MaPhieuXuat', $id)->firstOrFail();

            if ($phieuXuat->TrangThai === 'Hoàn tất') {
                return redirect()->back()->withErrors(['error' => 'Phiếu xuất kho này đã được xử lý hoàn tất trước đó.']);
            }

            foreach ($request->input('thuc_lay') as $maLoHang => $soLuongThucTe) {
                $soLuongThucTe = (int) $soLuongThucTe;
                if ($soLuongThucTe < 0) continue;

                $chiTiet = ChiTietPhieuXuat::where('MaPhieuXuat', $id)
                    ->where('MaLoHang', $maLoHang)
                    ->first();

                if (!$chiTiet) continue;

                // 1. KIỂM TRA RÀNG BUỘC CỨNG: Thực lấy phải bằng Yêu cầu
                if ($soLuongThucTe != $chiTiet->SoLuongXuat) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'error' => 'Lỗi tại lô ' . $maLoHang . ': Số lượng thực lấy (' . $soLuongThucTe . ') phải khớp chính xác với yêu cầu hệ thống chỉ định (' . $chiTiet->SoLuongXuat . '). Vui lòng xuất đúng số lượng!'
                    ]);
                }

                $loHang = LoHang::where('MaLoHang', $maLoHang)->first();
                if (!$loHang) continue;

                // 2. Trừ kho lô hàng
                if ($loHang->SoLuongConLai < $soLuongThucTe) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'error' => 'Số lượng tồn kho hiện tại của lô ' . $maLoHang . ' không đủ để trừ. Dữ liệu kho có thể đang bị sai lệch.'
                    ]);
                }

                $loHang->SoLuongConLai -= $soLuongThucTe;
                if ($loHang->SoLuongConLai == 0) {
                    $loHang->TrangThai = 'Hết hàng';
                }
                $loHang->save();

                // 3. Trừ kho tổng nguyên liệu
                $nguyenLieu = NguyenLieu::where('MaNguyenLieu', $loHang->MaNguyenLieu)->first();
                if ($nguyenLieu) {
                    $nguyenLieu->SoLuongTonKho -= $soLuongThucTe;
                    if ($nguyenLieu->SoLuongTonKho < 0) {
                        $nguyenLieu->SoLuongTonKho = 0;
                    }
                    $nguyenLieu->save();
                }
            }

            // 4. Đổi trạng thái phiếu
            $phieuXuat->update([
                'TrangThai' => 'Hoàn tất'
            ]);

            DB::commit();
            return redirect()->route('nhanvien.phieuxuat')->with('success', 'Xác nhận hoàn thành xuất hàng thành công. Hệ thống đã tự động trừ tồn kho nguyên liệu.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Không thể xác nhận hoàn tất xuất kho. Chi tiết lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Xem chi tiết phiếu dành cho Quản lý
     */
    public function quanLyShow(string $id)
    {
        $phieuXuat = PhieuXuatKho::where('MaPhieuXuat', $id)->firstOrFail();

        $chiTietPhieu = ChiTietPhieuXuat::where('MaPhieuXuat', $id)
            ->join('LoHang', 'chitietphieuxuat.MaLoHang', '=', 'LoHang.MaLoHang')
            ->join('NguyenLieu', 'LoHang.MaNguyenLieu', '=', 'NguyenLieu.MaNguyenLieu')
            ->select(
                'NguyenLieu.MaNguyenLieu',
                'NguyenLieu.TenNguyenLieu',
                'NguyenLieu.DonViTinh',
                'chitietphieuxuat.MaLoHang',
                'chitietphieuxuat.SoLuongXuat'
            )
            ->get();

        return view('quanly.chi-tiet-phieu', compact('phieuXuat', 'chiTietPhieu'));
    }
}
