<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonHangNVController extends Controller
{
    private const STATUS_WAITING_RECEIVE = 'Chờ nhận hàng';
    private const STATUS_RECEIVED = 'Đã nhận hàng';
    private const STATUS_WAITING_PROCESS = 'Chờ xử lý';

    public function index()
    {
        $orders = DB::table('DonDatHang as d')
            ->join('TaiKhoan as t', 't.MaTaiKhoan', '=', 'd.MaTaiKhoan')
            ->leftJoin('ChiTietDonDatHang as c', 'c.MaDonDatHang', '=', 'd.MaDonDatHang')
            ->select(
                'd.MaDonDatHang',
                'd.NgayDat',
                'd.TrangThai',
                'd.GhiChu',
                't.HoTen',
                DB::raw('COUNT(c.MaNguyenLieu) as SoMatHang'),
                DB::raw('COALESCE(SUM(c.SoLuongDat), 0) as TongSoLuong')
            )
            ->whereIn('d.TrangThai', $this->receivableStatuses())
            ->groupBy('d.MaDonDatHang', 'd.NgayDat', 'd.TrangThai', 'd.GhiChu', 't.HoTen')
            ->orderByDesc('d.NgayDat')
            ->paginate(10);

        return view('nhanvien.ds-don-hang', compact('orders'));
    }

    public function show($order)
    {
        $orderData = DB::table('DonDatHang as d')
            ->join('TaiKhoan as t', 't.MaTaiKhoan', '=', 'd.MaTaiKhoan')
            ->select('d.*', 't.HoTen')
            ->where('d.MaDonDatHang', $order)
            ->first();

        abort_if(!$orderData, 404);

        $items = DB::table('ChiTietDonDatHang as c')
            ->join('NguyenLieu as n', 'n.MaNguyenLieu', '=', 'c.MaNguyenLieu')
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
            'items.*.NgaySanXuat' => 'required|date|before_or_equal:today',
            'items.*.HanSuDung' => 'required|date|after:items.*.NgaySanXuat',
        ], [
            'items.*.NgaySanXuat.before_or_equal' => 'Ngày sản xuất không được lớn hơn ngày hiện tại.',
            'items.*.HanSuDung.after' => 'Hạn sử dụng phải lớn hơn Ngày sản xuất.',
        ]);

        $currentStatus = DB::table('DonDatHang')->where('MaDonDatHang', $order)->value('TrangThai');
        if (! in_array($currentStatus, $this->receivableStatuses(), true)) {
            return back()->with('error', 'Đơn hàng không ở trạng thái có thể nhận!');
        }

        DB::beginTransaction();
        try {
            // Tạo phiếu nhận hàng
            $lastReceipt = DB::table('PhieuNhanHang')
                ->where('MaPhieuNhan', 'like', 'PN%')
                ->orderByDesc('MaPhieuNhan')
                ->first();
            $nextNumber = $lastReceipt ? ((int) substr($lastReceipt->MaPhieuNhan, 2)) + 1 : 1;
            $maPhieuNhan = 'PN' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            DB::table('PhieuNhanHang')->insert([
                'MaPhieuNhan' => $maPhieuNhan,
                'NgayNhan' => $request->NgayNhan,
                'GhiChu' => $request->GhiChu,
                'MaTaiKhoan' => auth()->user()->MaTaiKhoan,
                'MaDonDatHang' => $order,
            ]);

            $totalSoLuongDat = 0;
            $totalSoLuongThucNhan = 0;
            $hasExpiredItem = false;

            // Lưu thông tin lô hàng
            foreach ($request->items as $item) {
                $totalSoLuongDat += DB::table('ChiTietDonDatHang')
                    ->where('MaDonDatHang', $order)
                    ->where('MaNguyenLieu', $item['MaNguyenLieu'])
                    ->value('SoLuongDat');

                $totalSoLuongThucNhan += $item['SoLuongThucNhan'];

                // Tạo mã lô hàng
                $lastLoHang = DB::table('LoHang')
                    ->where('MaLoHang', 'like', 'LH%')
                    ->orderByDesc('MaLoHang')
                    ->first();
                $loHangNumber = $lastLoHang ? ((int) substr($lastLoHang->MaLoHang, 2)) + 1 : 1;
                $maLoHang = 'LH' . str_pad($loHangNumber, 3, '0', STR_PAD_LEFT);

                // Xác định trạng thái lô hàng
                $ngayHienTai = now()->startOfDay();
                $hanSuDung = \Illuminate\Support\Carbon::parse($item['HanSuDung'])->startOfDay();
                $trangThai = 'Còn hạn';
                if ($hanSuDung->isBefore($ngayHienTai)) {
                    $trangThai = 'Hết hạn';
                    $hasExpiredItem = true;
                } elseif ($hanSuDung->diffInDays($ngayHienTai) <= 15) {
                    $trangThai = 'Sắp hết hạn';
                }

                DB::table('LoHang')->insert([
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
                DB::table('NguyenLieu')
                    ->where('MaNguyenLieu', $item['MaNguyenLieu'])
                    ->increment('SoLuongTonKho', $item['SoLuongThucNhan']);
            }

            // Xác định trạng thái mới của đơn hàng
            $isFullyReceived = ($totalSoLuongDat == $totalSoLuongThucNhan);
            $trangThaiMoi = ($isFullyReceived && !$hasExpiredItem) ? self::STATUS_RECEIVED : self::STATUS_WAITING_PROCESS;

            // Lưu lịch sử truy vết (nếu có bảng)
            if (DB::getSchemaBuilder()->hasTable('TruyVetDonDatHang')) {
                DB::table('TruyVetDonDatHang')->insert([
                    'MaDonDatHang' => $order,
                    'HanhDong' => 'Nhận hàng',
                    'TrangThaiTruoc' => $currentStatus,
                    'TrangThaiSau' => $trangThaiMoi,
                    'MaTaiKhoan' => auth()->user()->MaTaiKhoan,
                    'NoiDung' => $request->GhiChu ?? 'Nhân viên tạo phiếu nhận hàng' . ($hasExpiredItem ? ' (Có hàng hết hạn)' : ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Cập nhật trạng thái đơn hàng
            DB::table('DonDatHang')
                ->where('MaDonDatHang', $order)
                ->update(['TrangThai' => $trangThaiMoi]);

            DB::commit();

            if ($trangThaiMoi === self::STATUS_RECEIVED) {
                return redirect()->route('ds-don-hang.index')
                    ->with('success', 'Tạo phiếu nhận hàng thành công! Đơn hàng đã chuyển trạng thái Đã nhận hàng.');
            } else {
                $msg = 'Tạo phiếu nhận hàng thành công! ';
                if ($hasExpiredItem) {
                    $msg .= 'Phát hiện nguyên liệu đã hết hạn sử dụng. ';
                }
                if (!$isFullyReceived) {
                    $msg .= 'Số lượng thực nhận khác số lượng đặt. ';
                }
                $msg .= 'Đơn hàng chuyển trạng thái Chờ xử lý.';

                return redirect()->route('ds-don-hang.index')
                    ->with('warning', $msg);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function receivableStatuses(): array
    {
        return [
            self::STATUS_WAITING_RECEIVE,
        ];
    }
}
