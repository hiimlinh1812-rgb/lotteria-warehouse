<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    private const TRACE_TABLE = 'TruyVetDonDatHang';
    private const STATUS_PENDING = 'Cho phe duyet';
    private const STATUS_PROCESSING = 'Dang xu ly';
    private const STATUS_APPROVED = 'Da duyet';
    private const STATUS_REJECTED = 'Tu choi';
    private const STATUS_CANCELLED = 'Da huy';
    private const STATUS_RECEIVED = 'Da nhan hang';
    private const STATUS_STOCKED = 'Da nhap kho';
    private const EDITABLE_STATUSES = [self::STATUS_PENDING, self::STATUS_PROCESSING];
    private const CANCELLABLE_STATUSES = [self::STATUS_PENDING, self::STATUS_PROCESSING];
    private const SUMMARY_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];
    private const FILTER_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_RECEIVED,
        self::STATUS_STOCKED,
    ];
    private const SORT_FIELDS = [
        'code' => 'd.MaDonDatHang',
        'date' => 'd.NgayDat',
        'status' => 'd.TrangThai',
        'items' => 'SoMatHang',
        'quantity' => 'TongSoLuong',
    ];

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'date');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! array_key_exists($sort, self::SORT_FIELDS)) {
            $sort = 'date';
            $direction = 'desc';
        }

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
            ->when($status, fn ($query) => $query->where('d.TrangThai', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('d.MaDonDatHang', 'like', "%{$search}%")
                        ->orWhere('t.HoTen', 'like', "%{$search}%")
                        ->orWhere('d.GhiChu', 'like', "%{$search}%");
                });
            })
            ->groupBy('d.MaDonDatHang', 'd.NgayDat', 'd.TrangThai', 'd.GhiChu', 't.HoTen')
            ->orderBy(self::SORT_FIELDS[$sort], $direction)
            ->when($sort !== 'code', fn ($query) => $query->orderByDesc('d.MaDonDatHang'))
            ->when($sort !== 'date', fn ($query) => $query->orderByDesc('d.NgayDat'))
            ->orderByDesc('d.MaDonDatHang')
            ->paginate(10)
            ->withQueryString();

        $summary = DB::table('DonDatHang')
            ->select('TrangThai', DB::raw('COUNT(*) as SoLuong'))
            ->groupBy('TrangThai')
            ->pluck('SoLuong', 'TrangThai');

        $summaryCards = collect(self::SUMMARY_STATUSES)
            ->mapWithKeys(fn (string $orderStatus) => [$orderStatus => (int) ($summary[$orderStatus] ?? 0)]);

        return view('purchase-orders.index', [
            'orders' => $orders,
            'summaryCards' => $summaryCards,
            'status' => $status,
            'statusOptions' => self::FILTER_STATUSES,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('purchase-orders.create', [
            'accounts' => $this->accounts(),
            'ingredients' => $this->ingredients(),
            'nextCode' => $this->nextOrderCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$validated, $items] = $this->validatedOrderPayload($request);

        if ($items->isEmpty()) {
            return back()
                ->withErrors(['items' => 'Vui lòng chọn ít nhất một nguyên liệu.'])
                ->withInput();
        }

        $orderCode = DB::transaction(function () use ($validated, $items) {
            $orderCode = $this->nextOrderCode(true);

            DB::table('DonDatHang')->insert([
                'MaDonDatHang' => $orderCode,
                'NgayDat' => $validated['NgayDat'],
                'TrangThai' => self::STATUS_PENDING,
                'GhiChu' => $validated['GhiChu'] ?? null,
                'MaTaiKhoan' => $validated['MaTaiKhoan'],
            ]);

            DB::table('ChiTietDonDatHang')->insert(
                $items->map(fn ($item) => [
                    'MaDonDatHang' => $orderCode,
                    'MaNguyenLieu' => $item['MaNguyenLieu'],
                    'SoLuongDat' => $item['SoLuongDat'],
                ])->all()
            );

            $this->recordAudit(
                $orderCode,
                'Tạo đơn',
                null,
                self::STATUS_PENDING,
                $validated['MaTaiKhoan'],
                $validated['GhiChu'] ?? 'Khởi tạo đơn mua'
            );

            return $orderCode;
        });

        return redirect()
            ->route('purchase-orders.show', $orderCode)
            ->with('success', "Đã tạo đơn mua {$orderCode} và chuyển sang trạng thái Chờ phê duyệt.");
    }

    public function edit(string $order): View
    {
        $orderData = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->first();

        abort_if(! $orderData, 404);

        if (! in_array($orderData->TrangThai, self::EDITABLE_STATUSES, true)) {
            abort(403, 'Chỉ được sửa đơn mua đang chờ phê duyệt hoặc đang xử lý.');
        }

        $items = DB::table('ChiTietDonDatHang')
            ->select('MaNguyenLieu', 'SoLuongDat')
            ->where('MaDonDatHang', $order)
            ->orderBy('MaNguyenLieu')
            ->get()
            ->map(fn ($item) => [
                'MaNguyenLieu' => $item->MaNguyenLieu,
                'SoLuongDat' => $item->SoLuongDat,
            ])
            ->all();

        return view('purchase-orders.edit', [
            'order' => $orderData,
            'items' => $items,
            'accounts' => $this->accounts(),
            'ingredients' => $this->ingredients(),
        ]);
    }

    public function update(Request $request, string $order): RedirectResponse
    {
        [$validated, $items] = $this->validatedOrderPayload($request);

        if ($items->isEmpty()) {
            return back()
                ->withErrors(['items' => 'Vui lòng chọn ít nhất một nguyên liệu.'])
                ->withInput();
        }

        $updated = DB::transaction(function () use ($order, $validated, $items) {
            $currentStatus = DB::table('DonDatHang')
                ->where('MaDonDatHang', $order)
                ->value('TrangThai');

            $affected = DB::table('DonDatHang')
                ->where('MaDonDatHang', $order)
                ->whereIn('TrangThai', self::EDITABLE_STATUSES)
                ->update([
                    'NgayDat' => $validated['NgayDat'],
                    'GhiChu' => $validated['GhiChu'] ?? null,
                    'MaTaiKhoan' => $validated['MaTaiKhoan'],
                ]);

            if (! $affected) {
                return false;
            }

            DB::table('ChiTietDonDatHang')
                ->where('MaDonDatHang', $order)
                ->delete();

            DB::table('ChiTietDonDatHang')->insert(
                $items->map(fn ($item) => [
                    'MaDonDatHang' => $order,
                    'MaNguyenLieu' => $item['MaNguyenLieu'],
                    'SoLuongDat' => $item['SoLuongDat'],
                ])->all()
            );

            $this->recordAudit(
                $order,
                'Cập nhật đơn',
                $currentStatus,
                $currentStatus,
                $validated['MaTaiKhoan'],
                $validated['GhiChu'] ?? 'Cập nhật thông tin đơn mua'
            );

            return true;
        });

        return redirect()
            ->route('purchase-orders.show', $order)
            ->with(
                $updated ? 'success' : 'warning',
                $updated ? 'Đã cập nhật đơn mua.' : 'Chỉ được sửa đơn đang chờ phê duyệt hoặc đang xử lý.'
            );
    }

    public function show(string $order): View
    {
        $orderData = DB::table('DonDatHang as d')
            ->join('TaiKhoan as t', 't.MaTaiKhoan', '=', 'd.MaTaiKhoan')
            ->select('d.*', 't.HoTen', 't.VaiTro')
            ->where('d.MaDonDatHang', $order)
            ->first();

        abort_if(! $orderData, 404);

        $items = DB::table('ChiTietDonDatHang as c')
            ->join('NguyenLieu as n', 'n.MaNguyenLieu', '=', 'c.MaNguyenLieu')
            ->select('c.*', 'n.TenNguyenLieu', 'n.DonViTinh', 'n.NhomHang', 'n.SoLuongTonKho')
            ->where('c.MaDonDatHang', $order)
            ->orderBy('c.MaNguyenLieu')
            ->get();

        return view('purchase-orders.show', [
            'order' => $orderData,
            'items' => $items,
            'accounts' => $this->accounts(),
            'approvalAccounts' => $this->approvalAccounts(),
            'auditTrail' => $this->auditTrail($order),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function process(Request $request, string $order): RedirectResponse
    {
        $request->validate([
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
            'GhiChuXuLy' => ['nullable', 'string', 'max:180'],
        ]);

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->where('TrangThai', self::STATUS_PENDING)
            ->update([
                'TrangThai' => self::STATUS_PROCESSING,
                'GhiChu' => $this->appendApprovalNote($order, 'Xử lý', $request->MaTaiKhoan, $request->GhiChuXuLy),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Chuyển xử lý',
                $previousStatus,
                self::STATUS_PROCESSING,
                $request->MaTaiKhoan,
                $request->GhiChuXuLy
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã chuyển sang trạng thái Đang xử lý.' : 'Chỉ chuyển xử lý được đơn đang chờ phê duyệt.'
        );
    }

    public function approve(Request $request, string $order): RedirectResponse
    {
        $request->validate([
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
            'GhiChuDuyet' => ['nullable', 'string', 'max:180'],
        ]);

        if (! $this->isApprovalAccount($request->MaTaiKhoan)) {
            return back()->with('warning', 'Chỉ tài khoản Quản lý mới được phê duyệt đơn mua.');
        }

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', self::EDITABLE_STATUSES)
            ->update([
                'TrangThai' => self::STATUS_APPROVED,
                'GhiChu' => $this->appendApprovalNote($order, 'Phê duyệt', $request->MaTaiKhoan, $request->GhiChuDuyet),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Phê duyệt đơn',
                $previousStatus,
                self::STATUS_APPROVED,
                $request->MaTaiKhoan,
                $request->GhiChuDuyet
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã được phê duyệt.' : 'Chỉ phê duyệt được đơn đang chờ phê duyệt hoặc đang xử lý.'
        );
    }

    public function reject(Request $request, string $order): RedirectResponse
    {
        $request->validate([
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
            'LyDoTuChoi' => ['required', 'string', 'max:180'],
        ]);

        if (! $this->isApprovalAccount($request->MaTaiKhoan)) {
            return back()->with('warning', 'Chỉ tài khoản Quản lý mới được từ chối đơn mua.');
        }

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', self::EDITABLE_STATUSES)
            ->update([
                'TrangThai' => self::STATUS_REJECTED,
                'GhiChu' => $this->appendApprovalNote($order, 'Từ chối', $request->MaTaiKhoan, $request->LyDoTuChoi),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Từ chối đơn',
                $previousStatus,
                self::STATUS_REJECTED,
                $request->MaTaiKhoan,
                $request->LyDoTuChoi
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã bị từ chối.' : 'Chỉ từ chối được đơn đang chờ phê duyệt hoặc đang xử lý.'
        );
    }

    public function cancel(string $order): RedirectResponse
    {
        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', self::CANCELLABLE_STATUSES)
            ->update([
                'TrangThai' => self::STATUS_CANCELLED,
                'GhiChu' => $this->appendApprovalNote($order, 'Hủy đơn', 'Hệ thống', null),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Hủy đơn',
                $previousStatus,
                self::STATUS_CANCELLED,
                null,
                'Hủy khi đơn chưa hoàn tất'
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã được hủy.' : 'Chỉ hủy được đơn đang chờ phê duyệt hoặc đang xử lý.'
        );
    }

    public function receive(string $order): RedirectResponse
    {
        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->where('TrangThai', self::STATUS_APPROVED)
            ->update([
                'TrangThai' => self::STATUS_RECEIVED,
                'GhiChu' => $this->appendApprovalNote($order, 'Nhận hàng', 'Hệ thống', null),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Nhận hàng',
                $previousStatus,
                self::STATUS_RECEIVED,
                null,
                'Xác nhận đã nhận đủ hàng'
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã chuyển sang trạng thái Đã nhận hàng.' : 'Chỉ nhận hàng được đơn đã duyệt.'
        );
    }

    public function stock(string $order): RedirectResponse
    {
        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->where('TrangThai', self::STATUS_RECEIVED)
            ->update([
                'TrangThai' => self::STATUS_STOCKED,
                'GhiChu' => $this->appendApprovalNote($order, 'Nhập kho', 'Hệ thống', null),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Nhập kho',
                $previousStatus,
                self::STATUS_STOCKED,
                null,
                'Hoàn tất nhập kho'
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã chuyển sang trạng thái Đã nhập kho.' : 'Chỉ nhập kho được đơn đã nhận hàng.'
        );
    }

    private function accounts()
    {
        return DB::table('TaiKhoan')
            ->select('MaTaiKhoan', 'HoTen', 'VaiTro')
            ->orderBy('MaTaiKhoan')
            ->get();
    }

    private function approvalAccounts()
    {
        $accounts = DB::table('TaiKhoan')
            ->select('MaTaiKhoan', 'HoTen', 'VaiTro')
            ->where('VaiTro', 'Quan ly')
            ->orderBy('MaTaiKhoan')
            ->get();

        return $accounts->isNotEmpty() ? $accounts : $this->accounts();
    }

    private function isApprovalAccount(string $accountCode): bool
    {
        return DB::table('TaiKhoan')
            ->where('MaTaiKhoan', $accountCode)
            ->where('VaiTro', 'Quan ly')
            ->exists();
    }

    private function ingredients()
    {
        return DB::table('NguyenLieu')
            ->select('MaNguyenLieu', 'TenNguyenLieu', 'DonViTinh', 'NhomHang', 'SoLuongTonKho')
            ->orderBy('MaNguyenLieu')
            ->get();
    }

    private function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ phê duyệt',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RECEIVED => 'Đã nhận hàng',
            self::STATUS_STOCKED => 'Đã nhập kho',
        ];
    }

    private function auditTrail(string $order)
    {
        if (! Schema::hasTable(self::TRACE_TABLE)) {
            return collect();
        }

        return DB::table(self::TRACE_TABLE)
            ->where('MaDonDatHang', $order)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function currentStatus(string $order): ?string
    {
        return DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->value('TrangThai');
    }

    private function nextOrderCode(bool $lock = false): string
    {
        $query = DB::table('DonDatHang');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastCode = $query
            ->where('MaDonDatHang', 'like', 'DDH%')
            ->orderByDesc('MaDonDatHang')
            ->value('MaDonDatHang');

        $number = $lastCode ? ((int) substr($lastCode, 3)) + 1 : 1;

        return 'DDH' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function validatedOrderPayload(Request $request): array
    {
        $validated = $request->validate([
            'NgayDat' => ['required', 'date'],
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
            'GhiChu' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.MaNguyenLieu' => ['required', 'exists:NguyenLieu,MaNguyenLieu'],
            'items.*.SoLuongDat' => ['required', 'integer', 'min:1', 'max:999999'],
        ]);

        $items = collect($validated['items'])
            ->filter(fn ($item) => ! empty($item['MaNguyenLieu']) && (int) $item['SoLuongDat'] > 0)
            ->groupBy('MaNguyenLieu')
            ->map(fn ($rows, $code) => [
                'MaNguyenLieu' => $code,
                'SoLuongDat' => $rows->sum(fn ($row) => (int) $row['SoLuongDat']),
            ])
            ->values();

        return [$validated, $items];
    }

    private function appendApprovalNote(string $order, string $action, string $accountCode, ?string $note): string
    {
        $currentNote = (string) DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->value('GhiChu');

        $line = trim($action . ' bởi ' . $accountCode . ($note ? ': ' . $note : ''));
        $combined = trim($currentNote . ($currentNote ? ' | ' : '') . $line);

        return mb_substr($combined, 0, 255);
    }

    private function recordAudit(
        string $order,
        string $action,
        ?string $statusFrom,
        ?string $statusTo,
        ?string $accountCode,
        ?string $note
    ): void {
        if (! Schema::hasTable(self::TRACE_TABLE)) {
            return;
        }

        DB::table(self::TRACE_TABLE)->insert([
            'MaDonDatHang' => $order,
            'HanhDong' => $action,
            'TrangThaiTruoc' => $statusFrom,
            'TrangThaiSau' => $statusTo,
            'MaTaiKhoan' => $accountCode,
            'NoiDung' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
