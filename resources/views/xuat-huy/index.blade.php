@extends('layouts.app')

@section('title', 'Xuất hủy')

@php
    $statusClasses = [
        'Chờ duyệt' => 'bg-warning text-dark',
        'Đã duyệt' => 'bg-success',
        'Đã hủy' => 'bg-secondary',
        'Từ chối' => 'bg-danger',
    ];
    $currentUser = auth()->user();
    $isStoreChief = $currentUser && $currentUser->VaiTro === 'Cửa hàng trưởng';
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Phiếu xuất hủy</h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tổng phiếu hủy</div>
                <div class="display-6 fw-bold text-lotteria">{{ $summary['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Chờ duyệt</div>
                <div class="display-6 fw-bold text-warning">{{ $summary['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Đã duyệt</div>
                <div class="display-6 fw-bold text-success">{{ $summary['approved'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small mb-2">Tổng SL hủy</div>
                <div class="display-6 fw-bold text-danger">{{ $summary['total_quantity'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card page-card">
    <div class="card-header bg-white border-0 pb-0">
        <h5 class="fw-bold mb-1">Danh sách phiếu xuất hủy</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Ngày tạo</th>
                        <th>Người tạo</th>
                        <th>Phiếu kiểm kê</th>
                        <th>Loại kiểm kê</th>
                        <th>Số NL</th>
                        <th>Tổng SL hủy</th>
                        <th>Trạng thái</th>
                        <th>Lý do hủy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPhieuHuy as $phieu)
                        <tr>
                            <td class="fw-bold">{{ $phieu->MaPhieuHuy }}</td>
                            <td>{{ $phieu->NgayTao ? \Carbon\Carbon::parse($phieu->NgayTao)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $phieu->NguoiTao ?? '-' }}</td>
                            <td>{{ $phieu->MaPhieuKiemKe ?: '-' }}</td>
                            <td>{{ $phieu->LoaiKiemKe ?? '-' }}</td>
                            <td>{{ $phieu->SoDongNguyenLieu ?? 0 }}</td>
                            <td>{{ $phieu->TongSoLuongHuy ?? 0 }}</td>
                            <td>
                                <span class="badge {{ $statusClasses[$phieu->TrangThai] ?? 'bg-secondary' }}">
                                    {{ $phieu->TrangThai ?: 'Chưa rõ' }}
                                </span>
                            </td>
                            <td>{{ $phieu->LyDoHuy ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                Chưa có dữ liệu phiếu xuất hủy để hiển thị.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
