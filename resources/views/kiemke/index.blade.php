@extends('layouts.app')

@section('title', 'Kiểm kê')

@php
    $statusClasses = [
        'Chờ duyệt' => 'bg-warning text-dark',
        'Đã duyệt' => 'bg-success',
        'Từ chối' => 'bg-danger',
        'Nháp' => 'bg-secondary',
    ];

    $typeClasses = [
        'Cuối ngày' => 'bg-danger-subtle text-danger',
        'Định kỳ' => 'bg-primary-subtle text-primary',
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Kiểm kê</h2>
        <p class="text-muted mb-0">Màn tổng quan kiểm kê dành cho quản lý, gom lại các luồng kiểm kê bếp, kiểm kho chính và số liệu chờ duyệt.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('quanly.kiemke.bep') }}" class="btn btn-outline-secondary">Duyệt kiểm kê bếp</a>
        <a href="{{ route('quanly.khochinh.duyet') }}" class="btn btn-outline-secondary">Duyệt kiểm kho chính</a>
        <a href="{{ route('cht.khochinh.thongke') }}" class="btn btn-outline-secondary">Thống kê tồn kho</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4 col-xl">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tổng phiếu kiểm kê</div>
                <div class="display-6 fw-bold text-lotteria">{{ $summary['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Cuối ngày</div>
                <div class="display-6 fw-bold text-danger">{{ $summary['end_of_day'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Định kỳ</div>
                <div class="display-6 fw-bold text-primary">{{ $summary['periodic'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Chờ duyệt</div>
                <div class="display-6 fw-bold text-warning">{{ $summary['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Đã duyệt</div>
                <div class="display-6 fw-bold text-success">{{ $summary['approved'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <a href="{{ route('quanly.kiemke.bep') }}" class="text-decoration-none">
            <div class="card page-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-lotteria">Kiểm kê bếp</h5>
                    <p class="text-muted mb-0">Duyệt đối soát cuối ngày, theo dõi hàng hủy sinh ra từ vận hành bếp.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4">
        <a href="{{ route('quanly.khochinh.duyet') }}" class="text-decoration-none">
            <div class="card page-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-lotteria">Kiểm kho chính</h5>
                    <p class="text-muted mb-0">Duyệt phiếu kiểm kê định kỳ, hiệu chỉnh chênh lệch và xử lý giải trình.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4">
        <a href="{{ route('cht.khochinh.thongke') }}" class="text-decoration-none">
            <div class="card page-card h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-lotteria">Thống kê tồn kho</h5>
                    <p class="text-muted mb-0">Xem lịch sử các phiếu định kỳ đã duyệt và số liệu sau đối soát.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card page-card">
    <div class="card-header bg-white border-0 pb-0">
        <h5 class="fw-bold mb-1">Phiếu kiểm kê gần đây</h5>
        <p class="text-muted small mb-0">Giữ nguyên route `kiem-ke.index` nhưng thay bằng trang tổng hợp có dữ liệu gần nhất.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Loại kiểm kê</th>
                        <th>Ngày kiểm</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPhieuKiemKe as $phieu)
                        <tr>
                            <td class="fw-bold">{{ $phieu->MaPhieuKiemKe }}</td>
                            <td>
                                <span class="badge {{ $typeClasses[$phieu->LoaiKiemKe] ?? 'bg-secondary' }}">
                                    {{ $phieu->LoaiKiemKe ?: 'Chưa rõ' }}
                                </span>
                            </td>
                            <td>{{ $phieu->NgayKiemKe ? \Carbon\Carbon::parse($phieu->NgayKiemKe)->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge {{ $statusClasses[$phieu->TrangThai] ?? 'bg-secondary' }}">
                                    {{ $phieu->TrangThai ?: 'Chưa rõ' }}
                                </span>
                            </td>
                            <td>{{ $phieu->GhiChu ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                Chưa có phiếu kiểm kê nào để hiển thị.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
