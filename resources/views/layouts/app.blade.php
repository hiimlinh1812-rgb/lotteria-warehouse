<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hệ Thống Quản Lý Kho - Lotteria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* CSS của bạn: Giữ lại để các trang phiếu xuất không bị mất màu/vỡ nút */
        .bg-lotteria { background-color: #a52a2a !important; color: white; }
        .text-lotteria { color: #a52a2a !important; }
        .btn-lotteria { background-color: #a52a2a; color: white; }
        .btn-lotteria:hover { background-color: #8b2323; color: white; }
        
        /* CSS của bạn kia: Giữ lại để menu đẹp như nhóm yêu cầu */
        .navbar-nav .nav-link.active {
            font-weight: bold;
            background-color: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-lotteria mb-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="#">Lotteria Kho</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    
                    <!-- 1. CẤP QUYỀN: CỬA HÀNG TRƯỞNG -->
                    @can('isCuaHangTruong')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
                        </li>
                    @endcan

                    <!-- 2. CẤP QUYỀN: QUẢN LÝ CA (ẨN/HIỆN ĐÚNG LINK PHÊ DUYỆT) -->
                    @can('isQuanLy')
                        <li class="nav-item"><a class="nav-link" href="#">Đơn hàng</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('xuatkho.*') ? 'active' : '' }}" href="{{ route('xuatkho.index') }}">Xuất kho</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Xuất hủy</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Kiểm kê</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Giải trình</a></li>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold {{ request()->routeIs('quanly.kiemke.bep') ? 'active' : '' }}" href="{{ route('quanly.kiemke.bep') }}">Duyệt kiểm kê bếp</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold {{ request()->routeIs('quanly.khochinh.duyet') ? 'active' : '' }}" href="{{ route('quanly.khochinh.duyet') }}">Duyệt kiểm kho chính</a>
                        </li>
                    @endcan

                    <!-- 3. CẤP QUYỀN: NHÂN VIÊN CA TRỰC (ẨN/HIỆN ĐÚNG LINK LẬP PHIẾU) -->
                    @can('isNhanVien')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('nhanvien.*') ? 'active' : '' }}" href="{{ route('nhanvien.phieuxuat') ?? '#' }}">Phiếu xuất kho</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Danh sách đơn hàng</a></li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kiemke.bep') ? 'active' : '' }}" href="{{ route('kiemke.bep') }}">Kiểm kê cuối ngày</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('khochinh.kiemke') ? 'active' : '' }}" href="{{ route('khochinh.kiemke') }}">Kiểm kê hàng hóa định kỳ</a>
                        </li>
                    @endcan

                </ul>

                <!-- THÔNG TIN TÀI KHOẢN ĐĂNG NHẬP CHUẨN AUTH -->
                <div class="d-flex align-items-center text-white">
                    @if(auth()->check())
                        <span class="me-3">Xin chào, <strong>{{ auth()->user()->HoTen }}</strong>!</span>
                        <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm fw-bold">Đăng xuất</a>
                    @endif
                </div>

            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if (session('status'))
            <div class="alert alert-success shadow-sm mb-4">
                {{ session('status') }}
            </div>
        @endif

        @yield('content') 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>