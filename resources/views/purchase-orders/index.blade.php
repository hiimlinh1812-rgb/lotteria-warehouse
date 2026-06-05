@extends('layouts.app')

@section('title', 'Trang Đơn Hàng')

@php
    $isManagerUser = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Quan ly', 'Quản lý'], true);
    $isStoreChiefUser = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Cua hang truong', 'Cửa hàng trưởng'], true);
    $managerMode = request()->routeIs('don-hang.*');
    $routePrefix = $managerMode ? 'don-hang' : 'purchase-orders';
    $statusLabels = [
        'Cho phe duyet' => 'Chờ phê duyệt',
        'Dang xu ly' => 'Đang xử lý',
        'Cho xu ly' => 'Chờ xử lý',
        'Dang doi tra' => 'Đang đổi trả',
        'Da duyet' => 'Đã duyệt',
        'Tu choi' => 'Từ chối',
        'Da huy' => 'Đã hủy',
        'Da nhan hang' => 'Đã nhận hàng',
        'Da nhap kho' => 'Đã nhập kho',
    ];
    $statusClass = function (?string $status) {
        return match ($status) {
            'Cho phe duyet' => 'pending',
            'Dang xu ly' => 'processing',
            'Cho xu ly' => 'processing',
            'Dang doi tra' => 'processing',
            'Da duyet' => 'approved',
            'Da nhan hang' => 'received',
            'Da nhap kho' => 'stocked',
            'Tu choi' => 'rejected',
            'Da huy' => 'cancelled',
            default => '',
        };
    };
    $sortIcon = function (string $column) use ($sort, $direction) {
        if ($sort !== $column) {
            return '↕';
        }

        return $direction === 'asc' ? '↑' : '↓';
    };
    $sortUrl = function (string $column) use ($sort, $direction, $search, $status, $routePrefix) {
        $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

        return route($routePrefix . '.index', array_filter([
            'search' => $search !== '' ? $search : null,
            'status' => $status ?: null,
            'sort' => $column,
            'direction' => $nextDirection,
        ], fn ($value) => $value !== null));
    };
@endphp

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">{{ $managerMode ? 'Trang Đơn Hàng' : ($isStoreChiefUser ? 'Phê Duyệt Đơn Mua' : 'Đặt hàng & phê duyệt đơn mua') }}</h2>
    </div>
    @if ($isManagerUser && $managerMode)
        <a href="{{ route($routePrefix . '.create') }}" class="btn btn-lotteria fw-bold">+ Tạo đơn đặt hàng</a>
    @endif
</div>

