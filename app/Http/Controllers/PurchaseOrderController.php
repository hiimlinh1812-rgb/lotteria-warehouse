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
    private const STATUS_PENDING = 'Chờ phê duyệt';
    private const STATUS_WAITING_RECEIVE = 'Chờ nhận hàng';
    private const STATUS_REJECTED = 'Từ chối';
    private const STATUS_CANCELLED = 'Đã hủy';
    private const STATUS_RECEIVED = 'Đã nhận hàng';
    private const STATUS_WAITING_PROCESS = 'Chờ xử lý';
    private const STATUS_RETURNING = 'Đang đổi trả';
    private const STATUS_STOCKED = 'Đã nhập kho';
    private const STATUS_ALIASES = [
        'Cho phe duyet' => self::STATUS_PENDING,
        'Chờ nhận hàng' => self::STATUS_WAITING_RECEIVE,
        'Tu choi' => self::STATUS_REJECTED,
        'Da huy' => self::STATUS_CANCELLED,
        'Da nhan hang' => self::STATUS_RECEIVED,
        'Cho xu ly' => self::STATUS_WAITING_PROCESS,
        'Dang doi tra' => self::STATUS_RETURNING,
        'Da nhap kho' => self::STATUS_STOCKED,
    ];
    private const EDITABLE_STATUSES = [self::STATUS_PENDING];
    private const CANCELLABLE_STATUSES = [self::STATUS_PENDING];
    private const SUMMARY_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_WAITING_RECEIVE,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_RECEIVED,
        self::STATUS_WAITING_PROCESS,
        self::STATUS_RETURNING,
        self::STATUS_STOCKED,
    ];
    private const FILTER_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_WAITING_RECEIVE,
        self::STATUS_WAITING_PROCESS,
        self::STATUS_RETURNING,
        self::STATUS_RECEIVED,
        self::STATUS_STOCKED,
        self::STATUS_REJECTED,
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
        $status = $this->normalizeStatus($request->query('status'));
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
            ->when($status, fn ($query) => $query->whereIn('d.TrangThai', $this->statusCandidates([$status])))
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

        $orders->getCollection()->transform(function ($order) {
            $order->TrangThai = $this->normalizeStatus($order->TrangThai);

            return $order;
        });

        $summary = [];

        foreach (
            DB::table('DonDatHang')
                ->select('TrangThai', DB::raw('COUNT(*) as SoLuong'))
                ->groupBy('TrangThai')
                ->get() as $row
        ) {
            $normalizedStatus = $this->normalizeStatus($row->TrangThai);

            if ($normalizedStatus === null) {
                continue;
            }

            $summary[$normalizedStatus] = ($summary[$normalizedStatus] ?? 0) + (int) $row->SoLuong;
        }

        $summaryCards = collect(self::SUMMARY_STATUSES)
            ->mapWithKeys(fn (string $orderStatus) => [$orderStatus => (int) ($summary[$orderStatus] ?? 0)]);

        $managerSummary = [
            self::STATUS_PENDING => (int) ($summary[self::STATUS_PENDING] ?? 0),
            self::STATUS_WAITING_RECEIVE => (int) ($summary[self::STATUS_WAITING_RECEIVE] ?? 0),
            self::STATUS_RECEIVED => (int) ($summary[self::STATUS_RECEIVED] ?? 0),
            self::STATUS_STOCKED => (int) ($summary[self::STATUS_STOCKED] ?? 0),
        ];

        return view('purchase-orders.index', [
            'orders' => $orders,
            'summaryCards' => $summaryCards,
            'managerSummary' => $managerSummary,
            'status' => $status,
            'statusOptions' => self::FILTER_STATUSES,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        $this->abortUnlessManager();

        return view('purchase-orders.create', [
            'accounts' => $this->accounts(),
            'ingredients' => $this->ingredients(),
            'nextCode' => $this->nextOrderCode(),
            'currentAccountCode' => auth()->user()->MaTaiKhoan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->abortUnlessManager();

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
                'NgayDat' => now()->toDateString(),
                'TrangThai' => self::STATUS_PENDING,
                'GhiChu' => $validated['GhiChu'] ?? null,
                'MaTaiKhoan' => auth()->user()->MaTaiKhoan,
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
                auth()->user()->MaTaiKhoan,
                $validated['GhiChu'] ?? 'Khởi tạo đơn mua'
            );

            return $orderCode;
        });

        if ($request->routeIs('don-hang.*')) {
            return redirect()
                ->route('don-hang.index')
                ->with('success', "Đã tạo đơn mua {$orderCode} và chuyển sang trạng thái Chờ phê duyệt.");
        }

        return redirect()
            ->route('purchase-orders.show', $orderCode)
            ->with('success', "Đã tạo đơn mua {$orderCode} và chuyển sang trạng thái Chờ phê duyệt.");
    }

    public function edit(string $order): View
    {
        $this->abortUnlessManager();

        $orderData = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->first();

        abort_if(! $orderData, 404);
        $orderData->TrangThai = $this->normalizeStatus($orderData->TrangThai);

        if (! in_array($orderData->TrangThai, self::EDITABLE_STATUSES, true)) {
            abort(403, 'Chỉ được sửa đơn mua đang chờ phê duyệt.');
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
        $this->abortUnlessManager();

        [$validated, $items] = $this->validatedOrderPayload($request);

        if ($items->isEmpty()) {
            return back()
                ->withErrors(['items' => 'Vui lòng chọn ít nhất một nguyên liệu.'])
                ->withInput();
        }

        $updated = DB::transaction(function () use ($order, $validated, $items) {
            $currentStatus = $this->currentStatus($order);
            $orderData = DB::table('DonDatHang')
                ->where('MaDonDatHang', $order)
                ->whereIn('TrangThai', $this->statusCandidates(self::EDITABLE_STATUSES))
                ->first();

            if (!$orderData) {
                return false;
            }

            // Get original items
            $originalItems = DB::table('ChiTietDonDatHang')
                ->where('MaDonDatHang', $order)
                ->select('MaNguyenLieu', 'SoLuongDat')
                ->get()
                ->map(fn ($item) => [
                    'MaNguyenLieu' => $item->MaNguyenLieu,
                    'SoLuongDat' => $item->SoLuongDat,
                ])
                ->keyBy('MaNguyenLieu');

            // Prepare new items as keyed collection
            $newItemsKeyed = $items->keyBy('MaNguyenLieu');

            // Check if anything changed
            $ghiChuChanged = ($orderData->GhiChu ?? null) !== ($validated['GhiChu'] ?? null);
            $itemsChanged = false;

            // Check if number of items changed
            if ($originalItems->count() !== $newItemsKeyed->count()) {
                $itemsChanged = true;
            } else {
                // Check each item
                foreach ($originalItems as $maNL => $originalItem) {
                    $newItem = $newItemsKeyed->get($maNL);
                    if (!$newItem || $newItem['SoLuongDat'] != $originalItem['SoLuongDat']) {
                        $itemsChanged = true;
                        break;
                    }
                }
            }

            if (!$ghiChuChanged && !$itemsChanged) {
                return 'no_changes';
            }

            // Update GhiChu
            if ($ghiChuChanged) {
                DB::table('DonDatHang')
                    ->where('MaDonDatHang', $order)
                    ->update([
                        'GhiChu' => $validated['GhiChu'] ?? null,
                    ]);
            }

            // Update items
            if ($itemsChanged) {
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
            }

            $this->recordAudit(
                $order,
                'Cập nhật đơn',
                $currentStatus,
                $currentStatus,
                $orderData->MaTaiKhoan,
                $validated['GhiChu'] ?? 'Cập nhật thông tin đơn mua'
            );

            return true;
        });

        $routePrefix = request()->routeIs('don-hang.*') ? 'don-hang' : 'purchase-orders';
        
        if ($updated === 'no_changes') {
            return redirect()
                ->route($routePrefix . '.index')
                ->with('warning', 'Bạn chưa thay đổi gì cả.');
        }

        return redirect()
            ->route($routePrefix . '.index')
            ->with(
                $updated ? 'success' : 'warning',
                $updated ? 'Đã cập nhật đơn mua thành công.' : 'Chỉ được sửa đơn đang chờ phê duyệt.'
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
        $orderData->TrangThai = $this->normalizeStatus($orderData->TrangThai);

        $items = DB::table('ChiTietDonDatHang as c')
            ->join('NguyenLieu as n', 'n.MaNguyenLieu', '=', 'c.MaNguyenLieu')
            ->select('c.*', 'n.TenNguyenLieu', 'n.DonViTinh', 'n.NhomHang', 'n.SoLuongTonKho')
            ->where('c.MaDonDatHang', $order)
            ->orderBy('c.MaNguyenLieu')
            ->get();

        $receipt = $this->latestReceipt($order);
        $reconciliationItems = $this->reconciliationItems($order, $receipt?->MaPhieuNhan);
        $reconciliationSummary = [
            'matched' => $reconciliationItems->where('KetQua', 'Khớp')->count(),
            'short' => $reconciliationItems->where('KetQua', 'Thiếu')->count(),
            'extra' => $reconciliationItems->where('KetQua', 'Dư')->count(),
        ];

        return view('purchase-orders.show', [
            'order' => $orderData,
            'items' => $items,
            'accounts' => $this->accounts(),
            'approvalAccounts' => $this->approvalAccounts(),
            'auditTrail' => $this->auditTrail($order),
            'statusLabels' => $this->statusLabels(),
            'receipt' => $receipt,
            'reconciliationItems' => $reconciliationItems,
            'reconciliationSummary' => $reconciliationSummary,
        ]);
    }

    public function returnForm(string $order): View
    {
        $this->abortUnlessManager();

        $orderData = $this->orderWithCreator($order);
        $receipt = $this->latestReceipt($order);

        abort_if(! $orderData, 404);
        abort_if($orderData->TrangThai !== self::STATUS_WAITING_PROCESS || ! $receipt, 403);

        return view('purchase-orders.return', [
            'order' => $orderData,
            'receipt' => $receipt,
            'returnCode' => $this->nextSequentialCode('PhieuDoiTra', 'MaPhieuDoiTra', 'PDT'),
        ]);
    }

    public function storeReturn(Request $request, string $order): RedirectResponse
    {
        $this->abortUnlessManager();

        $orderData = $this->orderWithCreator($order);
        $receipt = $this->latestReceipt($order);

        abort_if(! $orderData, 404);
        abort_if($orderData->TrangThai !== self::STATUS_WAITING_PROCESS || ! $receipt, 403);

        $validated = $request->validate([
            'NgayTao' => ['required', 'date'],
            'LoaiXuLy' => ['required', 'string', 'max:50'],
            'LyDo' => ['required', 'string', 'max:255'],
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
        ]);

        if (! Schema::hasTable('PhieuDoiTra')) {
            return back()->with('warning', 'Chưa có bảng phiếu đổi trả trong hệ thống hiện tại.');
        }

        DB::transaction(function () use ($validated, $order, $orderData, $receipt) {
            DB::table('PhieuDoiTra')->insert([
                'MaPhieuDoiTra' => $this->nextSequentialCode('PhieuDoiTra', 'MaPhieuDoiTra', 'PDT'),
                'NgayTao' => $validated['NgayTao'],
                'LoaiXuLy' => $validated['LoaiXuLy'],
                'LyDo' => $validated['LyDo'],
                'MaTaiKhoan' => $validated['MaTaiKhoan'],
                'MaPhieuNhan' => $receipt->MaPhieuNhan,
            ]);

            DB::table('DonDatHang')
                ->where('MaDonDatHang', $order)
                ->update([
                    'TrangThai' => self::STATUS_RETURNING,
                    'GhiChu' => $this->appendApprovalNote($order, 'Tạo phiếu đổi trả', $validated['MaTaiKhoan'], $validated['LoaiXuLy'] . ': ' . $validated['LyDo']),
                ]);

            $this->recordAudit(
                $order,
                'Tạo phiếu đổi trả',
                $orderData->TrangThai,
                self::STATUS_RETURNING,
                $validated['MaTaiKhoan'],
                $validated['LoaiXuLy'] . ': ' . $validated['LyDo']
            );
        });

        return redirect()
            ->route('don-hang.index')
            ->with('success', 'Đã tạo phiếu đổi trả và chuyển đơn hàng sang trạng thái Đang đổi trả.');
    }

    public function stockForm(string $order): View
    {
        $this->abortUnlessManager();

        $orderData = $this->orderWithCreator($order);
        $receipt = $this->latestReceipt($order);

        abort_if(! $orderData, 404);
        abort_if($orderData->TrangThai !== self::STATUS_RECEIVED || ! $receipt, 403);

        return view('purchase-orders.stock', [
            'order' => $orderData,
            'receipt' => $receipt,
            'accounts' => $this->accounts(),
            'stockCode' => $this->nextSequentialCode('PhieuNhapKho', 'MaPhieuNhap', 'PNK'),
        ]);
    }

    public function stockFromForm(Request $request, string $order): RedirectResponse
    {
        $this->abortUnlessManager();

        $orderData = $this->orderWithCreator($order);
        $receipt = $this->latestReceipt($order);

        abort_if(! $orderData, 404);
        abort_if($orderData->TrangThai !== self::STATUS_RECEIVED || ! $receipt, 403);

        $validated = $request->validate([
            'NgayNhap' => ['required', 'date'],
            'GhiChu' => ['nullable', 'string', 'max:255'],
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
        ]);

        if (Schema::hasTable('PhieuNhapKho')) {
            DB::table('PhieuNhapKho')->insert([
                'MaPhieuNhap' => $this->nextSequentialCode('PhieuNhapKho', 'MaPhieuNhap', 'PNK'),
                'NgayNhap' => $validated['NgayNhap'],
                'GhiChu' => $validated['GhiChu'] ?? null,
                'MaTaiKhoan' => $validated['MaTaiKhoan'],
                'MaPhieuNhan' => $receipt->MaPhieuNhan,
            ]);
        }

        return $this->markAsStocked($order, $validated['MaTaiKhoan'], $validated['GhiChu'] ?? 'Nhập kho từ form quản lý', 'don-hang.show');
    }

    public function approve(Request $request, string $order): RedirectResponse
    {
        $this->abortUnlessStoreChief();

        $request->validate([
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
            'GhiChuDuyet' => ['nullable', 'string', 'max:180'],
        ]);

        if (! $this->isApprovalAccount($request->MaTaiKhoan)) {
            return back()->with('warning', 'Chỉ tài khoản Cửa hàng trưởng mới được phê duyệt đơn mua.');
        }

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', $this->statusCandidates([self::STATUS_PENDING]))
            ->update([
                'TrangThai' => self::STATUS_WAITING_RECEIVE,
                'GhiChu' => $this->appendApprovalNote($order, 'Phê duyệt', $request->MaTaiKhoan, $request->GhiChuDuyet),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Phê duyệt đơn',
                $previousStatus,
                self::STATUS_WAITING_RECEIVE,
                $request->MaTaiKhoan,
                $request->GhiChuDuyet
            );
        }

        return back()->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã được phê duyệt và chuyển trạng thái thành Chờ nhận hàng.' : 'Chỉ phê duyệt được đơn đang chờ phê duyệt.'
        );
    }

    public function reject(Request $request, string $order): RedirectResponse
    {
        $this->abortUnlessStoreChief();

        $request->validate([
            'MaTaiKhoan' => ['required', 'exists:TaiKhoan,MaTaiKhoan'],
            'LyDoTuChoi' => ['required', 'string', 'max:180'],
        ]);

        if (! $this->isApprovalAccount($request->MaTaiKhoan)) {
            return back()->with('warning', 'Chỉ tài khoản Cửa hàng trưởng mới được từ chối đơn mua.');
        }

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', $this->statusCandidates([self::STATUS_PENDING]))
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
            $updated ? 'Đơn mua đã bị từ chối.' : 'Chỉ từ chối được đơn đang chờ phê duyệt.'
        );
    }

    public function cancel(string $order): RedirectResponse
    {
        $this->abortUnlessManager();

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', $this->statusCandidates(self::CANCELLABLE_STATUSES))
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
            $updated ? 'Đơn mua đã được hủy.' : 'Chỉ hủy được đơn đang chờ phê duyệt.'
        );
    }

    public function receive(string $order): RedirectResponse
    {
        $this->abortUnlessManager();

        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', $this->statusCandidates([self::STATUS_WAITING_RECEIVE]))
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
            $updated ? 'Đơn mua đã chuyển sang trạng thái Đã nhận hàng.' : 'Chỉ nhận hàng được đơn đang chờ xử lý.'
        );
    }

    public function stock(Request $_request, string $order): RedirectResponse
    {
        $this->abortUnlessManager();

        return $this->markAsStocked($order, null, 'Hoàn tất nhập kho', null);
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
            ->whereIn('VaiTro', ['Cua hang truong', 'Cửa hàng trưởng'])
            ->orderBy('MaTaiKhoan')
            ->get();

        return $accounts->isNotEmpty() ? $accounts : $this->accounts();
    }

    private function isApprovalAccount(string $accountCode): bool
    {
        return DB::table('TaiKhoan')
            ->where('MaTaiKhoan', $accountCode)
            ->whereIn('VaiTro', ['Cua hang truong', 'Cửa hàng trưởng'])
            ->exists();
    }

    private function ingredients()
    {
        return DB::table('NguyenLieu')
            ->select('MaNguyenLieu', 'TenNguyenLieu', 'DonViTinh', 'NhomHang', 'SoLuongTonKho')
            ->orderBy('MaNguyenLieu')
            ->get();
    }

    private function orderWithCreator(string $order): ?object
    {
        $orderData = DB::table('DonDatHang as d')
            ->join('TaiKhoan as t', 't.MaTaiKhoan', '=', 'd.MaTaiKhoan')
            ->select('d.*', 't.HoTen', 't.VaiTro')
            ->where('d.MaDonDatHang', $order)
            ->first();

        if ($orderData) {
            $orderData->TrangThai = $this->normalizeStatus($orderData->TrangThai);
        }

        return $orderData;
    }

    private function statusLabels(): array
    {
        $labels = [
            self::STATUS_PENDING => 'Chờ phê duyệt',
            self::STATUS_WAITING_RECEIVE => 'Chờ nhận hàng',
            self::STATUS_WAITING_PROCESS => 'Chờ xử lý',
            self::STATUS_RETURNING => 'Đang đổi trả',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_RECEIVED => 'Đã nhận hàng',
            self::STATUS_STOCKED => 'Đã nhập kho',
        ];

        foreach (self::STATUS_ALIASES as $alias => $canonical) {
            $labels[$alias] = $labels[$canonical] ?? $alias;
        }

        return $labels;
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
        return $this->normalizeStatus(DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->value('TrangThai'));
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

    private function nextSequentialCode(string $table, string $column, string $prefix): string
    {
        if (! Schema::hasTable($table)) {
            return $prefix . '001';
        }

        $lastCode = DB::table($table)
            ->where($column, 'like', $prefix . '%')
            ->orderByDesc($column)
            ->value($column);

        $number = $lastCode ? ((int) substr((string) $lastCode, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function validatedOrderPayload(Request $request): array
    {
        $validated = $request->validate([
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

    private function latestReceipt(string $order): ?object
    {
        if (! Schema::hasTable('PhieuNhanHang')) {
            return null;
        }

        return DB::table('PhieuNhanHang as p')
            ->leftJoin('TaiKhoan as t', 't.MaTaiKhoan', '=', 'p.MaTaiKhoan')
            ->select('p.*', 't.HoTen')
            ->where('p.MaDonDatHang', $order)
            ->orderByDesc('p.NgayNhan')
            ->orderByDesc('p.MaPhieuNhan')
            ->first();
    }

    private function reconciliationItems(string $order, ?string $receiptCode)
    {
        $orderItems = DB::table('ChiTietDonDatHang as c')
            ->join('NguyenLieu as n', 'n.MaNguyenLieu', '=', 'c.MaNguyenLieu')
            ->select('c.MaNguyenLieu', 'n.TenNguyenLieu', 'n.DonViTinh', 'c.SoLuongDat')
            ->where('c.MaDonDatHang', $order)
            ->orderBy('c.MaNguyenLieu')
            ->get();

        if (! $receiptCode || ! Schema::hasTable('LoHang')) {
            return $orderItems->map(function ($item) {
                $item->SoLuongNhan = 0;
                $item->ChenhLech = 0 - (int) $item->SoLuongDat;
                $item->KetQua = 'Thiếu';

                return $item;
            });
        }

        $receivedMap = DB::table('LoHang')
            ->select('MaNguyenLieu', DB::raw('SUM(SoLuongNhap) as SoLuongNhan'))
            ->where('MaPhieuNhan', $receiptCode)
            ->groupBy('MaNguyenLieu')
            ->pluck('SoLuongNhan', 'MaNguyenLieu');

        return $orderItems->map(function ($item) use ($receivedMap) {
            $received = (int) ($receivedMap[$item->MaNguyenLieu] ?? 0);
            $ordered = (int) $item->SoLuongDat;
            $difference = $received - $ordered;

            $item->SoLuongNhan = $received;
            $item->ChenhLech = $difference;
            $item->KetQua = match (true) {
                $difference === 0 => 'Khớp',
                $difference < 0 => 'Thiếu',
                default => 'Dư',
            };

            return $item;
        });
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

    private function normalizeStatus(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        return self::STATUS_ALIASES[$status] ?? $status;
    }

    private function statusCandidates(array $statuses): array
    {
        $normalizedStatuses = array_values(array_unique(array_map(
            fn (?string $status) => $this->normalizeStatus($status),
            $statuses
        )));

        $aliasedStatuses = array_keys(array_filter(
            self::STATUS_ALIASES,
            fn (string $canonical) => in_array($canonical, $normalizedStatuses, true)
        ));

        return array_values(array_unique(array_merge($normalizedStatuses, $aliasedStatuses)));
    }

    private function abortUnlessManager(): void
    {
        abort_unless($this->isManagerUser(), 403, 'Chỉ Quản lý được thực hiện thao tác này.');
    }

    private function abortUnlessStoreChief(): void
    {
        abort_unless($this->isStoreChiefUser(), 403, 'Chỉ Cửa hàng trưởng được thực hiện thao tác này.');
    }

    private function isManagerUser(): bool
    {
        $role = auth()->user()->VaiTro ?? null;

        return in_array($role, ['Quan ly', 'Quản lý'], true);
    }

    private function isStoreChiefUser(): bool
    {
        $role = auth()->user()->VaiTro ?? null;

        return in_array($role, ['Cua hang truong', 'Cửa hàng trưởng'], true);
    }

    private function markAsStocked(string $order, ?string $accountCode, ?string $note, ?string $redirectRoute): RedirectResponse
    {
        $previousStatus = $this->currentStatus($order);

        $updated = DB::table('DonDatHang')
            ->where('MaDonDatHang', $order)
            ->whereIn('TrangThai', $this->statusCandidates([self::STATUS_RECEIVED]))
            ->update([
                'TrangThai' => self::STATUS_STOCKED,
                'GhiChu' => $this->appendApprovalNote($order, 'Nhập kho', $accountCode ?? 'Hệ thống', $note),
            ]);

        if ($updated) {
            $this->recordAudit(
                $order,
                'Nhập kho',
                $previousStatus,
                self::STATUS_STOCKED,
                $accountCode,
                $note ?? 'Hoàn tất nhập kho'
            );
        }

        $target = $redirectRoute ? redirect()->route($redirectRoute, $order) : back();

        return $target->with(
            $updated ? 'success' : 'warning',
            $updated ? 'Đơn mua đã chuyển sang trạng thái Đã nhập kho.' : 'Chỉ nhập kho được đơn đã nhận hàng.'
        );
    }
}
