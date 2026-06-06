@extends('layouts.app')

@section('title', 'Tạo phiếu đổi trả ' . $order->MaDonDatHang)

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Đổi trả cho đơn {{ $order->MaDonDatHang }}</h2>
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
                <div class="mb-0"><strong>Mã phiếu đổi trả dự kiến:</strong> {{ $returnCode }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="post" action="{{ route('don-hang.return.store', $order->MaDonDatHang) }}" class="card page-card">
            @csrf
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="mb-1 fw-bold">Thông tin phiếu đổi trả</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="NgayTao" class="form-label fw-semibold">Ngày tạo</label>
                        <input id="NgayTao" type="date" name="NgayTao" class="form-control bg-light" value="{{ old('NgayTao', now()->toDateString()) }}" readonly required style="pointer-events: none;" tabindex="-1">
                    </div>
                    <div class="col-md-4">
                        <label for="LoaiXuLy" class="form-label fw-semibold">Loại xử lý</label>
                        <select id="LoaiXuLy" name="LoaiXuLy" class="form-select" required>
                            <option value="">Chọn loại xử lý</option>
                            <option value="Đổi hàng" {{ old('LoaiXuLy') === 'Đổi hàng' ? 'selected' : '' }}>Đổi hàng</option>
                            <option value="Trả hàng" {{ old('LoaiXuLy') === 'Trả hàng' ? 'selected' : '' }}>Trả hàng</option>
                            <option value="Thiếu hàng" {{ old('LoaiXuLy') === 'Thiếu hàng' ? 'selected' : '' }}>Thiếu hàng</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="MaTaiKhoan" class="form-label fw-semibold">Người lập phiếu</label>
                        <input
                            id="MaTaiKhoan"
                            class="form-control"
                            value="{{ (auth()->user()->MaTaiKhoan ?? '') . ' - ' . (auth()->user()->HoTen ?? '') }}"
                            readonly
                        >
                        <input type="hidden" name="MaTaiKhoan" value="{{ old('MaTaiKhoan', auth()->user()->MaTaiKhoan ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label for="LyDo" class="form-label fw-semibold">Lý do</label>
                        <textarea id="LyDo" name="LyDo" rows="4" maxlength="255" class="form-control" placeholder="Ví dụ: nhân viên thực nhận thiếu 2 thùng dầu ăn so với đơn đặt" required>{{ old('LyDo') }}</textarea>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-lotteria fw-bold" type="submit">Tạo phiếu đổi trả</button>
                    <a class="btn btn-outline-secondary" href="{{ route('don-hang.show', $order->MaDonDatHang) }}">Hủy</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
