@extends('layouts.app')

@section('title', 'Chi tiết đơn mua ' . $order->MaDonDatHang)

@php
    $isManagerUser = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Quan ly', 'Quản lý'], true);
    $isStoreChiefUser = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Cua hang truong', 'Cửa hàng trưởng'], true);
    $currentUser = auth()->user();
    $managerMode = request()->routeIs('don-hang.*') && $isManagerUser;
    $routePrefix = $managerMode ? 'don-hang' : 'purchase-orders';
    $statusClass = match ($order->TrangThai) {
        'Cho phe duyet' => 'pending',
        'Dang xu ly' => 'processing',
        'Da duyet' => 'approved',
        'Da nhan hang' => 'received',
        'Da nhap kho' => 'stocked',
        'Tu choi' => 'rejected',
        'Da huy' => 'cancelled',
        default => '',
    };
    $canApprove = $isStoreChiefUser && $order->TrangThai === 'Cho phe duyet';
    $canEdit = $isManagerUser && $order->TrangThai === 'Cho phe duyet';
    $canProcess = false;
    $canCancel = $isManagerUser && $order->TrangThai === 'Cho phe duyet';
    $canReceive = $isManagerUser && $order->TrangThai === 'Da duyet';
    $canStock = $isManagerUser && $order->TrangThai === 'Da nhan hang';
    $totalReceived = collect($reconciliationItems)->sum('SoLuongNhan');
