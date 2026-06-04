@extends('layouts.app')

@section('title', 'Chi tiết đơn mua ' . $order->MaDonDatHang)

@php
    $statusClass = match ($order->TrangThai) {
        'Da duyet' => 'approved',
        'Cho phe duyet' => 'pending',
        'Tu choi', 'Da huy' => 'rejected',
        'Dang xu ly' => 'processing',
        'Da nhan hang', 'Da nhap kho' => 'received',
        default => '',
    };
    $canApprove = in_array($order->TrangThai, ['Cho phe duyet', 'Dang xu ly'], true);
    $canEdit = in_array($order->TrangThai, ['Cho phe duyet', 'Dang xu ly'], true);
    $canProcess = $order->TrangThai === 'Cho phe duyet';
    $canCancel = in_array($order->TrangThai, ['Cho phe duyet', 'Dang xu ly'], true);
    $canReceive = $order->TrangThai === 'Da duyet';
    $canStock = $order->TrangThai === 'Da nhan hang';
@endphp

@section('content')
    <div class="topbar">
        <div>
            <h1>Đơn mua {{ $order->MaDonDatHang }}</h1>
            <p class="subtle">Lập ngày {{ \Illuminate\Support\Carbon::parse($order->NgayDat)->format('d/m/Y') }} bởi {{ $order->HoTen }}.</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('purchase-orders.index') }}">Danh sách</a>
            @if ($canEdit)
                <a class="btn btn-secondary" href="{{ route('purchase-orders.edit', $order->MaDonDatHang) }}">Sửa đơn</a>
            @endif
            <a class="btn btn-primary" href="{{ route('purchase-orders.create') }}">Tạo đơn mới</a>
        </div>
    </div>

    <div class="panel">
        <div class="grid-3">
            <div>
                <p class="subtle">Trạng thái</p>
                <span class="badge {{ $statusClass }}">{{ $statusLabels[$order->TrangThai] ?? $order->TrangThai }}</span>
            </div>
            <div>
                <p class="subtle">Người lập</p>
                <strong>{{ $order->MaTaiKhoan }} - {{ $order->HoTen }}</strong>
            </div>
            <div>
                <p class="subtle">Vai trò</p>
                <strong>{{ $order->VaiTro }}</strong>
            </div>
        </div>
        <div style="margin-top:14px;">
            <p class="subtle">Ghi chú</p>
            <strong>{{ $order->GhiChu ?: '-' }}</strong>
        </div>
    </div>

    <div class="panel">
        <h2 style="font-size:18px;margin:0 0 14px;">Chi tiết nguyên liệu</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã NL</th>
                    <th>Tên nguyên liệu</th>
                    <th>Nhóm hàng</th>
                    <th>Tồn kho</th>
                    <th>Số lượng đặt</th>
                    <th>Đơn vị</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td><strong>{{ $item->MaNguyenLieu }}</strong></td>
                        <td>{{ $item->TenNguyenLieu }}</td>
                        <td>{{ $item->NhomHang }}</td>
                        <td>{{ number_format($item->SoLuongTonKho) }}</td>
                        <td><strong>{{ number_format($item->SoLuongDat) }}</strong></td>
                        <td>{{ $item->DonViTinh }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Tổng số lượng đặt</th>
                    <th>{{ number_format($items->sum('SoLuongDat')) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="panel">
        <h2 style="font-size:18px;margin:0 0 14px;">Thao tác theo trạng thái</h2>
        @if ($canApprove || $canCancel || $canReceive || $canStock)
            @if ($canProcess)
                <form method="post" action="{{ route('purchase-orders.process', $order->MaDonDatHang) }}" style="margin-bottom:16px;">
                    @csrf
                    <div class="grid-2">
                        <div class="field">
                            <label for="process-account">Người xử lý</label>
                            <select id="process-account" name="MaTaiKhoan" required>
                                <option value="">Chọn tài khoản</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->MaTaiKhoan }}">{{ $account->MaTaiKhoan }} - {{ $account->HoTen }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="GhiChuXuLy">Ghi chú xử lý</label>
                            <input id="GhiChuXuLy" name="GhiChuXuLy" maxlength="180" placeholder="Đang kiểm tra số lượng/giá">
                        </div>
                    </div>
                    <button class="btn btn-secondary" style="margin-top:12px;" type="submit">Chuyển đang xử lý</button>
                </form>
            @endif

            @if ($canApprove)
                <div class="grid-2">
                <form method="post" action="{{ route('purchase-orders.approve', $order->MaDonDatHang) }}">
                    @csrf
                    <div class="field">
                        <label for="approve-account">Người phê duyệt</label>
                        <select id="approve-account" name="MaTaiKhoan" required>
                            <option value="">Chọn tài khoản</option>
                            @foreach ($approvalAccounts as $account)
                                <option value="{{ $account->MaTaiKhoan }}">{{ $account->MaTaiKhoan }} - {{ $account->HoTen }} ({{ $account->VaiTro }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="margin-top:10px;">
                        <label for="GhiChuDuyet">Ghi chú phê duyệt</label>
                        <input id="GhiChuDuyet" name="GhiChuDuyet" maxlength="180" placeholder="Đồng ý mua hàng">
                    </div>
                    <button class="btn btn-success" style="margin-top:12px;" type="submit">Phê duyệt</button>
                </form>

                <form method="post" action="{{ route('purchase-orders.reject', $order->MaDonDatHang) }}">
                    @csrf
                    <div class="field">
                        <label for="reject-account">Người từ chối</label>
                        <select id="reject-account" name="MaTaiKhoan" required>
                            <option value="">Chọn tài khoản</option>
                            @foreach ($approvalAccounts as $account)
                                <option value="{{ $account->MaTaiKhoan }}">{{ $account->MaTaiKhoan }} - {{ $account->HoTen }} ({{ $account->VaiTro }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="margin-top:10px;">
                        <label for="LyDoTuChoi">Lý do từ chối</label>
                        <input id="LyDoTuChoi" name="LyDoTuChoi" maxlength="180" placeholder="Nhập lý do bắt buộc">
                    </div>
                    <button class="btn btn-danger" style="margin-top:12px;" type="submit">Từ chối</button>
                </form>
                </div>
            @endif

            @if ($canCancel)
                <form method="post" action="{{ route('purchase-orders.cancel', $order->MaDonDatHang) }}" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?');" style="margin-top:16px;">
                    @csrf
                    <button class="btn btn-danger" type="submit">Hủy đơn</button>
                </form>
            @endif

            @if ($canReceive)
                <form method="post" action="{{ route('purchase-orders.receive', $order->MaDonDatHang) }}" style="margin-top:16px;">
                    @csrf
                    <button class="btn btn-success" type="submit">Xác nhận nhận hàng</button>
                </form>
            @endif

            @if ($canStock)
                <form method="post" action="{{ route('purchase-orders.stock', $order->MaDonDatHang) }}" style="margin-top:16px;">
                    @csrf
                    <button class="btn btn-success" type="submit">Xác nhận nhập kho</button>
                </form>
            @endif
        @else
            <p class="subtle">Đơn mua này đã ở trạng thái {{ $statusLabels[$order->TrangThai] ?? $order->TrangThai }}, không còn thao tác tiếp theo.</p>
        @endif
    </div>

    <details class="panel">
        <summary style="cursor:pointer;font-weight:700;">Truy vết thao tác</summary>
        <p class="subtle" style="margin-top:10px;">Phần này được ẩn mặc định, mở ra để xem lịch sử thao tác trên đơn mua.</p>

        @if ($auditTrail->isEmpty())
            <p class="subtle" style="margin-top:14px;">Chưa có dữ liệu truy vết cho đơn mua này.</p>
        @else
            <table style="margin-top:14px;">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Hành động</th>
                        <th>Người thực hiện</th>
                        <th>Từ trạng thái</th>
                        <th>Sang trạng thái</th>
                        <th>Nội dung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($auditTrail as $trace)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($trace->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td><strong>{{ $trace->HanhDong }}</strong></td>
                            <td>{{ $trace->MaTaiKhoan ?: 'Hệ thống' }}</td>
                            <td>{{ $trace->TrangThaiTruoc ? ($statusLabels[$trace->TrangThaiTruoc] ?? $trace->TrangThaiTruoc) : '-' }}</td>
                            <td>{{ $trace->TrangThaiSau ? ($statusLabels[$trace->TrangThaiSau] ?? $trace->TrangThaiSau) : '-' }}</td>
                            <td>{{ $trace->NoiDung ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </details>
@endsection
