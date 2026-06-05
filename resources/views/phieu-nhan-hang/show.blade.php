@extends('layouts.nhap-kho')

@section('title', 'Chi tiết Phiếu Nhận – ' . $phieuNhan->MaPhieuNhan)

@section('content')

{{-- Step Indicator --}}
@php
    $steps = [
        'Chờ nhận hàng'       => 1,
        'Đang xử lý đổi/trả'  => 2,
        'Chờ xử lý'           => 2,
        'Đã nhận hàng'        => 3,
        'Hoàn tất'            => 4,
    ];
    $curStep = $steps[$phieuNhan->TrangThai] ?? 1;
@endphp
<div class="steps">
    @foreach([1=>'Chờ nhận hàng', 2=>'Kiểm đếm / Đổi-Trả', 3=>'Đã nhận đủ', 4=>'Hoàn tất'] as $s=>$label)
    <div class="step {{ $curStep == $s ? 'active' : ($curStep > $s ? 'done' : '') }}">
        <div class="step-circle">{{ $curStep > $s ? '✔' : $s }}</div>
        <div class="step-label">{{ $label }}</div>
    </div>
    @endforeach
</div>

<div class="page-header">
    <div>
        <h1>📋 Phiếu nhận hàng – {{ $phieuNhan->MaPhieuNhan }}</h1>
        <div class="subtitle">Đơn đặt hàng: {{ $phieuNhan->MaDonDatHang }}</div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <x-status-badge :status="$phieuNhan->TrangThai" />
        <a href="{{ route('phieu-nhan-hang.index') }}" class="btn btn-secondary btn-sm">← Quay lại</a>
    </div>
</div>

{{-- Thông tin tổng quan --}}
<div class="card">
    <div class="card-header"><h3>ℹ Thông tin phiếu</h3></div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item"><label>Mã phiếu nhận</label><span>{{ $phieuNhan->MaPhieuNhan }}</span></div>
            <div class="info-item"><label>Đơn đặt hàng</label><span>{{ $phieuNhan->MaDonDatHang }}</span></div>
            <div class="info-item"><label>Ngày nhận</label><span>{{ \Carbon\Carbon::parse($phieuNhan->NgayNhan)->format('d/m/Y') }}</span></div>
            <div class="info-item"><label>Nhân viên nhận</label><span>{{ $phieuNhan->taiKhoan->HoTen ?? '–' }}</span></div>
            <div class="info-item"><label>Trạng thái</label><span><x-status-badge :status="$phieuNhan->TrangThai" /></span></div>
            <div class="info-item"><label>Ghi chú</label><span>{{ $phieuNhan->GhiChu ?? '–' }}</span></div>
        </div>
    </div>
</div>

{{-- Bảng đối chiếu số lượng đặt vs nhận --}}
<div class="card">
    <div class="card-header">
        <h3>📊 Đối chiếu số lượng đặt – thực nhận</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nguyên liệu</th>
                    <th>Đơn vị</th>
                    <th style="text-align:center">SL Đặt</th>
                    <th style="text-align:center">SL Thực nhận</th>
                    <th style="text-align:center">Chênh lệch</th>
                    <th>NSX / HSD</th>
                    <th>Tình trạng lô</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chiTietDon as $ct)
                @php
                    $loHangs     = $loHangTheoNL->get($ct->MaNguyenLieu, collect());
                    $slNhan      = $loHangs->sum('SoLuongNhap');
                    $chenhLech   = $slNhan - $ct->SoLuongDat;
                    $coSaiLech   = $chenhLech !== 0;
                @endphp
                <tr class="{{ $coSaiLech ? 'row-mismatch' : '' }}">
                    <td><strong>{{ $ct->nguyenLieu->TenNguyenLieu ?? $ct->MaNguyenLieu }}</strong></td>
                    <td>{{ $ct->nguyenLieu->DonViTinh ?? '' }}</td>
                    <td style="text-align:center">{{ $ct->SoLuongDat }}</td>
                    <td style="text-align:center">
                        {{ $slNhan > 0 ? $slNhan : '–' }}
                    </td>
                    <td style="text-align:center">
                        @if($slNhan === 0)
                            <span class="text-muted">–</span>
                        @elseif($chenhLech === 0)
                            <span class="text-success">✔ Khớp</span>
                        @elseif($chenhLech < 0)
                            <span class="text-danger">{{ $chenhLech }} (thiếu)</span>
                        @else
                            <span class="text-warning">+{{ $chenhLech }} (thừa)</span>
                        @endif
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <div style="font-size:12px; line-height:1.8">
                                NSX: {{ \Carbon\Carbon::parse($lh->NgaySanXuat)->format('d/m/Y') }}
                                – HSD: {{ \Carbon\Carbon::parse($lh->HanSuDung)->format('d/m/Y') }}
                            </div>
                        @endforeach
                        @if($loHangs->isEmpty()) <span class="text-muted">–</span> @endif
                    </td>
                    <td>
                        @foreach($loHangs as $lh)
                            <x-status-badge :status="$lh->TrangThai" />
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Phiếu đổi/trả liên quan --}}
@if($phieuNhan->phieuDoiTras->isNotEmpty())
<div class="card">
    <div class="card-header"><h3>🔄 Phiếu Đổi/Trả liên quan</h3></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Mã phiếu ĐT</th><th>Ngày tạo</th><th>Loại xử lý</th><th>Lý do</th><th>Trạng thái</th><th>Xem</th></tr>
            </thead>
            <tbody>
                @foreach($phieuNhan->phieuDoiTras as $dt)
                <tr>
                    <td>{{ $dt->MaPhieuDoiTra }}</td>
                    <td>{{ \Carbon\Carbon::parse($dt->NgayTao)->format('d/m/Y') }}</td>
                    <td><x-status-badge :status="$dt->LoaiXuLy" /></td>
                    <td>{{ $dt->LyDo }}</td>
                    <td><x-status-badge :status="$dt->TrangThaiXuLy" /></td>
                    <td><a href="{{ route('phieu-doi-tra.show', $dt->MaPhieuDoiTra) }}" class="btn btn-outline btn-sm">👁</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Action Buttons --}}
<div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
    @if(in_array($phieuNhan->TrangThai, ['Chờ nhận hàng','Đang xử lý đổi/trả']))
        <a href="{{ route('phieu-nhan-hang.nhap-so-luong', $phieuNhan->MaPhieuNhan) }}"
           class="btn btn-primary btn-lg">✏ Nhập số lượng thực nhận</a>
    @endif

    @if($phieuNhan->TrangThai === 'Chờ xử lý')
        <a href="{{ route('phieu-doi-tra.create', $phieuNhan->MaPhieuNhan) }}"
           class="btn btn-warning btn-lg">🔄 Tạo yêu cầu Đổi/Trả</a>
    @endif

    @if($phieuNhan->TrangThai === 'Đã nhận hàng')
        <a href="{{ route('phieu-nhap-kho.create', $phieuNhan->MaPhieuNhan) }}"
           class="btn btn-success btn-lg">📥 Xác nhận Nhập kho</a>
    @endif

    <a href="{{ route('phieu-nhan-hang.index') }}" class="btn btn-secondary btn-lg">← Quay lại danh sách</a>
</div>

@endsection
