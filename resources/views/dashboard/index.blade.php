@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $role = auth()->user()->VaiTro ?? null;
    $isManager = in_array($role, ['Quan ly', 'Quản lý'], true);
    $isStoreChief = in_array($role, ['Cua hang truong', 'Cửa hàng trưởng'], true);
    $isEmployee = in_array($role, ['Nhan vien', 'Nhân viên'], true);
    $orderRoute = $isManager ? route('don-hang.index') : route('purchase-orders.index');
    $inspectionRoute = $isManager ? route('kiem-ke.index') : route('cht.khochinh.thongke');
@endphp

@section('content')
@if ($isStoreChief)
    <!-- Dashboard Cửa hàng trưởng -->
    <div class="mb-4">
        <h2 class="text-lotteria fw-bold mb-1">Xin chào {{ auth()->user()->HoTen }}!</h2>
    </div>

    <div class="row g-4 mb-4">
        <!-- Phiếu chờ duyệt -->
        <div class="col-md-6">
            <a href="{{ $orderRoute }}" class="text-decoration-none d-block">
                <div class="card bg-warning text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Phiếu chờ duyệt</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-clipboard-check opacity-75" viewBox="0 0 16 16">
                                <path d="M4.5 2a.5.5 0 0 0-.5.5v12a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5h-3.5V1h-1v1h-3zm1.5 1h4V2h-4v1zm0 2h4v1h-4V5zm0 2h4v1h-4V7zm0 2h4v1h-4V9zm0 2h4v1h-4v-1z"/>
                            </svg>
                        </div>
                        <div class="display-4 fw-bold mb-2">{{ $countDonHangChoDuyet ?? 0 }}</div>
                        <p class="mb-0 opacity-90">Các đơn hàng đang chờ phê duyệt</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Phiếu xuất hủy -->
        <div class="col-md-6">
            <a href="{{ route('xuat-huy.index') }}" class="text-decoration-none d-block">
                <div class="card bg-danger text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Phiếu xuất hủy</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-trash opacity-75" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                        </div>
                        <div class="display-4 fw-bold mb-2">{{ $countPhieuXuatHuy ?? 0 }}</div>
                        <p class="mb-0 opacity-90">Tổng số phiếu xuất hủy trong hệ thống</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Phiếu thống kê tồn kho -->
        <div class="col-md-6">
            <a href="{{ $inspectionRoute }}" class="text-decoration-none d-block">
                <div class="card bg-primary text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Phiếu thống kê tồn kho</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-archive opacity-75" viewBox="0 0 16 16">
                                <path d="M0 2.5A1.5 1.5 0 0 1 1.5 1h13A1.5 1.5 0 0 1 16 2.5V4a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V2.5zM1 5v8.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5V5H1zm2.5 2a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 0-.5-.5h-9z"/>
                            </svg>
                        </div>
                        <div class="display-4 fw-bold mb-2">{{ $countPhieuThongKeTonKho ?? 0 }}</div>
                        <p class="mb-0 opacity-90">Các phiếu kiểm kê và thống kê tồn kho</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Phiếu giải trình thất thoát -->
        <div class="col-md-6">
            <a href="{{ route('giai-trinh.index') }}" class="text-decoration-none d-block">
                <div class="card text-white h-100 page-card border-0 shadow-sm" style="background-color: #6c757d;">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Phiếu giải trình thất thoát</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-circle opacity-75" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0L6.6 10h-.2l-1.7-5.005z"/>
                            </svg>
                        </div>
                        <div class="display-4 fw-bold mb-2">{{ $countPhieuGiaiTrinh ?? 0 }}</div>
                        <p class="mb-0 opacity-90">Các phiếu giải trình về thất thoát, chênh lệch</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@elseif ($isManager)
    <!-- Dashboard Quản lý -->
    <div class="mb-4">
        <h2 class="text-lotteria fw-bold mb-1">Bảng điều hướng nghiệp vụ</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <a href="{{ $orderRoute }}" class="text-decoration-none d-block">
                <div class="card bg-warning text-white h-100 page-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="fw-bold">Đơn hàng</h5>
                                <p class="mb-2">Tạo đơn đặt hàng, theo dõi chờ phê duyệt, đã nhận hàng và nhập kho.</p>
                            </div>
                            <div class="display-6 fw-bold">{{ $countChoDuyet ?? 0 }}</div>
                        </div>
                        <h3 class="mb-0 mt-3">Mở phân hệ</h3>
                    </div>
                </div>
            </a>
        </div>

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
    </div>
@elseif ($isEmployee)
    <!-- Dashboard Nhân viên -->
    <div class="mb-4">
        <h2 class="text-lotteria fw-bold mb-1">Xin chào {{ auth()->user()->HoTen }}!</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <a href="{{ route('nhanvien.phieuxuat') }}" class="text-decoration-none d-block">
                <div class="card bg-primary text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Phiếu xuất kho</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-arrow-right opacity-75" viewBox="0 0 16 16">
                                <path d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                            </svg>
                        </div>
                        <p class="mb-0 opacity-90">Tiếp nhận và xử lý phiếu xuất kho</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('ds-don-hang.index') }}" class="text-decoration-none d-block">
                <div class="card bg-success text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Đơn hàng cần nhận</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-seam opacity-75" viewBox="0 0 16 16">
                                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.84L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                            </svg>
                        </div>
                        <p class="mb-0 opacity-90">Nhận hàng và tạo phiếu nhận hàng</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('kiemke.bep') }}" class="text-decoration-none d-block">
                <div class="card bg-warning text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Kiểm kê cuối ngày</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-calendar-check opacity-75" viewBox="0 0 16 16">
                                <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                        </div>
                        <p class="mb-0 opacity-90">Kiểm kê và báo cáo cuối ngày</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="{{ route('khochinh.kiemke') }}" class="text-decoration-none d-block">
                <div class="card bg-secondary text-white h-100 page-card border-0 shadow-sm">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Kiểm kê định kỳ</h5>
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-calendar2-check opacity-75" viewBox="0 0 16 16">
                                <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M9.646 14.146a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708l-1.646-1.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708l-2 2a.5.5 0 0 0-.708.708L8 12.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L8.354 10.146a.5.5 0 0 0-.708.708l1.146 1.147a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L10 8.293l-1.146-1.147a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L12 6.293l-1.146-1.147a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14 4.293l-1.146-1.147a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14 2.293l-1.146-1.147a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14 0.293l-1.146-1.147a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-1.707l-1.146-1.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-3.707l-1.146-1.146a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-5.707l-1.146-1.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-7.707l-1.146-1.146a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-9.707l-1.146-1.146a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-11.707l-1.146-1.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708L14-13.707l-1.146-1.146a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708.708l2-2a.5.5 0 0 0-.708-.708z"/>
                            </svg>
                        </div>
                        <p class="mb-0 opacity-90">Kiểm kê kho chính định kỳ</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@else
    <!-- Dashboard mặc định -->
    <div class="mb-4">
        <h2 class="text-lotteria fw-bold mb-1">Chào mừng quay lại hệ thống Lotteria!</h2>
    </div>
@endif
@endsection
