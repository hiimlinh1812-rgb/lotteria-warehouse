@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold text-dark">📋 HỒ SƠ DANH SÁCH PHIẾU GIẢI TRÌNH THẤT THOÁT</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
            Quay lại Dashboard
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold">
            ✓ {{ session('status') }}
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped text-center align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 15%;">Mã Giải Trình</th>
                        <th style="width: 15%;">Mã Phiếu Kiểm</th>
                        <th style="width: 15%;">Ngày Kiểm Ca</th>
                        <th style="width: 30%;">Nội Dung Giải Trình Chi Tiết</th>
                        <th style="width: 25%;">Nguyên Nhân Khách Quan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachGiaiTrinh as $gt)
                        <tr>
                            <td class="font-monospace fw-bold text-danger">{{ $gt->MaPhieuGiaiTrinh }}</td>
                            <td class="font-monospace fw-bold text-secondary">{{ $gt->MaPhieuKiemKe }}</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($gt->NgayKiemKe)->format('d/m/Y') }}</td>
                            <td class="text-start text-dark small fw-semibold">{{ $gt->NoiDung }}</td>
                            <td class="text-start text-muted small italic">📌 {{ $gt->NguyenNhan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-muted">
                                <span class="fs-1 d-block mb-3">📄</span>
                                <h6 class="fw-bold text-secondary">Hiện tại chưa có phiếu giải trình thất thoát nào được ghi nhận trên hệ thống!</h6>
                                <p class="small mb-0">Hồ sơ sẽ tự động nạp khi Quản lý thực hiện luồng đối soát chênh lệch vật lý.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            let alert = document.querySelector('.alert');
            if(alert) {
                alert.style.transition = "opacity 0.5s ease-out";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    });
</script>
@endsection