<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class KiemKeBepController extends Controller
{
    public function index(Request $request)
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'NguyenLieu']);
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
                    ->map(fn($value) => (int) $value)
                    ->toArray();

                $wasteReasonMap = $this->parseWasteReasons($wasteReport->LyDoHuy);
            }
        }

        // Lấy danh sách nguyên liệu đã xuất cho bếp hôm nay
        $inventorySnapshot = $this->buildKitchenStockSnapshot(now()->toDateString(), $reportTable, $detailTable);
        $maNguyenLieuTrongNgay = array_keys($inventorySnapshot['issued']);

        // Gộp thêm nguyên liệu của phiếu bị từ chối (để hiển thị lại cho nhân viên sửa)
        if ($detailMap->isNotEmpty()) {
            $maNguyenLieuTrongNgay = array_unique(array_merge($maNguyenLieuTrongNgay, $detailMap->keys()->toArray()));
        }

        $nguyenLieusDb = $ingredientTable !== null && !empty($maNguyenLieuTrongNgay)
            ? DB::table($ingredientTable)->whereIn('MaNguyenLieu', $maNguyenLieuTrongNgay)->orderBy('TenNguyenLieu')->get()
            : collect();

        $nguyenLieuForm = [];
        foreach ($nguyenLieusDb as $nguyenLieu) {
            $chiTiet = $detailMap->get($nguyenLieu->MaNguyenLieu);

            $nguyenLieuForm[] = [
                'ma_nl' => $nguyenLieu->MaNguyenLieu,
                'ten_nl' => $nguyenLieu->TenNguyenLieu,
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
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'NguyenLieu']);
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

        DB::transaction(function () use ($request, $requestKiemKe, $reportTable, $detailTable, $wasteHeaderTable, $wasteDetailTable) {
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
                $hoanKho = (int) ($data['hoan_kho'] ?? 0);
                $hangHuy = (int) ($data['hang_huy'] ?? 0);

                // Lưu dữ liệu bếp cung cấp, bỏ qua Tồn hệ thống và Chênh lệch
                DB::table($detailTable)->insert([
                    'MaPhieuKiemKe' => $maPhieuKiemKe,
                    'MaNguyenLieu' => $maNguyenLieu,
                    'SoLuongHeThong' => 0,
                    'SoLuongThucTe' => $hoanKho,
                    'ChenhLech' => 0,
                    'TinhTrang' => 'Ghi nhận',
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

            // Xử lý tạo phiếu xuất hủy đính kèm nếu có
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
            ->with('success', 'Báo cáo kiểm kê cuối ngày đã được gửi cho Quản lý. Vui lòng chờ duyệt.');
    }

    public function danhSachBaoCao()
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'NguyenLieu']);
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
            // Chỉ lấy để biết số lượng xuất trong ngày
            $inventorySnapshot = $this->buildKitchenStockSnapshot($phieu->NgayKiemKe, $reportTable, $detailTable);

            $detailsRaw = DB::table($detailTable . ' as ct')
                ->join($ingredientTable . ' as nl', 'ct.MaNguyenLieu', '=', 'nl.MaNguyenLieu')
                ->where('ct.MaPhieuKiemKe', $phieu->MaPhieuKiemKe)
                ->select(
                    'ct.MaNguyenLieu',
                    'ct.SoLuongThucTe',
                    'nl.TenNguyenLieu'
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
            foreach ($detailsRaw as $detail) {
                $xuatTrongNgay = (int) ($inventorySnapshot['issued'][$detail->MaNguyenLieu] ?? 0);
                $soLuongHuyTrongCa = (int) ($qtyHuyMap[$detail->MaNguyenLieu] ?? 0);

                $details[] = [
                    'MaNguyenLieu' => $detail->MaNguyenLieu,
                    'TenNguyenLieu' => $detail->TenNguyenLieu,
                    'XuatTrongNgay' => $xuatTrongNgay,
                    'ThucTeDem' => (int) $detail->SoLuongThucTe,
                    'HangHuy' => $soLuongHuyTrongCa,
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
            ];
        }

        return view('kiemke.danh_sach_bep', compact('danhSachPhiu'));
    }

    public function tuChoiBaoCao(Request $request, string $maPhieu)
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

    public function chotCaBaoCao(string $maPhieu)
    {
        $reportTable = $this->resolveExistingTable(['PhieuKiemKe', 'phieukiemke']);
        $detailTable = $this->resolveExistingTable(['ChiTietPhieuKiemKeCuoiNgay', 'chitietphieukiemkecuoingay']);
        $ingredientTable = $this->resolveExistingTable(['NguyenLieu', 'NguyenLieu']);
        $wasteHeaderTable = $this->resolveExistingTable(['PhieuXuatHuy', 'phieuxuathuy']);

        if ($reportTable === null || $detailTable === null || $ingredientTable === null) {
            return back()->with('error', 'Không tìm thấy đủ dữ liệu để chốt ca.');
        }

        $phieuHuy = $wasteHeaderTable !== null
            ? DB::table($wasteHeaderTable)->where('MaPhieuKiemKe', $maPhieu)->first()
            : null;

        $details = DB::table($detailTable)->where('MaPhieuKiemKe', $maPhieu)->get();

        DB::transaction(function () use ($maPhieu, $details, $reportTable, $ingredientTable, $wasteHeaderTable, $phieuHuy) {
            // Cập nhật phiếu sang Đã duyệt
            DB::table($reportTable)
                ->where('MaPhieuKiemKe', $maPhieu)
                ->update(['TrangThai' => $this->statusApproved()]);

            if ($wasteHeaderTable !== null && $phieuHuy) {
                DB::table($wasteHeaderTable)
                    ->where('MaPhieuHuy', $phieuHuy->MaPhieuHuy)
                    ->update(['TrangThai' => $this->statusApproved()]);
            }

            // Cộng lại lượng Bếp hoàn kho vào Tồn tổng
            foreach ($details as $detail) {
                if ($detail->SoLuongThucTe > 0) {
                    DB::table($ingredientTable)
                        ->where('MaNguyenLieu', $detail->MaNguyenLieu)
                        ->increment('SoLuongTonKho', (int) $detail->SoLuongThucTe);
                }
            }
        });

        return back()->with('success', 'Đã xác nhận khớp và chốt ca thành công. Số lượng Bếp hoàn kho đã được cộng trả lại vào Tồn Kho Tổng.');
    }

    private function parseWasteReasons(?string $combinedReasons): array
    {
        if (! $combinedReasons) return [];
        $result = [];
        foreach (explode('|', $combinedReasons) as $segment) {
            $segment = trim($segment);
            if ($segment === '' || ! str_contains($segment, ':')) continue;
            [$maNguyenLieu, $lyDo] = array_map('trim', explode(':', $segment, 2));
            if ($maNguyenLieu !== '') {
                $result[$maNguyenLieu] = $lyDo;
            }
        }
        return $result;
    }

    private function buildKitchenStockSnapshot(string $reportDate, ?string $reportTable = null, ?string $detailTable = null): array
    {
        $exportHeaderTable = $this->resolveExistingTable(['PhieuXuatKho', 'phieuxuatkho']);
        $exportDetailTable = $this->resolveExistingTable(['ChiTietPhieuXuat', 'chitietphieuxuat']);
        $batchTable = $this->resolveExistingTable(['LoHang', 'LoHang']);

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
                ->map(fn($value) => (int) $value)
                ->toArray();
        }

        // Bỏ logic opening, system đi cho gọn nhẹ vì bếp không dùng nữa
        return [
            'issued' => $issued,
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
            if (Schema::hasTable($table)) return $table;
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
    private function completedExportStatuses(): array
    {
        return ['Hoàn tất', 'Đã xuất', 'Hoàn thành'];
    }
}