@if ($managerMode)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Chờ phê duyệt</div>
                    <div class="display-6 fw-bold text-warning-emphasis">{{ $managerSummary['Cho phe duyet'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Chờ xử lý</div>
                    <div class="display-6 fw-bold text-primary">{{ $managerSummary['Cho xu ly'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Đã nhận hàng</div>
                    <div class="display-6 fw-bold text-success">{{ $managerSummary['Da nhan hang'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Đã nhập kho</div>
                    <div class="display-6 fw-bold text-info-emphasis">{{ $managerSummary['Da nhap kho'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
@elseif ($isStoreChiefUser)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Chờ phê duyệt</div>
                    <div class="display-6 fw-bold text-warning-emphasis">{{ $summaryCards['Cho phe duyet'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Đã duyệt</div>
                    <div class="display-6 fw-bold text-success">{{ $summaryCards['Da duyet'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Từ chối / Hủy</div>
                    <div class="display-6 fw-bold text-danger">{{ ($summaryCards['Tu choi'] ?? 0) + ($summaryCards['Da huy'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="card page-card mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="get" action="{{ route($routePrefix . '.index') }}">
            <div class="col-md-5">
                <label for="search" class="form-label fw-semibold">Tìm kiếm</label>
                <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Mã đơn, người tạo, ghi chú">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">Trạng thái</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ $statusLabels[$option] ?? $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-lotteria">Lọc</button>
            </div>
            <div class="col-md-auto">
                <a class="btn btn-outline-secondary" href="{{ route($routePrefix . '.index') }}">Đặt lại</a>
            </div>
        </form>
    </div>
</div>

<div class="card page-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><a class="text-decoration-none text-dark" href="{{ $sortUrl('code') }}">Mã đơn {{ $sortIcon('code') }}</a></th>
                        <th><a class="text-decoration-none text-dark" href="{{ $sortUrl('date') }}">Ngày đặt {{ $sortIcon('date') }}</a></th>
                        <th>Người tạo</th>
                        <th><a class="text-decoration-none text-dark" href="{{ $sortUrl('status') }}">Trạng thái {{ $sortIcon('status') }}</a></th>
                        <th><a class="text-decoration-none text-dark" href="{{ $sortUrl('items') }}">Mặt hàng {{ $sortIcon('items') }}</a></th>
                        <th><a class="text-decoration-none text-dark" href="{{ $sortUrl('quantity') }}">Tổng SL {{ $sortIcon('quantity') }}</a></th>
                        <th>Ghi chú</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="fw-bold">{{ $order->MaDonDatHang }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($order->NgayDat)->format('d/m/Y') }}</td>
                            <td>{{ $order->HoTen }}</td>
                            <td><span class="status-badge {{ $statusClass($order->TrangThai) }}">{{ $statusLabels[$order->TrangThai] ?? $order->TrangThai }}</span></td>
                            <td>{{ $order->SoMatHang }}</td>
                            <td>{{ number_format($order->TongSoLuong) }}</td>
                            <td>{{ $order->GhiChu ?: '-' }}</td>
                            <td class="text-end">
                                @if ($managerMode)
                                    @if ($order->TrangThai === 'Cho phe duyet')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route($routePrefix . '.edit', $order->MaDonDatHang) }}">Sửa</a>
                                            <form method="post" action="{{ route($routePrefix . '.cancel', $order->MaDonDatHang) }}" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Hủy</button>
                                            </form>
                                        </div>
                                    @elseif ($order->TrangThai === 'Cho xu ly')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem</a>
                                            <a class="btn btn-sm btn-outline-danger" href="{{ route('don-hang.return.create', $order->MaDonDatHang) }}">Đổi trả</a>
                                        </div>
                                    @elseif ($order->TrangThai === 'Dang doi tra')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem lịch sử/chi tiết</a>
                                        </div>
                                    @elseif ($order->TrangThai === 'Da nhan hang')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem</a>
                                            <a class="btn btn-sm btn-success" href="{{ route('don-hang.stock.create', $order->MaDonDatHang) }}">Nhập kho</a>
                                        </div>
                                    @elseif ($order->TrangThai === 'Da nhap kho')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem lịch sử/chi tiết</a>
                                        </div>
                                    @else
                                        <span class="text-muted small">Không có thao tác</span>
                                    @endif
                                @elseif ($isStoreChiefUser)
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        @if ($order->TrangThai === 'Cho phe duyet')
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Xem & duyệt</a>
                                        @else
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Xem lịch sử/chi tiết</a>
                                        @endif
                                    </div>
                                @else
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Chi tiết</a>
                                        @if ($order->TrangThai === 'Cho phe duyet')
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('purchase-orders.edit', $order->MaDonDatHang) }}">Sửa</a>
                                            <form method="post" action="{{ route('purchase-orders.cancel', $order->MaDonDatHang) }}" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Hủy đơn</button>
                                            </form>
                                        @elseif ($order->TrangThai === 'Da duyet')
                                            <form method="post" action="{{ route('purchase-orders.receive', $order->MaDonDatHang) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" type="submit">Nhận hàng</button>
                                            </form>
                                        @elseif ($order->TrangThai === 'Da nhan hang')
                                            <form method="post" action="{{ route('purchase-orders.stock', $order->MaDonDatHang) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-success" type="submit">Nhập kho</button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có đơn hàng phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $orders->links() }}</div>
    </div>
</div>
@endsection
