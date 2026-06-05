@extends('layouts.app')

@section('title', $page['title'])

@section('content')
<div class="mb-4">
    <h2 class="text-lotteria fw-bold mb-1">{{ $page['title'] }}</h2>
    <p class="text-muted mb-0">{{ $page['description'] }}</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card page-card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Tổng quan phân hệ</h5>
                <p class="mb-3">{{ $page['highlight'] }}</p>
                <div class="alert alert-light border mb-0">
                    Đây là màn điều hướng tạm để bạn có thể bấm vào và xem từng tab sau khi đăng nhập. Khi nhóm hoàn thiện nghiệp vụ của phân hệ này, mình có thể ráp tiếp dữ liệu thật vào đúng màn hiện tại.
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card page-card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Đi nhanh</h6>
                <div class="d-grid gap-2">
                    <a class="btn btn-outline-secondary text-start" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('don-hang.index') }}">Đơn hàng</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('xuatkho.index') }}">Xuất kho</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('xuat-huy.index') }}">Xuất hủy</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('kiem-ke.index') }}">Kiểm kê</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('giai-trinh.index') }}">Giải trình</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
