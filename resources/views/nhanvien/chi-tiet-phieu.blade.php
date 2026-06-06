@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="mb-4 border-bottom pb-3">
        <h3 class="text-lotteria fw-bold mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-box-seam me-2 align-text-bottom" viewBox="0 0 16 16">
                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.84L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
            </svg>
            Tiếp Nhận Phiếu Xuất Kho
        </h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-lotteria text-white py-2">
            <h6 class="mb-0 fw-bold">Danh sách phiếu đang chờ xử lý</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-lotteria border-bottom">
                        <tr>
                            <th class="ps-3">MÃ PHIẾU</th>
                            <th>NGÀY YÊU CẦU</th>
                            <th>NGƯỜI TẠO</th>
                            <th>TRẠNG THÁI</th>
                            <th class="text-center pe-3">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($danhSachChoXuat as $phieu)
                        <tr>
                            <td class="fw-bold ps-3">{{ $phieu->MaPhieuXuat }}</td>
                            <td>{{ \Carbon\Carbon::parse($phieu->NgayXuat)->format('d/m/Y') }}</td>
                            <td>{{ $phieu->MaTaiKhoan }}</td>
                            <td>
                                <span class="status-badge pending">
                                    {{ $phieu->TrangThai }}
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('nhanvien.phieuxuat.show', $phieu->MaPhieuXuat) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                    Xử lý ngay &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="55" height="55" fill="#d9534f" class="bi bi-check2-circle mb-3 opacity-50" viewBox="0 0 16 16">
                                    <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
                                    <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
                                </svg>
                                <h5 class="fw-bold text-secondary">Khu vực kho hiện tại không có yêu cầu xuất hàng mới.</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection