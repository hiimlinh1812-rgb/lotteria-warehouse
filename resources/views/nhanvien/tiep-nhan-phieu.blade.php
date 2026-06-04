@extends('layouts.app')

@section('content')
<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="text-lotteria fw-bold mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-box-seam me-2 align-text-bottom" viewBox="0 0 16 16">
                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.84L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                </svg>
                Tiếp Nhận Phiếu Xuất Kho
            </h3>
            <p class="text-muted mb-0">Danh sách các phiếu yêu cầu lấy hàng tại kệ</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Thành công!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-lotteria text-white py-2">
            <h6 class="mb-0 fw-bold">Danh sách phiếu đang chờ xử lý</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-3">MÃ PHIẾU</th>
                        <th>NGÀY YÊU CẦU</th>
                        <th>NGƯỜI TẠO</th>
                        <th>TRẠNG THÁI</th>
                        <th width="150" class="text-center">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachChoXuat as $phieu)
                    <tr>
                        <td class="fw-bold text-lotteria ps-3">{{ $phieu->MaPhieuXuat }}</td>
                        <td>{{ \Carbon\Carbon::parse($phieu->NgayXuat)->format('d/m/Y') }}</td>
                        <td>{{ $phieu->MaTaiKhoan }}</td>
                        <td>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">
                                <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true" style="width: 0.5rem; height: 0.5rem;"></span>
                                {{ $phieu->TrangThai }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('nhanvien.chitiet', $phieu->MaPhieuXuat) }}" class="btn btn-sm btn-outline-danger px-3 fw-bold">
                                Đi lấy hàng 
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <svg xmlns="..." width="60" height="60" fill="currentColor" class="bi bi-check2-circle mb-3 text-lotteria opacity-50" viewBox="0 0 16 16">
                                <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
                                <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
                            </svg>
                            <h6 class="fw-bold">Tuyệt vời! Không có phiếu nào đang chờ.</h6>
                            <p class="small">Khu vực kho hiện tại không có yêu cầu xuất hàng mới.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection