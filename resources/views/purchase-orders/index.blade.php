@extends('layouts.app')

@section('title', 'Đặt hàng & phê duyệt')

@php
    $statusLabels = [
        'Cho phe duyet' => 'Chờ phê duyệt',
        'Dang xu ly' => 'Đang xử lý',
        'Da duyet' => 'Đã duyệt',
        'Tu choi' => 'Từ chối',
        'Da huy' => 'Đã hủy',
        'Da nhan hang' => 'Đã nhận hàng',
        'Da nhap kho' => 'Đã nhập kho',
    ];
    $statusClass = function (?string $status) {
        return match ($status) {
            'Da duyet' => 'approved',
            'Cho phe duyet' => 'pending',
            'Tu choi', 'Da huy' => 'rejected',
            'Dang xu ly' => 'processing',
            'Da nhan hang', 'Da nhap kho' => 'received',
            default => '',
        };
    };
    $sortIcon = function (string $column) use ($sort, $direction) {
        if ($sort !== $column) {
            return '↕';
        }

        return $direction === 'asc' ? '↑' : '↓';
    };
    $sortUrl = function (string $column) use ($sort, $direction, $search, $status) {
        $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

        return route('purchase-orders.index', array_filter([
            'search' => $search !== '' ? $search : null,
            'status' => $status ?: null,
            'sort' => $column,
            'direction' => $nextDirection,
        ], fn ($value) => $value !== null));
    };
@endphp

@section('content')
    <div class="topbar">
        <div>
            <h1>Quy trình đặt hàng & phê duyệt đơn mua</h1>
            <p class="subtle">Theo dõi đề xuất mua nguyên liệu, tạo đơn mới và chốt phê duyệt trước khi nhận hàng.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('purchase-orders.create') }}">+ Tạo đơn mua</a>
    </div>

    <div class="stats">
        <div class="stat">
            <span class="subtle">Chờ phê duyệt</span>
            <strong>{{ $summaryCards['Cho phe duyet'] ?? 0 }}</strong>
        </div>
        <div class="stat">
            <span class="subtle">Đang xử lý</span>
            <strong>{{ $summaryCards['Dang xu ly'] ?? 0 }}</strong>
        </div>
        <div class="stat">
            <span class="subtle">Đã duyệt</span>
            <strong>{{ $summaryCards['Da duyet'] ?? 0 }}</strong>
        </div>
        <div class="stat">
            <span class="subtle">Đã hủy</span>
            <strong>{{ $summaryCards['Da huy'] ?? 0 }}</strong>
        </div>
    </div>

    <div class="panel">
        <form class="toolbar" method="get" action="{{ route('purchase-orders.index') }}">
            <div class="field">
                <label for="search">Tìm kiếm</label>
                <input id="search" name="search" value="{{ $search }}" placeholder="Mã đơn, người tạo, ghi chú">
            </div>
            <div class="field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ $statusLabels[$option] ?? $option }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-secondary" type="submit">Lọc</button>
            <a class="btn btn-secondary" href="{{ route('purchase-orders.index') }}">Đặt lại</a>
        </form>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th><a href="{{ $sortUrl('code') }}">Mã đơn {{ $sortIcon('code') }}</a></th>
                    <th><a href="{{ $sortUrl('date') }}">Ngày đặt {{ $sortIcon('date') }}</a></th>
                    <th>Người tạo</th>
                    <th><a href="{{ $sortUrl('status') }}">Trạng thái {{ $sortIcon('status') }}</a></th>
                    <th><a href="{{ $sortUrl('items') }}">Mặt hàng {{ $sortIcon('items') }}</a></th>
                    <th><a href="{{ $sortUrl('quantity') }}">Tổng SL {{ $sortIcon('quantity') }}</a></th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><strong>{{ $order->MaDonDatHang }}</strong></td>
                        <td>{{ \Illuminate\Support\Carbon::parse($order->NgayDat)->format('d/m/Y') }}</td>
                        <td>{{ $order->HoTen }}</td>
                        <td><span class="badge {{ $statusClass($order->TrangThai) }}">{{ $statusLabels[$order->TrangThai] ?? $order->TrangThai }}</span></td>
                        <td>{{ $order->SoMatHang }}</td>
                        <td>{{ number_format($order->TongSoLuong) }}</td>
                        <td>{{ $order->GhiChu ?: '-' }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Chi tiết</a>
                                @if (in_array($order->TrangThai, ['Cho phe duyet', 'Dang xu ly'], true))
                                    <a class="btn btn-secondary" href="{{ route('purchase-orders.edit', $order->MaDonDatHang) }}">Sửa</a>
                                    <form method="post" action="{{ route('purchase-orders.cancel', $order->MaDonDatHang) }}" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?');">
                                        @csrf
                                        <button class="btn btn-danger" type="submit">Hủy đơn</button>
                                    </form>
                                @elseif ($order->TrangThai === 'Da duyet')
                                    <form method="post" action="{{ route('purchase-orders.receive', $order->MaDonDatHang) }}">
                                        @csrf
                                        <button class="btn btn-success" type="submit">Nhận hàng</button>
                                    </form>
                                @elseif ($order->TrangThai === 'Da nhan hang')
                                    <form method="post" action="{{ route('purchase-orders.stock', $order->MaDonDatHang) }}">
                                        @csrf
                                        <button class="btn btn-success" type="submit">Nhập kho</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Chưa có đơn mua phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $orders->links() }}</div>
    </div>
@endsection
