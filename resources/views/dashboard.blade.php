@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="fw-bold text-dark">Chào mừng quay lại hệ thống Lotteria!</h2>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <a href="{{ route('quanly.khochinh.duyet') }}" class="text-decoration-none widget-link">
                <div class="card bg-warning text-white h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold">Phiếu chờ duyệt</h5>
                        <h3 class="display-5 fw-bold mb-0 mt-2">{{ $countChoDuyet }}</h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-3">
            <a href="{{ route('xuat-huy.index') }}" class="text-decoration-none widget-link">
                <div class="card bg-danger text-white h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold">Phiếu xuất hủy</h5>
                        <h3 class="display-5 fw-bold mb-0 mt-2">{{ $countXuatHuy }}</h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-3">
            <a href="{{ route('cht.khochinh.thongke') }}" class="text-decoration-none widget-link">
                <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold">Phiếu thống kê tồn kho</h5>
                        <h3 class="display-5 fw-bold mb-0 mt-2">{{ $countThongKe }}</h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 mb-3">
            <a href="{{ route('giai-trinh.index') }}" class="text-decoration-none widget-link">
                <div class="card bg-secondary text-white h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold">Phiếu giải trình thất thoát</h5>
                        <h3 class="display-5 fw-bold mb-0 mt-2">{{ $countGiaiTrinh }}</h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .widget-link {
        display: block;
        transition: transform 0.2s ease-in-out, filter 0.2s ease-in-out;
    }
    .widget-link:hover {
        transform: translateY(-4px); /* Card nhấc nhẹ lên tạo hiệu ứng 3D */
        filter: brightness(92%);     /* Tối màu nhẹ để sếp biết card click được */
    }
</style>
@endsection