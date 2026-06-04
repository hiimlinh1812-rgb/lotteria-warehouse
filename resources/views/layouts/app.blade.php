<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Của bạn: Bắt buộc giữ lại để bảo mật form -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hệ Thống Quản Lý Kho - Lotteria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Của bạn: Giữ lại toàn bộ màu sắc thương hiệu -->
    <style>
        .bg-lotteria { background-color: #a52a2a !important; color: white; }
        .text-lotteria { color: #a52a2a !important; }
        .btn-lotteria { background-color: #a52a2a; color: white; }
        .btn-lotteria:hover { background-color: #8b2323; color: white; }
    </style>
</head>
<body class="bg-light">

    <!-- Của bạn: Thanh điều hướng dùng màu Lotteria -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-lotteria mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">🍔 Quản Lý Kho Lotteria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                
                <!-- Của bạn kia: Danh sách các link chức năng có phân quyền -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                    </li>
                    <!-- Đã thêm sẵn link tới trang quản lý phiếu xuất của bạn -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('xuatkho.*') ? 'active' : '' }}" href="{{ route('xuatkho.index') }}">Phiếu xuất kho</a>
                    </li>
                    
                    <!-- Hiển thị link Tài khoản nếu là Cửa hàng trưởng -->
                    @if(auth()->check() && auth()->user()->VaiTro === 'Cửa hàng trưởng')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Quản lý tài khoản</a>
                    </li>
                    @endif
                </ul>

                <!-- Của bạn kia: Lời chào và nút Đăng xuất -->
                <div class="d-flex flex-nowrap text-white align-items-center">
                    @if(auth()->check())
                        <span class="me-3 fw-bold text-nowrap">Xin chào, {{ auth()->user()->HoTen }}!</span>
                        <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm shadow-sm">Đăng xuất</a>
                    @endif
                </div>

            </div>
        </div>
    </nav>

    <!-- Của bạn: Giữ nguyên thẻ main này để các trang chi tiết/danh sách cũ không bị vỡ khung -->
    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>