<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonHangNVController extends Controller
{
    public function index()
    {
        $orders = DB::table('dondathang as d')
            ->join('taikhoan as t', 't.MaTaiKhoan', '=', 'd.MaTaiKhoan')
            ->leftJoin('chitietdondathang as c', 'c.MaDonDatHang', '=', 'd.MaDonDatHang')
            ->select(
                'd.MaDonDatHang',
                'd.NgayDat',
                'd.TrangThai',
                'd.GhiChu',
                't.HoTen',
                DB::raw('COUNT(c.MaNguyenLieu) as SoMatHang'),
                DB::raw('COALESCE(SUM(c.SoLuongDat), 0) as TongSoLuong')
            )
            ->where(function($q) {
                $q->where('d.TrangThai', 'Chờ nhận hàng')
                  ->orWhere('d.TrangThai', 'Đang xử lý đổi/trả');
            })
            ->groupBy('d.MaDonDatHang', 'd.NgayDat', 'd.TrangThai', 'd.GhiChu', 't.HoTen')
            ->orderByDesc('d.NgayDat')
            ->paginate(10);

        return view('nhanvien.ds-don-hang', compact('orders'));
    }

    public function show($order)
    {
        $orderData = DB::table('dondathang as d')
            ->join('taikhoan as t', 't.MaTaiKhoan', '=', 'd.MaTaiKhoan')
            ->select('d.*', 't.HoTen')
            ->where('d.MaDonDatHang', $order)
            ->first();

        abort_if(!$orderData, 404);

        $items = DB::table('chitietdondathang as c')
            ->join('nguyenlieu as n', 'n.MaNguyenLieu', '=', 'c.MaNguyenLieu')
            ->select('c.*', 'n.TenNguyenLieu', 'n.DonViTinh')
            ->where('c.MaDonDatHang', $order)
            ->get();

        return view('nhanvien.tao-phieu-nhan-hang', compact('orderData', 'items'));
    }

    public function store(Request $request, $order)
    {
        $request->validate([
            'NgayNhan' => 'required|date',
            'GhiChu' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.SoLuongThucNhan' => 'required|integer|min:0',
            'items.*.NgaySanXuat' => 'required|date',
            'items.*.HanSuDung' => 'required|date|after:items.*.NgaySanXuat',
        ]);

        $currentStatus = DB::table('dondathang')->where('MaDonDatHang', $order)->value('TrangThai');
        if (!in_array($currentStatus, ['Chờ nhận hàng', 'Đang xử lý đổi/trả'])) {
            return back()->with('error', 'Đơn hàng không ở trạng thái có thể nhận!');
        }

        DB::beginTransaction();
        try {
            // Tạo phiếu nhận hàng
            $lastReceipt = DB::table('phieunhanhang')
                ->where('MaPhieuNhan', 'like', 'PN%')
                ->orderByDesc('MaPhieuNhan')
                ->first();
            $nextNumber = $lastReceipt ? ((int) substr($lastReceipt->MaPhieuNhan, 2)) + 1 : 1;
            $maPhieuNhan = 'PN' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            DB::table('phieunhanhang')->insert([
                'MaPhieuNhan' => $maPhieuNhan,
                'NgayNhan' => $request->NgayNhan,
                'GhiChu' => $request->GhiChu,
                'MaTaiKhoan' => auth()->user()->MaTaiKhoan,
                'MaDonDatHang' => $order,
            ]);

            $totalSoLuongDat = 0;
            $totalSoLuongThucNhan = 0;

            // Lưu thông tin lô hàng
            foreach ($request->items as $index => $item) {
                $totalSoLuongDat += DB::table('chitietdondathang')
                    ->where('MaDonDatHang', $order)
                    ->where('MaNguyenLieu', $item['MaNguyenLieu'])
                    ->value('SoLuongDat');

                $totalSoLuongThucNhan += $item['SoLuongThucNhan'];

                // Tạo mã lô hàng
                $lastLoHang = DB::table('lohang')
                    ->where('MaLoHang', 'like', 'LH%')
                    ->orderByDesc('MaLoHang')
                    ->first();
                $loHangNumber = $lastLoHang ? ((int) substr($lastLoHang->MaLoHang, 2)) + 1 : 1;
                $maLoHang = 'LH' . str_pad($loHangNumber, 3, '0', STR_PAD_LEFT);

                // Xác định trạng thái lô hàng
                $ngayHienTai = now();
                $hanSuDung = \Illuminate\Support\Carbon::parse($item['HanSuDung']);
                $trangThai = 'Còn hạn';
                if ($hanSuDung->isPast()) {
                    $trangThai = 'Hết hạn';
                } elseif ($hanSuDung->diffInDays($ngayHienTai) <= 15) {
                    $trangThai = 'Sắp hết hạn';
                }

                DB::table('lohang')->insert([
                    'MaLoHang' => $maLoHang,
                    'NgaySanXuat' => $item['NgaySanXuat'],
                    'HanSuDung' => $item['HanSuDung'],
                    'SoLuongNhap' => $item['SoLuongThucNhan'],
                    'SoLuongConLai' => $item['SoLuongThucNhan'],
                    'TrangThai' => $trangThai,
                    'MaNguyenLieu' => $item['MaNguyenLieu'],
                    'MaPhieuNhan' => $maPhieuNhan,
                ]);

                // Cập nhật số lượng tồn kho
                DB::table('nguyenlieu')
                    ->where('MaNguyenLieu', $item['MaNguyenLieu'])
                    ->increment('SoLuongTonKho', $item['SoLuongThucNhan']);
            }

            // Lưu lịch sử truy vết (nếu có bảng)
            if (DB::getSchemaBuilder()->hasTable('TruyVetDonDatHang')) {
                DB::table('TruyVetDonDatHang')->insert([
                    'MaDonDatHang' => $order,
                    'HanhDong' => 'Nhận hàng',
                    'TrangThaiTruoc' => $currentStatus,
                    'TrangThaiSau' => $totalSoLuongDat == $totalSoLuongThucNhan ? 'Đã nhận hàng' : 'Chờ xử lý',
                    'MaTaiKhoan' => auth()->user()->MaTaiKhoan,
                    'NoiDung' => $request->GhiChu ?? 'Nhân viên tạo phiếu nhận hàng',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Cập nhật trạng thái đơn hàng
            $trangThaiMoi = $totalSoLuongDat == $totalSoLuongThucNhan ? 'Đã nhận hàng' : 'Chờ xử lý';
            DB::table('dondathang')
                ->where('MaDonDatHang', $order)
                ->update(['TrangThai' => $trangThaiMoi]);

            DB::commit();

            if ($totalSoLuongDat == $totalSoLuongThucNhan) {
                return redirect()->route('ds-don-hang.index')
                    ->with('success', 'Tạo phiếu nhận hàng thành công! Đơn hàng đã chuyển trạng thái Đã nhận hàng.');
            } else {
                return redirect()->route('ds-don-hang.index')
                    ->with('warning', 'Tạo phiếu nhận hàng thành công! Số lượng thực nhận khác số lượng đặt. Đơn hàng chuyển trạng thái Chờ xử lý.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
