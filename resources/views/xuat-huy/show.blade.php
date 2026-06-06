@extends('layouts.app')

@section('title', 'Chi tiết phiếu xuất hủy')

@php
    $statusClasses = [
        'Chờ duyệt' => 'bg-warning text-dark',
        'Đã duyệt' => 'bg-success',
        'Đã hủy' => 'bg-secondary',
        'Từ chối' => 'bg-danger',
    ];
@endphp

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="text-lotteria fw-bold mb-1">
                Chi Tiết Phiếu Hủy: {{ $phieuHuy->MaPhieuHuy }}
            </h3>
            <p class="text-muted mb-0">
                <small>📅 Ngày tạo: {{ \Carbon\Carbon::parse($phieuHuy->NgayTao)->format('d/m/Y') }} | 👤 Người tạo: {{ $phieuHuy->MaTaiKhoan }}</small>
            </p>
        </div>
        <a href="{{ route('xuat-huy.index') }}" class="btn btn-outline-secondary shadow-sm">
            &larr; Quay lại danh sách
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Trạng thái:</strong> <span class="badge {{ $statusClasses[$phieuHuy->TrangThai] ?? 'bg-secondary' }}">{{ $phieuHuy->TrangThai }}</span></p>
                    <p class="mb-1"><strong>Đính kèm phiếu kiểm kê:</strong> <a href="#" class="text-decoration-none fw-bold">{{ $phieuHuy->MaPhieuKiemKe ?: 'Không có' }}</a></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Lý do hủy tổng quát:</strong> <span class="text-danger">{{ $phieuHuy->LyDoHuy ?: 'Không có ghi chú' }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-lotteria text-white py-2">
            <h6 class="mb-0 fw-bold">Danh Sách Nguyên Liệu Tiêu Hủy</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light text-lotteria border-bottom">
                    <tr>
                        <th class="ps-3 text-start">MÃ NL</th>
                        <th class="text-start">TÊN NGUYÊN LIỆU</th>
                        <th>ĐƠN VỊ</th>
                        <th class="text-danger">SỐ LƯỢNG HỦY</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chiTietHuy as $ct)
                    <tr>
                        <td class="fw-bold ps-3 text-start">{{ $ct->MaNguyenLieu }}</td>
                        <td class="fw-bold text-start">{{ $ct->TenNguyenLieu }}</td>
                        <td class="text-muted">{{ $ct->DonViTinh }}</td>
                        <td class="text-danger fw-bold fs-5">{{ $ct->SoLuongHuy }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Không có chi tiết nguyên liệu hủy cho phiếu này.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection