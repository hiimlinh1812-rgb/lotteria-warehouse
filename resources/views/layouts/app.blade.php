<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotteria Warehouse Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Tùy chỉnh nhẹ để menu trông chuyên nghiệp hơn */
        .navbar-nav .nav-link.active {
            font-weight: bold;
            background-color: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Lotteria Kho</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    
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

                    @can('isQuanLy')
                        <li class="nav-item"><a class="nav-link" href="#">Đơn hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Xuất kho</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Xuất hủy</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Kiểm kê</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Giải trình</a></li>
                    @endcan

                    @can('isNhanVien')
                        <li class="nav-item"><a class="nav-link" href="#">Phiếu xuất kho</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Danh sách đơn hàng</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Kiểm kê cuối ngày</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Kiểm kê hàng hóa định kỳ</a></li>
                    @endcan
                </ul>

                <div class="d-flex align-items-center text-white">
                    @if(auth()->check())
                        <span class="me-3">Xin chào, <strong>{{ auth()->user()->HoTen }}</strong>!</span>
                        <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm">Đăng xuất</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content') 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>