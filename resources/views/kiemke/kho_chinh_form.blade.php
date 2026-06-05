@extends('layouts.app')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-danger text-white text-center py-3">
        <h3 class="mb-0 fw-bold">🏛️ LOTTERIA BÀ TRIỆU - KIỂM KÊ ĐỊNH KỲ</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('khochinh.kiemke.store') }}" method="POST">
            @csrf
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mã Lô Hàng</th>
                        <th>Tên Nguyên Liệu</th>
                        <th>Hạn Sử Dụng</th>
                        <th>Cảnh Báo HSD </th>
                        <!-- ❌ NGHIÊN CỨU BLIND COUNTING: ĐÃ XÓA CỘT SỔ SÁCH HỆ THỐNG ĐỂ ÉP NHÂN VIÊN PHẢI ĐẾM THỰC TẾ -->
                        <th class="table-warning text-dark" style="width: 35%;">Số Lượng Thực Tế Đếm</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($phiuKiemKeDienTu as $item)
                    <tr>
                        <td class="font-monospace fw-bold text-primary">{{ $item['ma_lo'] }}</td>
                        <td class="text-start fw-bold text-secondary">{{ $item['ten_nl'] }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $item['hsd'] }}</span></td>
                        <td>
                            <span class="badge {{ $item['canh_bao_hsd'] == 'An toàn' ? 'bg-success' : 'bg-danger animate-pulse' }}">
                                {{ $item['canh_bao_hsd'] }}
                            </span>
                        </td>
                        <!-- ❌ KHÔNG ĐIỀN SẴN SỐ LIỆU SỔ SÁCH - BẮT NHÂN VIÊN PHẢI TỰ GÕ SỐ THỰC TẾ -->
                        <td class="table-warning">
                            <input type="number" name="kiem_ke[{{ $item['ma_lo'] }}][thuc_te]" class="form-control text-center fw-bold" min="0" placeholder="Vui lòng đếm kho và nhập số thực tế vào đây..." required>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm">Hoàn Thành</button>
            </div>
        </form>
    </div>
</div>
@endsection