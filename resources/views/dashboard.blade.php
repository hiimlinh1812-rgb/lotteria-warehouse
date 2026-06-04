@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2 class="text-danger fw-bold border-bottom pb-2">DASHBOARD TỔNG QUAN KHO</h2>
        <p class="text-muted">Chào mừng Cửa hàng trưởng đã quay lại hệ thống!</p>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-muted fw-semibold">TỔNG NGUYÊN LIÊU</h6>
                <h3 class="fw-bold text-dark">120 <span class="fs-6 fw-normal">mặt hàng</span></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted fw-semibold">HÀNG SẮP HẾT (CẦN NHẬP)</h6>
                <h3 class="fw-bold text-dark">5 <span class="fs-6 fw-normal">mặt hàng</span></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-info border-4">
            <div class="card-body">
                <h6 class="text-muted fw-semibold">PHIẾU CHỜ DUYỆT</h6>
                <h3 class="fw-bold text-dark">2 <span class="fs-6 fw-normal">phiếu</span></h3>
            </div>
        </div>
    </div>
</div>
@endsection