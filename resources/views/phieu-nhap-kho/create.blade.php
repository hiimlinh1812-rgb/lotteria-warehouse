@extends('layouts.nhap-kho')

@section('title', 'Xác nhận Nhập Kho')

@section('content')

<div class="page-header">
    <div>
        <h1>📥 Xác nhận Nhập Kho</h1>
        <div class="subtitle">Phiếu nhận: {{ $phieuNhan->MaPhieuNhan }} | Đơn: {{ $phieuNhan->MaDonDatHang }}</div>
    </div>
    <a href="{{ route('phieu-nhan-hang.show', $phieuNhan->MaPhieuNhan) }}" class="btn btn-secondary">← Quay lại</a>
</div>

<div class="alert alert-info">
    <span>ℹ</span>
    Quản lý kiểm tra thông tin và xác nhận nhập kho. Sau khi xác nhận,
    <strong>tồn kho nguyên liệu sẽ được cập nhật tự động.</strong>
</div>

{{-- Tóm tắt --}}
<div class="card">
    <div class="card-header"><h3>ℹ Thông tin nhập kho</h3></div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item"><label>Phiếu nhận hàng</label><span>{{ $phieuNhan->MaPhieuNhan }}</span></div>
            <div class="info-item"><label>Đơn đặt hàng</label><span>{{ $phieuNhan->MaDonDatHang }}</span></div>
            <div class="info-item"><label>Ngày nhận hàng</label><span>{{ \Carbon\Carbon::parse($phieuNhan->NgayNhan)->format('d/m/Y') }}</span></div>
            <div class="info-item"><label>Ngày nhập kho</label><span>{{ now()->format('d/m/Y') }}</span></div>
            <div class="info-item"><label>Tổng mặt hàng</label><span>{{ $phieuNhan->loHangs->count() }} lô</span></div>
        </div>
    </div>
</div>

{{-- Chi tiết lô hàng sẽ nhập --}}
<div class="card">
    <div class="card-header"><h3>📦 Chi tiết lô hàng sẽ nhập vào kho</h3></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nguyên liệu</th>
                    <th>Nhóm</th>
                    <th style="text-align:center">Tổng SL nhập</th>
                    <th>NSX (lô)</th>
                    <th>HSD (lô)</th>
                    <th>Tình trạng</th>
                    <th style="text-align:center">Tồn kho hiện tại</th>
                    <th style="text-align:center">Tồn kho sau nhập</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loHangTheoNL as $maNL => $loHangs)
                @php
                    $nguyenLieu = $loHangs->first()->nguyenLieu;
                    $slNhap     = $loHangs->sum('SoLuongNhap');
                    $tonHienTai = $nguyenLieu->SoLuongTonKho ?? 0;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $nguyenLieu->TenNguyenLieu ?? $maNL }}</strong>
                        <div style="font-size:11.5px;color:var(--text-muted)">{{ $nguyenLieu->DonViTinh ?? '' }}</div>
                    </td>
                    <td>{{ $nguyenLieu->NhomHang ?? '' }}</td>
                    <td style="text-align:center">
                        <strong style="font-size:16px; color:var(--lotteria-red)">{{ $slNhap }}</strong>
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <div style="font-size:12px">{{ \Carbon\Carbon::parse($lh->NgaySanXuat)->format('d/m/Y') }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <div style="font-size:12px">{{ \Carbon\Carbon::parse($lh->HanSuDung)->format('d/m/Y') }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <x-status-badge :status="$lh->TrangThai" />
                        @endforeach
                    </td>
                    <td style="text-align:center; font-weight:600">{{ $tonHienTai }}</td>
                    <td style="text-align:center; font-weight:700; color:var(--success)">
                        {{ $tonHienTai + $slNhap }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Form xác nhận --}}
<div class="card">
    <div class="card-header"><h3>✔ Xác nhận nhập kho</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('phieu-nhap-kho.store', $phieuNhan->MaPhieuNhan) }}"
              onsubmit="return confirm('Xác nhận nhập kho? Tồn kho sẽ được cập nhật ngay.')">
            @csrf
            <div class="form-group" style="max-width:500px">
                <label class="form-label">Ghi chú (tùy chọn)</label>
                <input type="text" name="ghi_chu" class="form-control"
                       placeholder="Nhập ghi chú nếu cần..."
                       value="{{ old('ghi_chu') }}">
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px">
                <button type="submit" class="btn btn-success btn-lg">✔ Xác nhận Nhập Kho</button>
                <a href="{{ route('phieu-nhan-hang.show', $phieuNhan->MaPhieuNhan) }}"
                   class="btn btn-secondary btn-lg">✖ Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