@endphp

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">
            {{ $managerMode ? 'Kiểm tra kết quả đối soát đơn hàng ' : ($isStoreChiefUser ? 'Phê Duyệt Đơn Mua ' : 'Chi tiết đơn mua ') }}{{ $order->MaDonDatHang }}
        </h2>
        <p class="text-muted mb-0">Lập ngày {{ \Illuminate\Support\Carbon::parse($order->NgayDat)->format('d/m/Y') }} bởi {{ $order->HoTen }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="{{ route($routePrefix . '.index') }}">Quay lại</a>
        @if (! $managerMode && $canEdit)
            <a class="btn btn-outline-primary" href="{{ route('purchase-orders.edit', $order->MaDonDatHang) }}">Sửa đơn</a>
        @endif
        @if ($managerMode && $canStock)
            <a class="btn btn-outline-danger" href="{{ route('don-hang.return.create', $order->MaDonDatHang) }}">Đổi trả</a>
            <a class="btn btn-success" href="{{ route('don-hang.stock.create', $order->MaDonDatHang) }}">Nhập kho</a>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small text-uppercase fw-semibold">Trạng thái</div>
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabels[$order->TrangThai] ?? $order->TrangThai }}</span>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small text-uppercase fw-semibold">Người lập</div>
                        <div class="fw-semibold">{{ $order->MaTaiKhoan }} - {{ $order->HoTen }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small text-uppercase fw-semibold">Vai trò</div>
                        <div class="fw-semibold">{{ $order->VaiTro }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small text-uppercase fw-semibold">Ghi chú</div>
                        <div>{{ $order->GhiChu ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-2">Phiếu nhận gần nhất</div>
                @if ($receipt)
                    <div class="mb-2"><strong>{{ $receipt->MaPhieuNhan }}</strong></div>
                    <div class="small text-muted">Ngày nhận: {{ \Illuminate\Support\Carbon::parse($receipt->NgayNhan)->format('d/m/Y') }}</div>
                    <div class="small text-muted">Người nhận: {{ $receipt->HoTen ?: ($receipt->MaTaiKhoan ?? 'Chưa xác định') }}</div>
                    <div class="small text-muted">Ghi chú: {{ $receipt->GhiChu ?: '-' }}</div>
                @else
                    <div class="text-muted">Chưa có dữ liệu phiếu nhận hàng để đối soát.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card page-card">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="mb-1 fw-bold">Chi tiết nguyên liệu</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã NL</th>
                                <th>Tên nguyên liệu</th>
                                <th>Nhóm hàng</th>
                                <th>Tồn kho</th>
                                <th>Số lượng đặt</th>
                                <th>Đơn vị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item->MaNguyenLieu }}</td>
                                    <td>{{ $item->TenNguyenLieu }}</td>
                                    <td>{{ $item->NhomHang }}</td>
                                    <td>{{ number_format($item->SoLuongTonKho) }}</td>
                                    <td class="fw-bold">{{ number_format($item->SoLuongDat) }}</td>
                                    <td>{{ $item->DonViTinh }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Tổng số lượng đặt</th>
                                <th>{{ number_format($items->sum('SoLuongDat')) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($managerMode)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Mặt hàng khớp</div>
                    <div class="display-6 fw-bold text-success">{{ $reconciliationSummary['matched'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Mặt hàng thiếu</div>
                    <div class="display-6 fw-bold text-warning">{{ $reconciliationSummary['short'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Mặt hàng dư</div>
                    <div class="display-6 fw-bold text-danger">{{ $reconciliationSummary['extra'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-1 fw-bold">Kiểm tra kết quả đối soát</h5>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã NL</th>
                            <th>Tên nguyên liệu</th>
                            <th>Số đặt</th>
                            <th>Thực nhận</th>
                            <th>Chênh lệch</th>
                            <th>Kết quả</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reconciliationItems as $item)
                            <tr>
                                <td class="fw-bold">{{ $item->MaNguyenLieu }}</td>
                                <td>{{ $item->TenNguyenLieu }}</td>
                                <td>{{ number_format($item->SoLuongDat) }} {{ $item->DonViTinh }}</td>
                                <td>{{ number_format($item->SoLuongNhan) }} {{ $item->DonViTinh }}</td>
                                <td class="{{ $item->ChenhLech === 0 ? 'text-success' : ($item->ChenhLech < 0 ? 'text-warning' : 'text-danger') }}">
                                    {{ $item->ChenhLech > 0 ? '+' : '' }}{{ number_format($item->ChenhLech) }}
                                </td>
                                <td>
                                    <span class="status-badge {{ $item->KetQua === 'Khớp' ? 'approved' : ($item->KetQua === 'Thiếu' ? 'pending' : 'rejected') }}">
                                        {{ $item->KetQua }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu đối soát.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Tổng cộng</th>
                            <th>{{ number_format($items->sum('SoLuongDat')) }}</th>
                            <th>{{ number_format($totalReceived) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@elseif ($isStoreChiefUser)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Ưu tiên</div>
                    <div class="fw-bold {{ filled($order->GhiChu) && str_contains(mb_strtolower($order->GhiChu), 'khẩn') ? 'text-danger' : 'text-success' }}">
                        {{ filled($order->GhiChu) && str_contains(mb_strtolower($order->GhiChu), 'khẩn') ? 'Cần xử lý sớm' : 'Bình thường' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Số mặt hàng</div>
                    <div class="display-6 fw-bold text-primary">{{ $items->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Tổng số lượng đặt</div>
                    <div class="display-6 fw-bold text-lotteria">{{ number_format($items->sum('SoLuongDat')) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-1 fw-bold">Phê duyệt đơn mua</h5>
        </div>
        <div class="card-body px-4 pb-4">
            @if ($canApprove)
                <div class="row g-4">
                    <div class="col-lg-6">
                        <form method="post" action="{{ route('purchase-orders.approve', $order->MaDonDatHang) }}" class="border border-success-subtle rounded-4 p-3 h-100 bg-success bg-opacity-10">
                            @csrf
                            <h6 class="fw-bold">Phê duyệt đơn</h6>
                            <div class="mb-3">
                                <label for="approve-account" class="form-label fw-semibold">Cửa hàng trưởng</label>
                                <input id="approve-account" class="form-control" value="{{ ($currentUser->MaTaiKhoan ?? '') . ' - ' . ($currentUser->HoTen ?? '') }}" readonly>
                                <input type="hidden" name="MaTaiKhoan" value="{{ $currentUser->MaTaiKhoan ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label for="GhiChuDuyet" class="form-label fw-semibold">Ghi chú phê duyệt</label>
                                <input id="GhiChuDuyet" name="GhiChuDuyet" maxlength="180" class="form-control" placeholder="Ví dụ: đồng ý mua hàng cho ca sáng">
                            </div>
                            <button class="btn btn-success" type="submit">Phê duyệt</button>
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <form method="post" action="{{ route('purchase-orders.reject', $order->MaDonDatHang) }}" class="border border-danger-subtle rounded-4 p-3 h-100 bg-danger bg-opacity-10">
                            @csrf
                            <h6 class="fw-bold">Từ chối đơn</h6>
                            <div class="mb-3">
                                <label for="reject-account" class="form-label fw-semibold">Cửa hàng trưởng</label>
                                <input id="reject-account" class="form-control" value="{{ ($currentUser->MaTaiKhoan ?? '') . ' - ' . ($currentUser->HoTen ?? '') }}" readonly>
                                <input type="hidden" name="MaTaiKhoan" value="{{ $currentUser->MaTaiKhoan ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label for="LyDoTuChoi" class="form-label fw-semibold">Lý do từ chối</label>
                                <input id="LyDoTuChoi" name="LyDoTuChoi" maxlength="180" class="form-control" placeholder="Nhập lý do bắt buộc">
                            </div>
                            <button class="btn btn-outline-danger" type="submit">Từ chối</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="text-muted">Đơn mua này đang ở trạng thái {{ $statusLabels[$order->TrangThai] ?? $order->TrangThai }}.</div>
            @endif
        </div>
    </div>
@else
    <div class="card page-card mb-4">
        <div class="card-body px-4 py-4">
            <div class="text-muted">Tài khoản hiện tại chỉ có quyền xem chi tiết đơn mua.</div>
        </div>
    </div>
@endif

<details class="card page-card mb-4">
    <summary class="card-header bg-white fw-bold" style="cursor:pointer;">Truy vết thao tác</summary>
    <div class="card-body px-4 pb-4">
        @if ($auditTrail->isEmpty())
            <p class="text-muted mb-0">Chưa có dữ liệu truy vết cho đơn mua này.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                            <th>Người thực hiện</th>
                            <th>Từ trạng thái</th>
                            <th>Sang trạng thái</th>
                            <th>Nội dung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($auditTrail as $trace)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($trace->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td class="fw-bold">{{ $trace->HanhDong }}</td>
                                <td>{{ $trace->MaTaiKhoan ?: 'Hệ thống' }}</td>
                                <td>{{ $trace->TrangThaiTruoc ? ($statusLabels[$trace->TrangThaiTruoc] ?? $trace->TrangThaiTruoc) : '-' }}</td>
                                <td>{{ $trace->TrangThaiSau ? ($statusLabels[$trace->TrangThaiSau] ?? $trace->TrangThaiSau) : '-' }}</td>
                                <td>{{ $trace->NoiDung ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</details>
@endsection
