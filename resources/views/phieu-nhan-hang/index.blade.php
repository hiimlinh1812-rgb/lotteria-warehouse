@extends('layouts.nhap-kho')

@section('title', 'Danh sách Phiếu Nhận Hàng')

@section('content')
<div class="page-header">
    <div>
        <h1>📦 Tiếp nhận Hàng Hóa</h1>
        <div class="subtitle">Danh sách phiếu nhận hàng từ kho tổng</div>
    </div>
</div>

{{-- Bộ lọc --}}
<form method="GET" action="{{ route('phieu-nhan-hang.index') }}" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Tìm theo mã phiếu</label>
        <input type="text" name="search" class="form-control"
               placeholder="VD: PN001" value="{{ request('search') }}">
    </div>
    <div class="form-group">
        <label class="form-label">Trạng thái</label>
        <select name="trang_thai" class="form-control">
            <option value="">-- Tất cả --</option>
            <option value="Chờ nhận hàng"       @selected(request('trang_thai')==='Chờ nhận hàng')>Chờ nhận hàng</option>
            <option value="Đã nhận hàng"         @selected(request('trang_thai')==='Đã nhận hàng')>Đã nhận hàng</option>
            <option value="Chờ xử lý"            @selected(request('trang_thai')==='Chờ xử lý')>Chờ xử lý</option>
            <option value="Đang xử lý đổi/trả"   @selected(request('trang_thai')==='Đang xử lý đổi/trả')>Đang xử lý đổi/trả</option>
            <option value="Hoàn tất"             @selected(request('trang_thai')==='Hoàn tất')>Hoàn tất</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">🔍 Lọc</button>
    <a href="{{ route('phieu-nhan-hang.index') }}" class="btn btn-secondary">✖ Xóa lọc</a>
</form>

<div class="card">
    <div class="card-header">
        <h3>📋 Danh sách phiếu nhận hàng</h3>
        <span class="badge badge-gray">{{ $phieuNhanHangs->total() }} phiếu</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Đơn đặt hàng</th>
                    <th>Ngày nhận</th>
                    <th>Người nhận</th>
                    <th>Trạng thái</th>
                    <th style="text-align:center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieuNhanHangs as $pnh)
                <tr>
                    <td><strong>{{ $pnh->MaPhieuNhan }}</strong></td>
                    <td>{{ $pnh->MaDonDatHang }}</td>
                    <td>{{ \Carbon\Carbon::parse($pnh->NgayNhan)->format('d/m/Y') }}</td>
                    <td>{{ $pnh->taiKhoan->HoTen ?? '–' }}</td>
                    <td><x-status-badge :status="$pnh->TrangThai" /></td>
                    <td style="text-align:center">
                        <a href="{{ route('phieu-nhan-hang.show', $pnh->MaPhieuNhan) }}"
                           class="btn btn-outline btn-sm">👁 Xem</a>

                        @if(in_array($pnh->TrangThai, ['Chờ nhận hàng','Đang xử lý đổi/trả']))
                            <a href="{{ route('phieu-nhan-hang.nhap-so-luong', $pnh->MaPhieuNhan) }}"
                               class="btn btn-primary btn-sm">✏ Nhập SL</a>
                        @endif

                        @if($pnh->TrangThai === 'Chờ xử lý')
                            <a href="{{ route('phieu-doi-tra.create', $pnh->MaPhieuNhan) }}"
                               class="btn btn-warning btn-sm">🔄 Đổi/Trả</a>
                        @endif

                        @if($pnh->TrangThai === 'Đã nhận hàng')
                            <a href="{{ route('phieu-nhap-kho.create', $pnh->MaPhieuNhan) }}"
                               class="btn btn-success btn-sm">📥 Nhập kho</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted)">
                        Không có phiếu nhận hàng nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $phieuNhanHangs->withQueryString()->links() }}
    </div>
</div>
@endsection
