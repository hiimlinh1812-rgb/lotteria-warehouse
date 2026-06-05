@extends('layouts.nhap-kho')

@section('title', 'Chi tiết Phiếu Nhập Kho – ' . $phieuNhapKho->MaPhieuNhap)

@section('content')

<div class="page-header">
    <div>
        <h1>📥 Phiếu Nhập Kho – {{ $phieuNhapKho->MaPhieuNhap }}</h1>
        <div class="subtitle">Ngày nhập: {{ \Carbon\Carbon::parse($phieuNhapKho->NgayNhap)->format('d/m/Y') }}</div>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <x-status-badge :status="$phieuNhapKho->TrangThai" />
        <a href="{{ route('phieu-nhap-kho.bao-cao', $phieuNhapKho->MaPhieuNhap) }}"
           class="btn btn-outline btn-sm" target="_blank">🖨 In báo cáo</a>
        <a href="{{ route('phieu-nhap-kho.index') }}" class="btn btn-secondary btn-sm">← Quay lại</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>ℹ Thông tin phiếu nhập kho</h3></div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item"><label>Mã phiếu NK</label><span>{{ $phieuNhapKho->MaPhieuNhap }}</span></div>
            <div class="info-item"><label>Phiếu nhận hàng</label><span>{{ $phieuNhapKho->MaPhieuNhan }}</span></div>
            <div class="info-item"><label>Đơn đặt hàng</label>
                <span>{{ $phieuNhapKho->phieuNhanHang->MaDonDatHang ?? '–' }}</span>
            </div>
            <div class="info-item"><label>Ngày nhập kho</label>
                <span>{{ \Carbon\Carbon::parse($phieuNhapKho->NgayNhap)->format('d/m/Y') }}</span>
            </div>
            <div class="info-item"><label>Quản lý xác nhận</label>
                <span>{{ $phieuNhapKho->taiKhoan->HoTen ?? '–' }}</span>
            </div>
            <div class="info-item"><label>Trạng thái</label>
                <span><x-status-badge :status="$phieuNhapKho->TrangThai" /></span>
            </div>
            <div class="info-item"><label>Ghi chú</label><span>{{ $phieuNhapKho->GhiChu ?? '–' }}</span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>📦 Chi tiết nguyên liệu đã nhập</h3>
        <span class="badge badge-success">Tồn kho đã được cập nhật</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nguyên liệu</th>
                    <th>Nhóm hàng</th>
                    <th>Đơn vị</th>
                    <th style="text-align:center">SL đã nhập</th>
                    <th>NSX</th>
                    <th>HSD</th>
                    <th>Tình trạng lô</th>
                    <th>Mã lô</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loHangTheoNL as $maNL => $loHangs)
                @php
                    $nl = $loHangs->first()->nguyenLieu;
                    $slTong = $loHangs->sum('SoLuongNhap');
                @endphp
                <tr>
                    <td><strong>{{ $nl->TenNguyenLieu ?? $maNL }}</strong></td>
                    <td>{{ $nl->NhomHang ?? '–' }}</td>
                    <td>{{ $nl->DonViTinh ?? '' }}</td>
                    <td style="text-align:center; font-weight:700; color:var(--lotteria-red)">{{ $slTong }}</td>
                    <td>
                        @foreach($loHangs as $lh)
                            <div>{{ \Carbon\Carbon::parse($lh->NgaySanXuat)->format('d/m/Y') }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <div>{{ \Carbon\Carbon::parse($lh->HanSuDung)->format('d/m/Y') }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <x-status-badge :status="$lh->TrangThai" />
                        @endforeach
                    </td>
                    <td style="font-size:12px; color:var(--text-muted)">
                        @foreach($loHangs as $lh) <div>{{ $lh->MaLoHang }}</div> @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb; font-weight:700">
                    <td colspan="3" style="text-align:right; padding:12px 14px">Tổng số lượng đã nhập:</td>
                    <td style="text-align:center; font-size:18px; color:var(--lotteria-red)">
                        {{ $phieuNhapKho->loHangs->sum('SoLuongNhap') }}
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="alert alert-success" style="margin-top:8px">
    <span>✔</span>
    Phiếu nhập kho đã hoàn tất. Tồn kho nguyên liệu đã được cập nhật thành công vào hệ thống.
</div>

@endsection
