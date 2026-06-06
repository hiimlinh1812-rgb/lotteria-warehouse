@extends('layouts.app')

@section('title', 'Trang Đơn Hàng')

@php
    $isManagerUser = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Quan ly', 'Quản lý'], true);
    $isStoreChiefUser = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Cua hang truong', 'Cửa hàng trưởng'], true);
    $managerMode = request()->routeIs('don-hang.*');
    $routePrefix = $managerMode ? 'don-hang' : 'purchase-orders';
    $statusLabels = [
        'Chờ phê duyệt' => 'Chờ phê duyệt',
        'Chờ nhận hàng' => 'Chờ nhận hàng',
        'Chờ xử lý' => 'Chờ xử lý',
        'Đang đổi trả' => 'Đang đổi trả',
        'Từ chối' => 'Từ chối',
        'Đã hủy' => 'Đã hủy',
        'Đã nhận hàng' => 'Đã nhận hàng',
        'Đã nhập kho' => 'Đã nhập kho',
    ];
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
                    <div class="display-6 fw-bold text-warning-emphasis">{{ $managerSummary['Chờ phê duyệt'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Chờ xử lý</div>
                    <div class="display-6 fw-bold text-primary">{{ $managerSummary['Chờ xử lý'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Đã nhận hàng</div>
                    <div class="display-6 fw-bold text-success">{{ $managerSummary['Đã nhận hàng'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Đã nhập kho</div>
                    <div class="display-6 fw-bold text-info-emphasis">{{ $managerSummary['Đã nhập kho'] ?? 0 }}</div>
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
                    <div class="display-6 fw-bold text-warning-emphasis">{{ $summaryCards['Chờ phê duyệt'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Đã duyệt</div>
                    <div class="display-6 fw-bold text-success">{{ $summaryCards['Chờ nhận hàng'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card summary-tile h-100">
                <div class="card-body">
                    <div class="text-muted fw-semibold">Từ chối / Hủy</div>
                    <div class="display-6 fw-bold text-danger">{{ ($summaryCards['Từ chối'] ?? 0) + ($summaryCards['Đã hủy'] ?? 0) }}</div>
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
                            <td><x-status-badge :status="$order->TrangThai" /></td>
                            <td>{{ $order->SoMatHang }}</td>
                            <td>{{ number_format($order->TongSoLuong) }}</td>
                            <td>{{ $order->GhiChu ?: '-' }}</td>
                            <td class="text-end">
                                @if ($managerMode)
                                    @if ($order->TrangThai === 'Chờ phê duyệt')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route($routePrefix . '.edit', $order->MaDonDatHang) }}">Sửa</a>
                                            <form method="post" action="{{ route($routePrefix . '.cancel', $order->MaDonDatHang) }}" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Hủy</button>
                                            </form>
                                        </div>
                                    @elseif (in_array($order->TrangThai, ['Chờ nhận hàng', 'Từ chối', 'Đang đổi trả', 'Đã nhập kho']))
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem lịch sử/chi tiết</a>
                                        </div>
                                    @elseif ($order->TrangThai === 'Chờ xử lý')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem</a>
                                            <a class="btn btn-sm btn-outline-danger" href="{{ route('don-hang.return.create', $order->MaDonDatHang) }}">Đổi trả</a>
                                        </div>
                                    @elseif ($order->TrangThai === 'Đã nhận hàng')
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Xem</a>
                                            <a class="btn btn-sm btn-success" href="{{ route('don-hang.stock.create', $order->MaDonDatHang) }}">Nhập kho</a>
                                        </div>
                                    @else
                                        <span class="text-muted small">Không có thao tác</span>
                                    @endif
                                @elseif ($isStoreChiefUser)
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        @if ($order->TrangThai === 'Chờ phê duyệt')
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Xem & duyệt</a>
                                        @else
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Xem lịch sử/chi tiết</a>
                                        @endif
                                    </div>
                                @else
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Chi tiết</a>
                                        @if ($order->TrangThai === 'Chờ phê duyệt')
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('purchase-orders.edit', $order->MaDonDatHang) }}">Sửa</a>
                                            <form method="post" action="{{ route('purchase-orders.cancel', $order->MaDonDatHang) }}" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Hủy đơn</button>
                                            </form>
                                        @elseif ($order->TrangThai === 'Chờ nhận hàng')
                                            <form method="post" action="{{ route('purchase-orders.receive', $order->MaDonDatHang) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" type="submit">Nhận hàng</button>
                                            </form>
                                        @elseif ($order->TrangThai === 'Đã nhận hàng')
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
