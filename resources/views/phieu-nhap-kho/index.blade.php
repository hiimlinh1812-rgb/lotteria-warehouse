@extends('layouts.nhap-kho')

@section('title', 'Danh sách Phiếu Nhập Kho')

@section('content')

<div class="page-header">
    <div>
        <h1>📥 Quản lý Nhập Kho</h1>
        <div class="subtitle">Lịch sử và trạng thái các phiếu nhập kho</div>
    </div>
</div>

<form method="GET" action="{{ route('phieu-nhap-kho.index') }}" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Tìm mã phiếu</label>
        <input type="text" name="search" class="form-control"
               placeholder="VD: PNK001" value="{{ request('search') }}">
    </div>
    <div class="form-group">
        <label class="form-label">Trạng thái</label>
        <select name="trang_thai" class="form-control">
            <option value="">-- Tất cả --</option>
            <option value="Chờ nhập" @selected(request('trang_thai')==='Chờ nhập')>Chờ nhập</option>
            <option value="Đã nhập"  @selected(request('trang_thai')==='Đã nhập')>Đã nhập</option>
            <option value="Hoàn tất" @selected(request('trang_thai')==='Hoàn tất')>Hoàn tất</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">🔍 Lọc</button>
    <a href="{{ route('phieu-nhap-kho.index') }}" class="btn btn-secondary">✖ Xóa</a>
</form>

<div class="card">
    <div class="card-header">
        <h3>📋 Danh sách phiếu nhập kho</h3>
        <span class="badge badge-gray">{{ $phieuNhapKhos->total() }} phiếu</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã phiếu NK</th>
                    <th>Phiếu nhận</th>
                    <th>Đơn đặt hàng</th>
                    <th>Ngày nhập</th>
                    <th>Người lập</th>
                    <th>Trạng thái</th>
                    <th style="text-align:center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieuNhapKhos as $pnk)
                <tr>
                    <td><strong>{{ $pnk->MaPhieuNhap }}</strong></td>
                    <td>{{ $pnk->MaPhieuNhan }}</td>
                    <td>{{ $pnk->phieuNhanHang->MaDonDatHang ?? '–' }}</td>
                    <td>{{ \Carbon\Carbon::parse($pnk->NgayNhap)->format('d/m/Y') }}</td>
                    <td>{{ $pnk->taiKhoan->HoTen ?? '–' }}</td>
                    <td><x-status-badge :status="$pnk->TrangThai" /></td>
                    <td style="text-align:center">
                        <a href="{{ route('phieu-nhap-kho.show', $pnk->MaPhieuNhap) }}"
                           class="btn btn-outline btn-sm">👁 Xem</a>
                        <a href="{{ route('phieu-nhap-kho.bao-cao', $pnk->MaPhieuNhap) }}"
                           class="btn btn-secondary btn-sm" target="_blank">🖨 In</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted)">
                        Chưa có phiếu nhập kho nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $phieuNhapKhos->withQueryString()->links() }}</div>
</div>
@endsection
