@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="text-lotteria fw-bold mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-box-seam me-2 align-text-bottom" viewBox="0 0 16 16">
                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.84L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                </svg>
                Tiếp Nhận Phiếu: {{ $phieuXuat->MaPhieuXuat }}
            </h3>
            <p class="text-muted mb-0">
                <small>📅 Ngày xuất: {{ \Carbon\Carbon::parse($phieuXuat->NgayXuat)->format('d/m/Y') }} | 👤 Người tạo: {{ $phieuXuat->MaTaiKhoan }}</small>
            </p>
        </div>
        <a href="{{ route('nhanvien.phieuxuat') }}" class="btn btn-outline-secondary mt-3 mt-md-0 shadow-sm">
            &larr; Quay lại danh sách
        </a>
    </div>

    <form action="{{ route('nhanvien.hoantat', $phieuXuat->MaPhieuXuat) }}" method="POST">
        @csrf
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-lotteria text-white py-2">
                <h6 class="mb-0 fw-bold">Danh Sách Nguyên Liệu Cần Lấy Tại Kệ</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-lotteria border-bottom">
                        <tr>
                            <th class="ps-3">MÃ LÔ</th>
                            <th>MÃ NL</th>
                            <th>TÊN NGUYÊN LIỆU</th>
                            <th class="text-center">YÊU CẦU</th>
                            <th width="180" class="text-center bg-light">THỰC LẤY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chiTietPhieu as $ct)
                        <tr>
                            <td class="fw-bold ps-3">{{ $ct->MaLoHang }}</td>
                            <td>{{ $ct->MaNguyenLieu }}</td>
                            <td class="fw-bold text-lotteria">{{ $ct->TenNguyenLieu }}</td>
                            <td class="text-center text-primary fw-bold fs-5">
                                {{ $ct->SoLuongXuat }} <span class="fs-6 text-muted">{{ $ct->DonViTinh }}</span>
                            </td>
                            <td class="bg-light">
                                <div class="input-group input-group-sm">
                                    <input type="number"
                                           name="thuc_lay[{{ $ct->MaLoHang }}]"
                                           class="form-control text-center fw-bold text-success border-success"
                                           min="0"
                                           max="{{ $ct->SoLuongXuat }}"
                                           value="{{ $ct->SoLuongXuat }}">
                                    <span class="input-group-text bg-white text-muted">{{ $ct->DonViTinh }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary">{{ $item->DonViTinh }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white text-end py-3 border-top-0">
                <button type="submit" class="btn btn-lotteria px-5 py-2 fw-bold shadow-sm">
                    ✓ Xác Nhận Lấy Hàng Xong
                </button>
            </div>
        </div>
    </form>
</div>
@endsection