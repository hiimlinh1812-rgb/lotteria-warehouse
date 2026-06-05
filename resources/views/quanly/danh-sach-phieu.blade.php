@extends('layouts.app')

@section('content')
<div class="container mt-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3">
        <div class="mb-3 mb-md-0">
            <h3 class="text-lotteria fw-bold mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-list-task me-2 align-text-bottom" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5H2zM3 3H2v1h1V3z"/>
                    <path d="M5 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM5.5 7a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 4a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9z"/>
                    <path fill-rule="evenodd" d="M1.5 7a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5V7zM2 7h1v1H2V7zm0 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5H2zm1 .5H2v1h1v-1z"/>
                </svg>
                Danh Sách Phiếu Xuất Kho
            </h3>
        </div>
        
        <div>
            <a href="{{ route('xuatkho.create') }}" class="btn btn-lotteria px-4 py-2 shadow-sm fw-bold">
                + Tạo Phiếu Mới
            </a>
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
            <h6 class="mb-0 fw-bold">Tất Cả Phiếu</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-lotteria">
                    <tr>
                        <th class="ps-3">MÃ PHIẾU</th>
                        <th>NGÀY XUẤT</th>
                        <th>NGƯỜI TẠO</th>
                        <th>TRẠNG THÁI</th>
                        <th width="120" class="text-center">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachPhieu as $phieu)
                    <tr>
                        <td class="fw-bold text-dark ps-3">{{ $phieu->MaPhieuXuat }}</td>
                        <td>{{ \Carbon\Carbon::parse($phieu->NgayXuat)->format('d/m/Y') }}</td>
                        <td>{{ $phieu->MaTaiKhoan }}</td>
                        <td>
                            @if($phieu->TrangThai == 'Chờ xuất hàng' || $phieu->TrangThai == 'Chờ xuất')
                                <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm">{{ $phieu->TrangThai }}</span>
                            @elseif($phieu->TrangThai == 'Hoàn tất')
                                <span class="badge bg-secondary text-white px-3 py-2 rounded-pill shadow-sm">{{ $phieu->TrangThai }}</span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">{{ $phieu->TrangThai }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('quanly.chitiet', $phieu->MaPhieuXuat) }}" class="btn btn-sm btn-outline-secondary px-3">Xem</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-inbox mb-3 opacity-50" viewBox="0 0 16 16">
                                <path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4H4.98zm-1.17-.437A1.5 1.5 0 0 1 4.98 3h6.04a1.5 1.5 0 0 1 1.17.563l3.7 4.625a.5.5 0 0 1 .106.374l-.39 3.124A1.5 1.5 0 0 1 14.117 13H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .106-.374l3.7-4.625zM.125 8.67l.39 3.124a.5.5 0 0 0 .496.438h12.238a.5.5 0 0 0 .495-.438l.39-3.124H10.5a.5.5 0 0 1-.5.5 2.5 2.5 0 0 1-5 0 .5.5 0 0 1-.5-.5H.125z"/>
                            </svg>
                            <h6 class="fw-bold">Chưa có phiếu xuất kho nào.</h6>
                            <p class="small">Hãy tạo phiếu mới để bắt đầu.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection