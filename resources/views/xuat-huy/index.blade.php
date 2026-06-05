@extends('layouts.app')

@section('title', 'Xuất hủy')

@php
    $statusClasses = [
        'Chờ duyệt' => 'bg-warning text-dark',
        'Đã duyệt' => 'bg-success',
        'Đã hủy' => 'bg-secondary',
        'Từ chối' => 'bg-danger',
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Xuất hủy</h2>
        <p class="text-muted mb-0">Theo dõi các phiếu xuất hủy đã phát sinh từ luồng đối soát, kiểm kê và xử lý nguyên liệu lỗi.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('kiem-ke.index') }}" class="btn btn-outline-secondary">Sang kiểm kê</a>
        <a href="{{ route('giai-trinh.index') }}" class="btn btn-outline-secondary">Mở giải trình</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tổng phiếu hủy</div>
                <div class="display-6 fw-bold text-lotteria">{{ $summary['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Chờ duyệt</div>
                <div class="display-6 fw-bold text-warning">{{ $summary['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Đã duyệt</div>
                <div class="display-6 fw-bold text-success">{{ $summary['approved'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tổng SL hủy</div>
                <div class="display-6 fw-bold text-danger">{{ $summary['total_quantity'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card page-card">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="fw-bold mb-1">Danh sách phiếu xuất hủy gần đây</h5>
                <p class="text-muted small mb-0">Giữ route thật của hệ thống, nhưng hiển thị lại theo dạng bảng tổng quan để dễ theo dõi.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Mã phiếu kiểm</th>
                                <th>Ngày tạo</th>
                                <th>Trạng thái</th>
                                <th>Lý do hủy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPhieuHuy as $phieu)
                                <tr>
                                    <td class="fw-bold">{{ $phieu->MaPhieuHuy }}</td>
                                    <td>{{ $phieu->MaPhieuKiemKe ?: '-' }}</td>
                                    <td>{{ $phieu->NgayTao ? \Carbon\Carbon::parse($phieu->NgayTao)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $statusClasses[$phieu->TrangThai] ?? 'bg-secondary' }}">
                                            {{ $phieu->TrangThai ?: 'Chưa rõ' }}
                                        </span>
                                    </td>
                                    <td>{{ $phieu->LyDoHuy ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        Chưa có dữ liệu phiếu xuất hủy để hiển thị.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card page-card h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Đi nhanh</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary text-start">Dashboard</a>
                    <a href="{{ route('don-hang.index') }}" class="btn btn-outline-secondary text-start">Đơn hàng</a>
                    <a href="{{ route('xuatkho.index') }}" class="btn btn-outline-secondary text-start">Xuất kho</a>
                    <a href="{{ route('kiem-ke.index') }}" class="btn btn-outline-secondary text-start">Kiểm kê</a>
                    <a href="{{ route('giai-trinh.index') }}" class="btn btn-outline-secondary text-start">Giải trình</a>
                </div>

                <div class="alert alert-light border mt-4 mb-0">
                    Phiếu xuất hủy thường được sinh ra từ luồng kiểm kê có hàng lỗi, hết hạn hoặc thất thoát cần loại khỏi kho.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
