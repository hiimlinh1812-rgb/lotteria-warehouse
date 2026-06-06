@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('nhanvien.phieuxuat') }}" class="text-decoration-none text-secondary small">&larr; Quay lại danh sách</a>
    </div>

    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">📋 CHI TIẾT PHIẾU XUẤT KHO: <span class="text-warning font-monospace">{{ $phieuXuat->MaPhieuXuat }}</span></h5>
            <span class="badge bg-danger px-3 py-2 fw-bold">{{ $phieuXuat->TrangThai }}</span>
        </div>
        
        <div class="card-body bg-light border-bottom p-3">
            <div class="row small text-secondary">
                <div class="col-md-6">📅 <strong>Ngày khởi tạo:</strong> {{ \Carbon\Carbon::parse($phieuXuat->NgayXuat)->format('d/m/Y') }}</div>
                <div class="col-md-6 text-md-end">👤 <strong>Quản lý yêu cầu:</strong> {{ $phieuXuat->MaTaiKhoan }}</div>
            </div>
        </div>

        <form action="{{ route('nhanvien.hoantat', $phieuXuat->MaPhieuXuat) }}" method="POST">
            @csrf
            <div class="card-body p-0">
                <table class="table table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Nguyên Liệu</th>
                            <th>Tên Nguyên Liệu</th>
                            <th>Mã Lô </th>
                            <th style="width: 20%;">Số Lượng Yêu Cầu</th>
                            <th style="width: 20%;">Số Lượng Thực Lấy</th>
                            <th>Đơn Vị</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chiTietPhieu as $item)
                        <tr>
                            <td class="font-monospace fw-bold small">{{ $item->MaNguyenLieu }}</td>
                            <td class="text-start text-dark fw-bold">{{ $item->TenNguyenLieu }}</td>
                            <td class="font-monospace text-secondary fw-bold">{{ $item->MaLoHang }}</td>
                            <td class="fw-bold text-primary fs-5">{{ $item->SoLuongXuat }}</td>
                            <td>
                                <input type="number" name="thuc_lay[{{ $item->MaLoHang }}]" class="form-control form-control-sm text-center mx-auto fw-bold text-danger fs-5" style="width: 120px;" value="{{ $item->SoLuongXuat }}" min="0" required>
                            </td>
                            <td><span class="badge bg-secondary">{{ $item->DonViTinh }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($phieuXuat->TrangThai !== 'Hoàn tất')
            <div class="card-footer bg-white text-end p-3">
                <button type="submit" class="btn btn-danger fw-bold px-5 shadow-sm" onclick="return confirm('Xác nhận đã bốc đúng và đủ số lượng thực tế theo lô hệ thống chỉ định?')">
                    🚀 Xác Nhận Hoàn Tất Xuất Kho & Tự Động Trừ Tồn
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection