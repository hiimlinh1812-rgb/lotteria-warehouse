@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $role = auth()->user()->VaiTro ?? null;
    $isManager = in_array($role, ['Quan ly', 'Quản lý'], true);
    $isStoreChief = in_array($role, ['Cua hang truong', 'Cửa hàng trưởng'], true);
    $orderRoute = $isManager
        ? route('don-hang.index')
        : route('purchase-orders.index');
    $inspectionRoute = $isManager ? route('kiem-ke.index') : route('cht.khochinh.thongke');
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
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h5 class="fw-bold">Đơn hàng</h5>
                            <p class="mb-2">
                                {{ $isManager
                                    ? 'Tạo đơn đặt hàng, theo dõi chờ phê duyệt, đã nhận hàng và nhập kho.'
                                    : 'Phê duyệt đơn mua, theo dõi lịch sử xử lý và phản hồi về cho quản lý.' }}
                            </p>
                        </div>
                        <div class="display-6 fw-bold">{{ $countChoDuyet ?? 0 }}</div>
                    </div>
                    <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                </div>
            </div>
        </a>
    </div>

    @if ($isManager)
        <div class="col-md-6">
            <a href="{{ route('xuatkho.index') }}" class="text-decoration-none d-block">
                <div class="card bg-danger text-white h-100 page-card">
                    <div class="card-body">
                        <h5 class="fw-bold">Xuất kho</h5>
                        <p class="mb-2">Theo dõi phiếu xuất và tiến độ cấp phát nguyên liệu cho các bộ phận.</p>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('xuat-huy.index') }}" class="text-decoration-none d-block">
                <div class="card bg-primary text-white h-100 page-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="fw-bold">Xuất hủy</h5>
                                <p class="mb-2">Quản lý hàng hủy, hàng lỗi, hàng quá hạn cần loại bỏ khỏi kho.</p>
                            </div>
                            <div class="display-6 fw-bold">{{ $countXuatHuy ?? 0 }}</div>
                        </div>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('kiem-ke.index') }}" class="text-decoration-none d-block">
                <div class="card bg-secondary text-white h-100 page-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="fw-bold">Kiểm kê</h5>
                                <p class="mb-2">Đối chiếu số liệu tồn kho thực tế và hệ thống, ghi nhận chênh lệch.</p>
                            </div>
                            <div class="display-6 fw-bold">{{ $countThongKe ?? 0 }}</div>
                        </div>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12">
            <a href="{{ route('giai-trinh.index') }}" class="text-decoration-none d-block">
                <div class="card page-card border-0" style="background:#f8e7e7;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="fw-bold text-lotteria">Giải trình</h5>
                                <p class="mb-0 text-dark">Tập trung các phiếu giải trình liên quan đến thất thoát, chênh lệch và các phát sinh trong kho.</p>
                            </div>
                            <div class="display-6 fw-bold text-lotteria">{{ $countGiaiTrinh ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @elseif ($isStoreChief)
        <div class="col-md-6">
            <a href="{{ $inspectionRoute }}" class="text-decoration-none d-block">
                <div class="card bg-primary text-white h-100 page-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="fw-bold">Thống kê tồn kho</h5>
                                <p class="mb-2">Theo dõi kết quả kiểm kê định kỳ và số liệu đã được chốt duyệt.</p>
                            </div>
                            <div class="display-6 fw-bold">{{ $countThongKe ?? 0 }}</div>
                        </div>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('nguyen-lieu.index') }}" class="text-decoration-none d-block">
                <div class="card bg-danger text-white h-100 page-card">
                    <div class="card-body">
                        <h5 class="fw-bold">Nguyên liệu gốc</h5>
                        <p class="mb-2">Quản lý danh mục nguyên liệu, đơn vị tính và nhóm hàng của toàn hệ thống.</p>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('tai-khoan.index') }}" class="text-decoration-none d-block">
                <div class="card bg-secondary text-white h-100 page-card">
                    <div class="card-body">
                        <h5 class="fw-bold">Tài khoản</h5>
                        <p class="mb-2">Theo dõi phân quyền và thông tin tài khoản của quản lý, nhân viên và cửa hàng trưởng.</p>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>
    @endif
                <div class="card-body">
                    <h5 class="fw-bold text-lotteria">Giải trình</h5>
