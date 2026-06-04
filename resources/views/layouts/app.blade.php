<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotteria Warehouse Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Lotteria Kho</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                    </li>
                    @if(auth()->check() && auth()->user()->VaiTro === 'Cửa hàng trưởng')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
                    </li>
                    @endif
                </ul>
                <div class="d-flex flex-nowrap text-white align-items-center">
                    @if(auth()->check())
                    <span class="me-3 text-nowrap">Xin chào, {{ auth()->user()->HoTen }}!</span>
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