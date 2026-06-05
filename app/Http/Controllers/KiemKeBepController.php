<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KiemKeBepController extends Controller
{
    public function index(Request $request)
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'nguyenlieu']);
        $wasteHeaderTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);
        $wasteDetailTable = $this->resolveExistingTable(['ChiTietPhieuHuy', 'chitietphieuhuy']);

        $rejectedReport = null;
        if ($reportTable !== null) {
            $rejectedReport = DB::table($reportTable)
                ->where('LoaiKiemKe', $this->reportTypeEndOfDay())
                ->where('TrangThai', $this->statusRejected())
                ->where('MaTaiKhoan', Auth::id())
                ->orderByDesc('NgayKiemKe')
                ->orderByDesc('MaPhieuKiemKe')
                ->first();
        }

        $detailMap = collect();
        if ($rejectedReport && $detailTable !== null) {
            $detailMap = DB::table($detailTable)
                ->where('MaPhieuKiemKe', $rejectedReport->MaPhieuKiemKe)
                ->get()
                ->keyBy('MaNguyenLieu');
        }

        $wasteQtyMap = [];
        $wasteReasonMap = [];
        if ($rejectedReport && $wasteHeaderTable !== null && $wasteDetailTable !== null) {
            $wasteReport = DB::table($wasteHeaderTable)
                ->where('MaPhieuKiemKe', $rejectedReport->MaPhieuKiemKe)
                ->first();

            if ($wasteReport) {
                $wasteQtyMap = DB::table($wasteDetailTable)
                    ->where('MaPhieuHuy', $wasteReport->MaPhieuHuy)
                    ->pluck('SoLuongHuy', 'MaNguyenLieu')
                    ->map(fn ($value) => (int) $value)
                    ->toArray();

                $wasteReasonMap = $this->parseWasteReasons($wasteReport->LyDoHuy);
            }
        }

        $nguyenLieusDb = $ingredientTable !== null
            ? DB::table($ingredientTable)->orderBy('TenNguyenLieu')->get()
            : collect();

        $inventorySnapshot = $this->buildKitchenStockSnapshot(now()->toDateString(), $reportTable, $detailTable);
        $nguyenLieuForm = [];
        foreach ($nguyenLieusDb as $nguyenLieu) {
            $chiTiet = $detailMap->get($nguyenLieu->MaNguyenLieu);

            $nguyenLieuForm[] = [
                'ma_nl' => $nguyenLieu->MaNguyenLieu,
                'ten_nl' => $nguyenLieu->TenNguyenLieu,
                'don_vi_tinh' => $nguyenLieu->DonViTinh ?? '',
                'so_luong_he_thong' => (int) ($inventorySnapshot['system'][$nguyenLieu->MaNguyenLieu] ?? ($chiTiet->SoLuongHeThong ?? 0)),
                'old_hoan_kho' => (int) ($chiTiet->SoLuongThucTe ?? 0),
                'old_hang_huy' => (int) ($wasteQtyMap[$nguyenLieu->MaNguyenLieu] ?? 0),
                'old_ly_do_huy' => $wasteReasonMap[$nguyenLieu->MaNguyenLieu] ?? '',
            ];
        }

        return view('kiemke.kiem_ke_bep', compact('nguyenLieuForm', 'rejectedReport'));
    }

    public function store(Request $request)
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'nguyenlieu']);
        $wasteHeaderTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);
        $wasteDetailTable = $this->resolveExistingTable(['ChiTietPhieuHuy', 'chitietphieuhuy']);

        if ($reportTable === null || $detailTable === null || $ingredientTable === null || $wasteHeaderTable === null || $wasteDetailTable === null) {
            return back()->with('error', 'Hệ thống chưa đủ cấu trúc dữ liệu để lập báo cáo kiểm kê cuối ngày.');
        }

        $request->validate([
            'ma_phieu_cu' => ['nullable', 'string'],
            'kiem_ke' => ['required', 'array', 'min:1'],
            'kiem_ke.*.hoan_kho' => ['required', 'integer', 'min:0'],
            'kiem_ke.*.hang_huy' => ['required', 'integer', 'min:0'],
            'kiem_ke.*.ly_do_huy' => ['nullable', 'string', 'max:255'],
        ]);

        $requestKiemKe = $request->input('kiem_ke', []);
        $ingredientMap = DB::table($ingredientTable)
            ->whereIn('MaNguyenLieu', array_keys($requestKiemKe))
            ->get()
            ->keyBy('MaNguyenLieu');

        $validationMessages = [];
        foreach ($requestKiemKe as $maNguyenLieu => $data) {
            if (! $ingredientMap->has($maNguyenLieu)) {
                $validationMessages["kiem_ke.$maNguyenLieu"] = 'Nguyên liệu kiểm kê không hợp lệ.';
                continue;
            }

            $hangHuy = (int) ($data['hang_huy'] ?? 0);
            if ($hangHuy > 0 && trim((string) ($data['ly_do_huy'] ?? '')) === '') {
                $validationMessages["kiem_ke.$maNguyenLieu.ly_do_huy"] = 'Phải nhập lý do hủy khi có số lượng hủy.';
            }
        }

        if ($validationMessages !== []) {
            return back()->withErrors($validationMessages)->withInput();
        }
        DB::transaction(function () use ($request, $requestKiemKe, $ingredientMap, $reportTable, $detailTable, $wasteHeaderTable, $wasteDetailTable) {
            $inventorySnapshot = $this->buildKitchenStockSnapshot(now()->toDateString(), $reportTable, $detailTable);
            $maPhieuKiemKe = $request->input('ma_phieu_cu');

            if ($maPhieuKiemKe) {
                $existingReport = DB::table($reportTable)
                    ->where('MaPhieuKiemKe', $maPhieuKiemKe)
                    ->where('MaTaiKhoan', Auth::id())
                    ->where('LoaiKiemKe', $this->reportTypeEndOfDay())
                    ->where('TrangThai', $this->statusRejected())
                    ->first();

                if (! $existingReport) {
                    $maPhieuKiemKe = null;
                }
            }

            if (! $maPhieuKiemKe) {
                $maPhieuKiemKe = $this->generateNextCode($reportTable, 'MaPhieuKiemKe', 'PKK');
                DB::table($reportTable)->insert([
                    'MaPhieuKiemKe' => $maPhieuKiemKe,
                    'NgayKiemKe' => now()->toDateString(),
                    'LoaiKiemKe' => $this->reportTypeEndOfDay(),
                    'TrangThai' => $this->statusPending(),
                    'GhiChu' => null,
                    'MaTaiKhoan' => Auth::id(),
                ]);
            } else {
                DB::table($reportTable)
                    ->where('MaPhieuKiemKe', $maPhieuKiemKe)
                    ->update([
                        'NgayKiemKe' => now()->toDateString(),
                        'TrangThai' => $this->statusPending(),
                        'GhiChu' => null,
                    ]);
            }

            DB::table($detailTable)->where('MaPhieuKiemKe', $maPhieuKiemKe)->delete();

            $oldWasteHeader = DB::table($wasteHeaderTable)
                ->where('MaPhieuKiemKe', $maPhieuKiemKe)
                ->first();

            if ($oldWasteHeader) {
                DB::table($wasteDetailTable)->where('MaPhieuHuy', $oldWasteHeader->MaPhieuHuy)->delete();
                DB::table($wasteHeaderTable)->where('MaPhieuHuy', $oldWasteHeader->MaPhieuHuy)->delete();
            }

            $wasteItems = [];
            $wasteReasonLines = [];

            foreach ($requestKiemKe as $maNguyenLieu => $data) {
                $ingredient = $ingredientMap->get($maNguyenLieu);
                $soLuongHeThong = (int) ($inventorySnapshot['system'][$maNguyenLieu] ?? 0);
                $hoanKho = (int) ($data['hoan_kho'] ?? 0);
                $hangHuy = (int) ($data['hang_huy'] ?? 0);
                $chenhLech = ($hoanKho + $hangHuy) - $soLuongHeThong;
                $tinhTrang = $chenhLech === 0 ? $this->matchExact() : ($chenhLech > 0 ? $this->matchOver() : $this->matchUnder());

                DB::table($detailTable)->insert([
                    'MaPhieuKiemKe' => $maPhieuKiemKe,
                    'MaNguyenLieu' => $maNguyenLieu,
                    'SoLuongHeThong' => $soLuongHeThong,
                    'SoLuongThucTe' => $hoanKho,
                    'ChenhLech' => $chenhLech,
                    'TinhTrang' => $tinhTrang,
                ]);

                if ($hangHuy > 0) {
                    $lyDoHuy = trim((string) ($data['ly_do_huy'] ?? ''));
                    $wasteItems[] = [
                        'MaNguyenLieu' => $maNguyenLieu,
                        'SoLuongHuy' => $hangHuy,
                    ];
                    $wasteReasonLines[] = $maNguyenLieu . ': ' . $lyDoHuy;
                }
            }

            if ($wasteItems !== []) {
                $maPhieuHuy = $this->generateNextCode($wasteHeaderTable, 'MaPhieuHuy', 'PH');

                DB::table($wasteHeaderTable)->insert([
                    'MaPhieuHuy' => $maPhieuHuy,
                    'NgayTao' => now()->toDateString(),
                    'LyDoHuy' => implode(' | ', $wasteReasonLines),
                    'TrangThai' => $this->statusPending(),
                    'MaTaiKhoan' => Auth::id(),
                    'MaPhieuKiemKe' => $maPhieuKiemKe,
                ]);

                foreach ($wasteItems as $item) {
                    DB::table($wasteDetailTable)->insert([
                        'MaPhieuHuy' => $maPhieuHuy,
                        'MaNguyenLieu' => $item['MaNguyenLieu'],
                        'SoLuongHuy' => $item['SoLuongHuy'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('kiemke.bep')
            ->with('success', 'Báo cáo kiểm kê cuối ngày đã được tạo thành công với trạng thái Chờ duyệt.');
    }

    public function danhSachBaoCao()
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'nguyenlieu']);
        $wasteHeaderTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);
        $wasteDetailTable = $this->resolveExistingTable(['ChiTietPhieuHuy', 'chitietphieuhuy']);
        $accountTable = $this->resolveExistingTable(['TaiKhoan', 'taikhoan']);

        $danhSachPhiu = [];
        if ($reportTable === null || $detailTable === null || $ingredientTable === null) {
            return view('kiemke.danh_sach_bep', compact('danhSachPhiu'));
        }

        $phieuQuery = DB::table($reportTable . ' as pkk')
            ->where('pkk.LoaiKiemKe', $this->reportTypeEndOfDay())
            ->select('pkk.*');

        if ($accountTable !== null) {
            $phieuQuery->leftJoin($accountTable . ' as tk', 'tk.MaTaiKhoan', '=', 'pkk.MaTaiKhoan')
                ->addSelect('tk.HoTen as NhanVienLap');
        }

        $phieus = $phieuQuery
            ->orderByRaw("CASE pkk.TrangThai WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 ELSE 3 END", [
                $this->statusPending(),
                $this->statusRejected(),
                $this->statusApproved(),
            ])
            ->orderByDesc('pkk.NgayKiemKe')
            ->orderByDesc('pkk.MaPhieuKiemKe')
            ->get();

        foreach ($phieus as $phieu) {
            $inventorySnapshot = $this->buildKitchenStockSnapshot($phieu->NgayKiemKe, $reportTable, $detailTable);
            $detailsRaw = DB::table($detailTable . ' as ct')
                ->join($ingredientTable . ' as nl', 'ct.MaNguyenLieu', '=', 'nl.MaNguyenLieu')
                ->where('ct.MaPhieuKiemKe', $phieu->MaPhieuKiemKe)
                ->select(
                    'ct.MaNguyenLieu',
                    'ct.SoLuongThucTe',
                    'nl.TenNguyenLieu',
                    'nl.DonViTinh'
                )
                ->get();

            $phieuHuy = $wasteHeaderTable !== null
                ? DB::table($wasteHeaderTable)->where('MaPhieuKiemKe', $phieu->MaPhieuKiemKe)->first()
                : null;

            $qtyHuyMap = [];
            $phieuHuyDetails = [];
            $lyDoHuyMap = $phieuHuy ? $this->parseWasteReasons($phieuHuy->LyDoHuy) : [];

            if ($phieuHuy && $wasteDetailTable !== null) {
                $chiTietHuyRaw = DB::table($wasteDetailTable . ' as cth')
                    ->join($ingredientTable . ' as nl', 'cth.MaNguyenLieu', '=', 'nl.MaNguyenLieu')
                    ->where('cth.MaPhieuHuy', $phieuHuy->MaPhieuHuy)
                    ->select('cth.MaNguyenLieu', 'cth.SoLuongHuy', 'nl.TenNguyenLieu')
                    ->get();

                foreach ($chiTietHuyRaw as $item) {
                    $qtyHuyMap[$item->MaNguyenLieu] = (int) $item->SoLuongHuy;
                    $phieuHuyDetails[] = [
                        'MaNguyenLieu' => $item->MaNguyenLieu,
                        'TenNguyenLieu' => $item->TenNguyenLieu,
                        'SoLuongHuy' => $item->SoLuongHuy,
                        'LyDo' => $lyDoHuyMap[$item->MaNguyenLieu] ?? '-',
                    ];
                }
            }

            $details = [];
            $isFullyMatched = true;

            foreach ($detailsRaw as $detail) {
                $tonDau = (int) ($inventorySnapshot['opening'][$detail->MaNguyenLieu] ?? 0);
                $xuatTrongNgay = (int) ($inventorySnapshot['issued'][$detail->MaNguyenLieu] ?? 0);
                $soSachHeThong = (int) ($inventorySnapshot['system'][$detail->MaNguyenLieu] ?? 0);
                $soLuongHuyTrongCa = (int) ($qtyHuyMap[$detail->MaNguyenLieu] ?? 0);
                $chenhLech = ($detail->SoLuongThucTe + $soLuongHuyTrongCa) - $soSachHeThong;
                $tinhTrang = $chenhLech === 0 ? $this->matchExact() : ($chenhLech > 0 ? $this->matchOver() : $this->matchUnder());

                if ($chenhLech !== 0) {
                    $isFullyMatched = false;
                }

                $details[] = [
                    'MaNguyenLieu' => $detail->MaNguyenLieu,
                    'TenNguyenLieu' => $detail->TenNguyenLieu,
                    'DonViTinh' => $detail->DonViTinh ?? '',
                    'TonDau' => $tonDau,
                    'NhapKho' => 0,
                    'XuatTrongNgay' => $xuatTrongNgay,
                    'SoLuongHeThong' => $soSachHeThong,
                    'ThucTeDem' => (int) $detail->SoLuongThucTe,
                    'HangHuy' => $soLuongHuyTrongCa,
                    'ChenhLech' => $chenhLech,
                    'TinhTrang' => $tinhTrang,
                    'KetLuan' => $this->varianceLabel($chenhLech),
                ];
            }

            $danhSachPhiu[] = [
                'MaPhieuKiemKe' => $phieu->MaPhieuKiemKe,
                'NgayKiemKe' => $phieu->NgayKiemKe,
                'TrangThai' => $phieu->TrangThai,
                'GhiChu' => $phieu->GhiChu,
                'NhanVienLap' => $phieu->NhanVienLap ?? null,
                'Details' => $details,
                'PhieuHuy' => $phieuHuy,
                'PhieuHuyDetails' => $phieuHuyDetails,
                'isFullyMatched' => $isFullyMatched,
            ];
        }

        return view('kiemke.danh_sach_bep', compact('danhSachPhiu'));
    }

    public function tuChoiBaoCao(Request $request, $maPhieu)
    {
        $request->validate([
            'ghi_chu_tu_choi' => ['required', 'string', 'max:255'],
        ]);

        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $wasteHeaderTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);

        if ($reportTable === null) {
            return back()->with('error', 'Không tìm thấy dữ liệu báo cáo kiểm kê để xử lý.');
        }

        DB::table($reportTable)
            ->where('MaPhieuKiemKe', $maPhieu)
            ->update([
                'TrangThai' => $this->statusRejected(),
                'GhiChu' => $request->input('ghi_chu_tu_choi'),
            ]);

        if ($wasteHeaderTable !== null) {
            DB::table($wasteHeaderTable)
                ->where('MaPhieuKiemKe', $maPhieu)
                ->update(['TrangThai' => $this->statusRejected()]);
        }

        return back()->with('success', 'Đã từ chối báo cáo kiểm kê cuối ngày và yêu cầu nhân viên hiệu chỉnh lại số liệu.');
    }

    public function chotCaBaoCao($maPhieu)
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'nguyenlieu']);
        $wasteHeaderTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);
        $wasteDetailTable = $this->resolveExistingTable(['ChiTietPhieuHuy', 'chitietphieuhuy']);

        if ($reportTable === null || $detailTable === null || $ingredientTable === null) {
            return back()->with('error', 'Không tìm thấy đủ dữ liệu để chốt ca.');
        }

        $phieuHuy = $wasteHeaderTable !== null
            ? DB::table($wasteHeaderTable)->where('MaPhieuKiemKe', $maPhieu)->first()
            : null;

        $qtyHuyMap = [];
        if ($phieuHuy && $wasteDetailTable !== null) {
            $qtyHuyMap = DB::table($wasteDetailTable)
                ->where('MaPhieuHuy', $phieuHuy->MaPhieuHuy)
                ->pluck('SoLuongHuy', 'MaNguyenLieu')
                ->map(fn ($value) => (int) $value)
                ->toArray();
        }

        $details = DB::table($detailTable)->where('MaPhieuKiemKe', $maPhieu)->get();
        $report = DB::table($reportTable)->where('MaPhieuKiemKe', $maPhieu)->first();
        $inventorySnapshot = $this->buildKitchenStockSnapshot($report->NgayKiemKe ?? now()->toDateString(), $reportTable, $detailTable);
        foreach ($details as $detail) {
            $soLuongHuyTrongCa = (int) ($qtyHuyMap[$detail->MaNguyenLieu] ?? 0);
            $soSachHeThong = (int) ($inventorySnapshot['system'][$detail->MaNguyenLieu] ?? 0);
            if (($detail->SoLuongThucTe + $soLuongHuyTrongCa) !== $soSachHeThong) {
                return back()->with('warning', 'Không thể chốt ca vì số liệu hoàn kho và hàng hủy vẫn chưa khớp với số liệu hệ thống.');
            }
        }

        DB::transaction(function () use ($maPhieu, $details, $reportTable, $ingredientTable, $wasteHeaderTable, $phieuHuy) {
            DB::table($reportTable)
                ->where('MaPhieuKiemKe', $maPhieu)
                ->update(['TrangThai' => $this->statusApproved()]);

            if ($wasteHeaderTable !== null && $phieuHuy) {
                DB::table($wasteHeaderTable)
                    ->where('MaPhieuHuy', $phieuHuy->MaPhieuHuy)
                    ->update(['TrangThai' => $this->statusApproved()]);
            }

            foreach ($details as $detail) {
                DB::table($ingredientTable)
                    ->where('MaNguyenLieu', $detail->MaNguyenLieu)
                    ->update([
                        'SoLuongTonKho' => (int) $detail->SoLuongThucTe,
                    ]);
            }
        });

        return back()->with('success', 'Đã xác nhận và chốt ca thành công. Số lượng hoàn kho đã được cập nhật thành tồn đầu ngày cho chu kỳ tiếp theo.');
    }

    private function parseWasteReasons(?string $combinedReasons): array
    {
        if (! $combinedReasons) {
            return [];
        }

        $result = [];
        foreach (explode('|', $combinedReasons) as $segment) {
            $segment = trim($segment);
            if ($segment === '' || ! str_contains($segment, ':')) {
                continue;
            }

            [$maNguyenLieu, $lyDo] = array_map('trim', explode(':', $segment, 2));
            if ($maNguyenLieu !== '') {
                $result[$maNguyenLieu] = $lyDo;
            }
        }

        return $result;
    }

    private function buildKitchenStockSnapshot(string $reportDate, ?string $reportTable = null, ?string $detailTable = null): array
    {
        $reportTable ??= $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable ??= $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $exportHeaderTable = $this->resolveExistingTable(['PhieuXuatKho', 'phieuxuatkho']);
        $exportDetailTable = $this->resolveExistingTable(['ChiTietPhieuXuat', 'chitietphieuxuat']);
        $batchTable = $this->resolveExistingTable(['LoHang', 'lohang']);

        $opening = [];
        if ($reportTable !== null && $detailTable !== null) {
            $previousApprovedReport = DB::table($reportTable)
                ->where('LoaiKiemKe', $this->reportTypeEndOfDay())
                ->where('TrangThai', $this->statusApproved())
                ->whereDate('NgayKiemKe', '<', $reportDate)
                ->orderByDesc('NgayKiemKe')
                ->orderByDesc('MaPhieuKiemKe')
                ->first();

            if ($previousApprovedReport) {
                $opening = DB::table($detailTable)
                    ->where('MaPhieuKiemKe', $previousApprovedReport->MaPhieuKiemKe)
                    ->pluck('SoLuongThucTe', 'MaNguyenLieu')
                    ->map(fn ($value) => (int) $value)
                    ->toArray();
            }
        }

        $issued = [];
        if ($exportHeaderTable !== null && $exportDetailTable !== null && $batchTable !== null) {
            $issued = DB::table($exportHeaderTable . ' as px')
                ->join($exportDetailTable . ' as ct', 'ct.MaPhieuXuat', '=', 'px.MaPhieuXuat')
                ->join($batchTable . ' as lh', 'lh.MaLoHang', '=', 'ct.MaLoHang')
                ->whereDate('px.NgayXuat', $reportDate)
                ->whereIn('px.TrangThai', $this->completedExportStatuses())
                ->groupBy('lh.MaNguyenLieu')
                ->select('lh.MaNguyenLieu as MaNguyenLieu', DB::raw('SUM(ct.SoLuongXuat) as TongSoLuongXuat'))
                ->pluck('TongSoLuongXuat', 'MaNguyenLieu')
                ->map(fn ($value) => (int) $value)
                ->toArray();
        }

        $system = [];
        foreach (array_unique(array_merge(array_keys($opening), array_keys($issued))) as $maNguyenLieu) {
            $system[$maNguyenLieu] = (int) ($opening[$maNguyenLieu] ?? 0) + (int) ($issued[$maNguyenLieu] ?? 0);
        }

        return [
            'opening' => $opening,
            'issued' => $issued,
            'system' => $system,
        ];
    }

    private function generateNextCode(string $table, string $column, string $prefix): string
    {
        $lastCode = DB::table($table)
            ->where($column, 'like', $prefix . '%')
            ->orderByDesc($column)
            ->value($column);

        $nextNumber = 1;
        if ($lastCode && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function resolveExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function reportTypeEndOfDay(): string
    {
        return "Cu\u{1ED1}i ng\u{00E0}y";
    }

    private function statusPending(): string
    {
        return "Ch\u{1EDD} duy\u{1EC7}t";
    }

    private function statusRejected(): string
    {
        return "T\u{1EEB} ch\u{1ED1}i";
    }

    private function statusApproved(): string
    {
        return "\u{0110}\u{00E3} duy\u{1EC7}t";
    }

    private function matchExact(): string
    {
        return "Kh\u{1EDB}p";
    }

    private function matchOver(): string
    {
        return "Th\u{1EEB}a";
    }

    private function matchUnder(): string
    {
        return "Thi\u{1EBF}u";
    }

    private function varianceLabel(int $chenhLech): string
    {
        if ($chenhLech === 0) {
            return $this->matchExact();
        }

        return $chenhLech > 0 ? 'Thừa hàng' : 'Thiếu hàng';
    }

    private function completedExportStatuses(): array
    {
        return ['Hoàn tất', 'Đã xuất', 'Hoàn thành'];
    }
}
