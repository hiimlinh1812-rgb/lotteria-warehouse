@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="text-lotteria fw-bold mb-1">
                Chi Tiết Phiếu Xuất: {{ $phieuXuat->MaPhieuXuat }}
            </h3>
            <p class="text-muted mb-0">
                <small>📅 Ngày xuất: {{ \Carbon\Carbon::parse($phieuXuat->NgayXuat)->format('d/m/Y') }} | 👤 Người tạo: {{ $phieuXuat->MaTaiKhoan }}</small>
            </p>
        </div>
        <a href="{{ route('xuatkho.index') }}" class="btn btn-outline-secondary mt-3 mt-md-0 shadow-sm">
            &larr; Quay lại danh sách
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-lotteria text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Thông Tin Chi Tiết</h6>
            <span class="badge bg-light text-lotteria px-3 py-1 rounded-pill">{{ $phieuXuat->TrangThai }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-lotteria border-bottom">
                    <tr>
                        <th class="ps-3">MÃ LÔ</th>
                        <th>MÃ NL</th>
                        <th>TÊN NGUYÊN LIỆU</th>
                        <th class="text-center">SỐ LƯỢNG YÊU CẦU</th>
                        <th class="text-center">SỐ LƯỢNG THỰC TẾ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chiTietPhieu as $ct)
                    <tr>
                        <td class="fw-bold ps-3">{{ $ct->MaLoHang }}</td>
                        <td>{{ $ct->MaNguyenLieu }}</td>
                        <td class="fw-bold text-dark">{{ $ct->TenNguyenLieu }}</td>
                        
                        <td class="text-center text-secondary fw-bold fs-5">
                            {{ $ct->SoLuongXuat }} <span class="fs-6 text-muted">{{ $ct->DonViTinh }}</span>
                        </td>
                        
                        <td class="text-center fw-bold fs-5">
                            @if($phieuXuat->TrangThai == 'Hoàn tất')
                                <span class="text-success">
                                    {{ $ct->SoLuongXuat }} <span class="fs-6 text-muted">{{ $ct->DonViTinh }}</span>
                                </span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-1 fs-6 rounded-pill fw-normal">
                                    <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true" style="width: 0.4rem; height: 0.4rem;"></span>
                                    Chờ lấy hàng...
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection