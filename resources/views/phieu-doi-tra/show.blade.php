@extends('layouts.nhap-kho')

@section('title', 'Chi tiết Phiếu Đổi/Trả – ' . $phieuDoiTra->MaPhieuDoiTra)

@section('content')

<div class="page-header">
    <div>
        <h1>🔄 Phiếu Đổi/Trả – {{ $phieuDoiTra->MaPhieuDoiTra }}</h1>
        <div class="subtitle">Phiếu nhận: {{ $phieuDoiTra->MaPhieuNhan }}</div>
    </div>
    <div style="display:flex; gap:10px; align-items:center">
        <x-status-badge :status="$phieuDoiTra->TrangThaiXuLy" />
        <a href="{{ route('phieu-doi-tra.index') }}" class="btn btn-secondary btn-sm">← Quay lại</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>ℹ Thông tin phiếu</h3></div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item"><label>Mã phiếu ĐT</label><span>{{ $phieuDoiTra->MaPhieuDoiTra }}</span></div>
            <div class="info-item"><label>Phiếu nhận hàng</label><span>{{ $phieuDoiTra->MaPhieuNhan }}</span></div>
            <div class="info-item"><label>Đơn đặt hàng</label><span>{{ $phieuDoiTra->phieuNhanHang->MaDonDatHang ?? '–' }}</span></div>
            <div class="info-item"><label>Ngày tạo</label><span>{{ \Carbon\Carbon::parse($phieuDoiTra->NgayTao)->format('d/m/Y') }}</span></div>
            <div class="info-item"><label>Loại xử lý</label><span><x-status-badge :status="$phieuDoiTra->LoaiXuLy" /></span></div>
            <div class="info-item"><label>Trạng thái</label><span><x-status-badge :status="$phieuDoiTra->TrangThaiXuLy" /></span></div>
            <div class="info-item"><label>Người lập</label><span>{{ $phieuDoiTra->taiKhoan->HoTen ?? '–' }}</span></div>
            <div class="info-item"><label>Lý do</label><span>{{ $phieuDoiTra->LyDo }}</span></div>
        </div>
    </div>
</div>

{{-- Lô hàng liên quan --}}
@if($phieuDoiTra->loHangs->isNotEmpty())
<div class="card">
    <div class="card-header"><h3>📦 Lô hàng liên quan</h3></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã lô</th>
                    <th>Nguyên liệu</th>
                    <th style="text-align:center">SL Nhập</th>
                    <th style="text-align:center">SL Còn lại</th>
                    <th>NSX</th>
                    <th>HSD</th>
                    <th>Tình trạng</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phieuDoiTra->loHangs as $lh)
                <tr>
                    <td>{{ $lh->MaLoHang }}</td>
                    <td>{{ $lh->nguyenLieu->TenNguyenLieu ?? $lh->MaNguyenLieu }}</td>
                    <td style="text-align:center">{{ $lh->SoLuongNhap }}</td>
                    <td style="text-align:center">{{ $lh->SoLuongConLai }}</td>
                    <td>{{ \Carbon\Carbon::parse($lh->NgaySanXuat)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($lh->HanSuDung)->format('d/m/Y') }}</td>
                    <td><x-status-badge :status="$lh->TrangThai" /></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Action --}}
@if($phieuDoiTra->TrangThaiXuLy === 'Đang xử lý')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info" style="margin-bottom:16px">
            <span>ℹ</span>
            Sau khi kho tổng đã xử lý xong yêu cầu đổi/trả, nhấn nút bên dưới để cho phép nhân viên nhập lại số lượng thực nhận.
        </div>
        <form method="POST"
              action="{{ route('phieu-doi-tra.cap-nhat-xu-ly', $phieuDoiTra->MaPhieuDoiTra) }}"
              onsubmit="return confirm('Xác nhận kho tổng đã hoàn thành xử lý đổi/trả?')">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">✔ Xác nhận đã xử lý xong</button>
        </form>
    </div>
</div>
@endif

<div style="margin-top:8px">
    <a href="{{ route('phieu-nhan-hang.show', $phieuDoiTra->MaPhieuNhan) }}"
       class="btn btn-outline">📋 Xem phiếu nhận hàng</a>
    <a href="{{ route('phieu-doi-tra.index') }}" class="btn btn-secondary" style="margin-left:10px">← Quay lại danh sách</a>
</div>

@endsection
