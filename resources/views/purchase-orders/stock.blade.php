@extends('layouts.app')

@section('title', 'Nhập kho đơn ' . $order->MaDonDatHang)

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Form Nhập kho cho đơn {{ $order->MaDonDatHang }}</h2>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Quay lại đối soát</a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card page-card h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-2">Thông tin tham chiếu</div>
                <div class="mb-2"><strong>Mã đơn:</strong> {{ $order->MaDonDatHang }}</div>
                <div class="mb-2"><strong>Phiếu nhận:</strong> {{ $receipt->MaPhieuNhan }}</div>
                <div class="mb-2"><strong>Ngày nhận:</strong> {{ \Illuminate\Support\Carbon::parse($receipt->NgayNhan)->format('d/m/Y') }}</div>
                <div class="mb-0"><strong>Mã phiếu nhập dự kiến:</strong> {{ $stockCode }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="post" action="{{ route('don-hang.stock.store', $order->MaDonDatHang) }}" class="card page-card">
            @csrf
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="mb-1 fw-bold">Thông tin nhập kho</h5>
                <p class="text-muted mb-0">Xác nhận thời điểm nhập kho và người phụ trách thao tác.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="NgayNhap" class="form-label fw-semibold">Ngày nhập kho</label>
                        <input id="NgayNhap" type="date" name="NgayNhap" class="form-control" value="{{ old('NgayNhap', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="MaTaiKhoan" class="form-label fw-semibold">Người nhập kho</label>
                        <select id="MaTaiKhoan" name="MaTaiKhoan" class="form-select" required>
                            <option value="">Chọn tài khoản</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->MaTaiKhoan }}" {{ old('MaTaiKhoan', auth()->user()->MaTaiKhoan ?? '') == $account->MaTaiKhoan ? 'selected' : '' }}>
                                    {{ $account->MaTaiKhoan }} - {{ $account->HoTen }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="TrangThaiDon" class="form-label fw-semibold">Trạng thái hiện tại</label>
                        <input id="TrangThaiDon" class="form-control" value="Đã nhận hàng" disabled>
                    </div>
                    <div class="col-12">
                        <label for="GhiChu" class="form-label fw-semibold">Ghi chú nhập kho</label>
                        <textarea id="GhiChu" name="GhiChu" rows="4" maxlength="255" class="form-control" placeholder="Ví dụ: hàng đủ số lượng, kho đã tiếp nhận">{{ old('GhiChu') }}</textarea>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-success fw-bold" type="submit">Xác nhận nhập kho</button>
                    <a class="btn btn-outline-secondary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Hủy</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
