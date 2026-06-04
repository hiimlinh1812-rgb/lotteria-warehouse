@extends('layouts.nhap-kho')

@section('title', 'Danh sách Phiếu Đổi/Trả')

@section('content')

<div class="page-header">
    <div>
        <h1>🔄 Quản lý Đổi / Trả Hàng</h1>
        <div class="subtitle">Danh sách yêu cầu đổi trả hàng từ kho tổng</div>
    </div>
</div>

<form method="GET" action="{{ route('phieu-doi-tra.index') }}" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Tìm mã phiếu</label>
        <input type="text" name="search" class="form-control"
               placeholder="VD: DT001" value="{{ request('search') }}">
    </div>
    <div class="form-group">
        <label class="form-label">Trạng thái xử lý</label>
        <select name="trang_thai" class="form-control">
            <option value="">-- Tất cả --</option>
            <option value="Đang xử lý" @selected(request('trang_thai')==='Đang xử lý')>Đang xử lý</option>
            <option value="Đã xử lý"   @selected(request('trang_thai')==='Đã xử lý')>Đã xử lý</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">🔍 Lọc</button>
    <a href="{{ route('phieu-doi-tra.index') }}" class="btn btn-secondary">✖ Xóa</a>
</form>

<div class="card">
    <div class="card-header">
        <h3>📋 Danh sách phiếu đổi/trả</h3>
        <span class="badge badge-gray">{{ $phieuDoiTras->total() }} phiếu</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã phiếu ĐT</th>
                    <th>Phiếu nhận</th>
                    <th>Ngày tạo</th>
                    <th>Loại xử lý</th>
                    <th>Lý do</th>
                    <th>Người lập</th>
                    <th>Trạng thái</th>
                    <th style="text-align:center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieuDoiTras as $dt)
                <tr>
                    <td><strong>{{ $dt->MaPhieuDoiTra }}</strong></td>
                    <td>{{ $dt->MaPhieuNhan }}</td>
                    <td>{{ \Carbon\Carbon::parse($dt->NgayTao)->format('d/m/Y') }}</td>
                    <td><x-status-badge :status="$dt->LoaiXuLy" /></td>
                    <td>{{ \Str::limit($dt->LyDo, 40) }}</td>
                    <td>{{ $dt->taiKhoan->HoTen ?? '–' }}</td>
                    <td><x-status-badge :status="$dt->TrangThaiXuLy" /></td>
                    <td style="text-align:center">
                        <a href="{{ route('phieu-doi-tra.show', $dt->MaPhieuDoiTra) }}"
                           class="btn btn-outline btn-sm">👁 Xem</a>
                        @if($dt->TrangThaiXuLy === 'Đang xử lý')
                            <form method="POST"
                                  action="{{ route('phieu-doi-tra.cap-nhat-xu-ly', $dt->MaPhieuDoiTra) }}"
                                  style="display:inline"
                                  onsubmit="return confirm('Xác nhận kho tổng đã xử lý xong?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">✔ Đã xử lý</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted)">
                        Không có phiếu đổi/trả nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $phieuDoiTras->withQueryString()->links() }}</div>
</div>
@endsection
