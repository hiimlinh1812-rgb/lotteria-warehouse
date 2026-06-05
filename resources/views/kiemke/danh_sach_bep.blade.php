@extends('layouts.app')

@section('title', 'Duyệt kiểm kê cuối ngày')

@php
    $statusClasses = [
        'Chờ duyệt' => 'bg-warning text-dark',
        'Từ chối' => 'bg-danger',
        'Đã duyệt' => 'bg-success',
    ];
@endphp

@section('content')
<style>
    .bep-review-shell {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .bep-review-card {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.35rem 1.2rem rgba(15, 23, 42, 0.12);
    }

    .bep-review-header {
        background: #252934;
        color: #fff;
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .bep-review-code {
        font-size: 1.05rem;
        font-weight: 800;
        color: #fff;
    }

    .bep-review-meta {
        color: rgba(255, 255, 255, 0.92);
    }

    .bep-review-table thead th {
        background: #f8fafc;
        text-align: center;
        vertical-align: middle;
        border-color: #d9dee7;
        font-weight: 700;
        white-space: nowrap;
    }

    .bep-review-table tbody td {
        vertical-align: middle;
        border-color: #e5e7eb;
    }

    .bep-review-table .system-col {
        background: #eef2ff;
        font-weight: 700;
        text-align: center;
        color: #2563eb;
    }

    .bep-review-table .count-col {
        background: #fff7d6;
        font-weight: 700;
        text-align: center;
    }

    .bep-review-table .number-col {
        text-align: center;
        font-weight: 600;
    }

    .variance-pos {
        color: #d97706;
        font-weight: 800;
    }

    .variance-neg {
        color: #dc2626;
        font-weight: 800;
    }

    .variance-zero {
        color: #15803d;
        font-weight: 800;
    }

    .waste-box {
        border: 2px solid #f1d96f;
        border-radius: 0.9rem;
        background: #fffdf2;
        padding: 1rem;
    }

    .waste-title {
        color: #c2410c;
        font-weight: 800;
        margin-bottom: 0.85rem;
    }

    .waste-table thead th {
        background: #fde2e2;
        border-color: #f1c9c9;
        font-weight: 700;
    }

    .review-actions {
        border-top: 1px solid #eceff3;
        padding: 1rem 1.25rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .review-action-row {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
    }

    .review-reject-form {
        flex: 1 1 560px;
    }

    .review-note {
        min-width: 320px;
    }

    .review-lock-msg {
        color: #b91c1c;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .review-lock-button,
    .review-lock-button:disabled {
        background: #198754;
        border-color: #198754;
        color: #fff;
        opacity: 1;
        cursor: not-allowed;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Duyệt báo cáo kiểm kê cuối ngày</h2>
    </div>
</div>

@if(empty($danhSachPhiu))
    <div class="card page-card">
        <div class="card-body text-center text-muted py-5">Chưa có báo cáo kiểm kê cuối ngày nào để hiển thị.</div>
    </div>
@else
    <div class="bep-review-shell">
        @foreach($danhSachPhiu as $phieu)
            <div class="card bep-review-card">
                <div class="bep-review-header">
                    <div>
                        <div class="bep-review-code">
                            Mã phiếu: <span class="text-warning">{{ $phieu['MaPhieuKiemKe'] }}</span>
                            <span class="fw-normal bep-review-meta">| Ngày lập: {{ \Carbon\Carbon::parse($phieu['NgayKiemKe'])->format('Y-m-d') }}</span>
                            @if(!empty($phieu['NhanVienLap']))
                                <span class="fw-normal bep-review-meta">| Nhân viên: {{ $phieu['NhanVienLap'] }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $statusClasses[$phieu['TrangThai']] ?? 'bg-secondary' }}">Trạng thái: {{ $phieu['TrangThai'] }}</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table bep-review-table mb-0">
                            <thead>
                                <tr>
                                    <th>Mã Nguyên Liệu</th>
                                    <th>Tên Nguyên Liệu</th>
                                    <th>Tồn Đầu</th>
                                    <th>Xuất</th>
                                    <th>Sổ Sách Hệ Thống</th>
                                    <th>Thực Tế Đếm</th>
                                    <th>Chênh Lệch</th>
                                    <th>Kết Luận</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($phieu['Details'] as $detail)
                                    @php
                                        $varianceClass = $detail['ChenhLech'] > 0
                                            ? 'variance-pos'
                                            : ($detail['ChenhLech'] < 0 ? 'variance-neg' : 'variance-zero');
                                        $badgeClass = $detail['KetLuan'] === 'Khớp'
                                            ? 'bg-success'
                                            : ($detail['ChenhLech'] > 0 ? 'bg-warning text-dark' : 'bg-danger');
                                    @endphp
                                    <tr>
                                        <td class="number-col">{{ $detail['MaNguyenLieu'] }}</td>
                                        <td class="fw-semibold">{{ $detail['TenNguyenLieu'] }}</td>
                                        <td class="number-col">{{ $detail['TonDau'] }}</td>
                                        <td class="number-col">{{ $detail['XuatTrongNgay'] }}</td>
                                        <td class="system-col">{{ $detail['SoLuongHeThong'] }}</td>
                                        <td class="count-col">{{ $detail['ThucTeDem'] }}</td>
                                        <td class="number-col {{ $varianceClass }}">
                                            {{ $detail['ChenhLech'] > 0 ? '+' . $detail['ChenhLech'] : $detail['ChenhLech'] }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $badgeClass }}">{{ $detail['KetLuan'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($phieu['PhieuHuy'])
                        <div class="p-3 pt-4">
                            <div class="waste-box">
                                <div class="waste-title">CHI TIẾT PHIẾU XUẤT HỦY ĐÍNH KÈM: {{ $phieu['PhieuHuy']->MaPhieuHuy }}</div>
                                <div class="table-responsive">
                                    <table class="table waste-table table-sm align-middle mb-0 bg-white">
                                        <thead>
                                            <tr>
                                                <th>Mã Mặt Hàng</th>
                                                <th>Tên Nguyên Liệu Hủy</th>
                                                <th>Số Lượng Tiêu Hủy</th>
                                                <th>Lý Do Tiêu Hủy Chi Tiết</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($phieu['PhieuHuyDetails'] as $huy)
                                                <tr>
                                                    <td class="number-col">{{ $huy['MaNguyenLieu'] }}</td>
                                                    <td>{{ $huy['TenNguyenLieu'] }}</td>
                                                    <td class="text-danger fw-bold text-center">{{ $huy['SoLuongHuy'] }}</td>
                                                    <td>{{ $huy['LyDo'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($phieu['GhiChu'])
                        <div class="px-3 pb-3">
                            <div class="alert alert-light border mb-0">
                                <div class="fw-bold mb-1">Ghi chú xử lý</div>
                                <div>{{ $phieu['GhiChu'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="review-actions">
                    @if($phieu['TrangThai'] === 'Chờ duyệt')
                        <div class="review-action-row">
                            <form action="{{ route('quanly.kiemke.tuchoi', $phieu['MaPhieuKiemKe']) }}" method="POST" class="review-reject-form">
                                @csrf
                                <div class="d-flex gap-2 flex-wrap">
                                    <input
                                        type="text"
                                        name="ghi_chu_tu_choi"
                                        class="form-control review-note"
                                        placeholder="Bắt buộc nhập ghi chú lý do chênh lệch trước khi bấm từ chối..."
                                        required
                                    >
                                    <button type="submit" class="btn btn-outline-danger px-4">Từ chối</button>
                                </div>
                            </form>

                            <div class="text-end">
                                @if($phieu['isFullyMatched'])
                                    <form action="{{ route('quanly.chotca', $phieu['MaPhieuKiemKe']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary px-4">Xác nhận và chốt ca</button>
                                    </form>
                                @else
                                    <button type="button" class="btn review-lock-button px-4" disabled>Khóa Chốt Ca (Số liệu chưa khớp)</button>
                                @endif
                            </div>
                        </div>

                        @unless($phieu['isFullyMatched'])
                            <div class="review-lock-msg">Hệ thống đã tự động khóa chốt ca do phát hiện chênh lệch.</div>
                        @endunless
                    @elseif($phieu['TrangThai'] === 'Từ chối')
                        <div class="text-danger fw-semibold">Báo cáo đã bị từ chối và đang chờ nhân viên hiệu chỉnh lại.</div>
                    @elseif($phieu['TrangThai'] === 'Đã duyệt')
                        <div class="text-success fw-semibold">Báo cáo đã được duyệt và chốt ca thành công.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
