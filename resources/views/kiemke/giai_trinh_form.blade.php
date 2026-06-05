@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white text-center py-3">
                    <h4 class="mb-0 fw-bold">📋 LẬP PHIẾU GIẢI TRÌNH THẤT THOÁT KHO CHÍNH</h4>
                    <small class="text-white-50">Mã Phiếu Kiểm Kê Liên Kết: {{ $maPhieu }}</small>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-warning border-start border-4 border-warning shadow-sm mb-4">
                            <h6 class="fw-bold text-dark mb-2">⚠️ Luồng phụ 1: Thông tin nhập không đầy đủ, hệ thống yêu cầu nhập lại:</h6>
                            <ul class="mb-0 small text-danger fw-bold">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4 bg-light rounded p-3 border">
                        <h6 class="fw-bold text-secondary mb-2">📊 Bằng chứng rà soát sai lệch vật lý từ ca sử dụng trước:</h6>
                        <table class="table table-sm table-bordered bg-white text-center mb-0 small">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Mã Lô</th>
                                    <th>Sổ Sách</th>
                                    <th>Thực Tế Đếm</th>
                                    <th>Chênh Lệch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailsLech as $dl)
                                <tr>
                                    <td class="font-monospace fw-bold">{{ $dl->MaLoHang }}</td>
                                    <td>{{ $dl->SoLuongHeThong }}</td>
                                    <td class="fw-bold text-dark">{{ $dl->SoLuongThucTe }}</td>
                                    <td class="text-danger fw-bold font-monospace">{{ $dl->ChenhLech }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <form action="{{ route('quanly.khochinh.giaitrinh', $maPhieu) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">1. Nội dung giải trình thất thoát:</label>
                            <textarea name="noi_dung" rows="3" class="form-control" placeholder="Ví dụ: Giải trình chênh lệch số liệu tồn kho cuối tháng của các lô hàng thịt bò patty và xà lách..." required>{{ old('noi_dung') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">2. Nguyên nhân thất thoát khách quan/chủ quan:</label>
                            <textarea name="nguyen_nhan" rows="3" class="form-control" placeholder="Ví dụ: Do thiết bị đo nhiệt độ tủ đông kho chính gặp sự cố trong đêm làm một số lượng nguyên liệu bị hỏng rã đông buộc phải hủy bỏ..." required>{{ old('nguyen_nhan') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">3. Bằng chứng liên quan kèm theo (Mã biên bản vật lý / Link hình ảnh):</label>
                            <input type="text" name="bang_chung" class="form-control" placeholder="Ví dụ: BB-HUY-LH004 / Ảnh đính kèm camera khu vực sơ chế ca tối" value="{{ old('bang_chung') }}" required>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('quanly.khochinh.duyet') }}" class="btn btn-light w-100 fw-bold border">Quay lại danh sách</a>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-danger class w-100 fw-bold shadow-sm">Xác nhận gửi phiếu giải trình</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection