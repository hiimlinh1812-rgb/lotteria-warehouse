@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $orderRoute = auth()->check() && in_array(auth()->user()->VaiTro ?? null, ['Quan ly', 'Quản lý'], true)
        ? route('don-hang.index')
        : route('purchase-orders.index');
@endphp

@section('content')
<div class="mb-4">
    <h2 class="text-lotteria fw-bold mb-1">Bảng điều hướng nghiệp vụ</h2>
    <p class="text-muted mb-0">Các tab trên cùng đã được mở cho tài khoản quản trị. Bạn cũng có thể bấm trực tiếp các khối bên dưới để vào từng phân hệ.</p>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <a href="{{ $orderRoute }}" class="text-decoration-none d-block">
            <div class="card bg-warning text-white h-100 page-card">
                <div class="card-body">
                    <h5 class="fw-bold">Đơn hàng</h5>
                    <p class="mb-2">Tạo đơn đặt hàng, theo dõi chờ phê duyệt, đã nhận hàng và nhập kho.</p>
                    <h3 class="mb-0">Mở phân hệ</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('xuatkho.index') }}" class="text-decoration-none">
            <div class="card bg-danger text-white h-100 page-card">
                <div class="card-body">
                    <h5 class="fw-bold">Xuất kho</h5>
                    <p class="mb-2">Theo dõi phiếu xuất và tiến độ cấp phát nguyên liệu cho các bộ phận.</p>
                    <h3 class="mb-0">Mở phân hệ</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('xuathuy.index') }}" class="text-decoration-none">
            <div class="card bg-primary text-white h-100 page-card">
                <div class="card-body">
                    <h5 class="fw-bold">Xuất hủy</h5>
                    <p class="mb-2">Quản lý hàng hủy, hàng lỗi, hàng quá hạn cần loại bỏ khỏi kho.</p>
                    <h3 class="mb-0">Mở phân hệ</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('kiemke.index') }}" class="text-decoration-none">
            <div class="card bg-secondary text-white h-100 page-card">
                <div class="card-body">
                    <h5 class="fw-bold">Kiểm kê</h5>
                    <p class="mb-2">Đối chiếu số liệu tồn kho thực tế và hệ thống, ghi nhận chênh lệch.</p>
                    <h3 class="mb-0">Mở phân hệ</h3>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12">
        <a href="{{ route('giaitrinh.index') }}" class="text-decoration-none">
            <div class="card page-card border-0" style="background:#f8e7e7;">
                <div class="card-body">
                    <h5 class="fw-bold text-lotteria">Giải trình</h5>
                    <p class="mb-0 text-dark">Tập trung các phiếu giải trình liên quan đến thất thoát, chênh lệch và các phát sinh trong kho.</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
