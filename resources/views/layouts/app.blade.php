<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ Thống Quản Lý Kho - Lotteria')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-lotteria { background-color: #a52a2a !important; color: #fff; }
        .btn-lotteria { background-color: #a52a2a; color: #fff; }
        .btn-lotteria:hover { background-color: #8b2323; color: #fff; }
        .text-lotteria { color: #a52a2a !important; }
        .app-shell-nav {
            background: linear-gradient(90deg, #a52a2a 0%, #b82b2b 100%);
        }
        .navbar-nav {
            gap: 0.25rem;
        }
        .navbar-nav .nav-link.active {
            font-weight: 700;
            background-color: rgba(255,255,255,0.18);
            border-radius: 4px;
        }
        .navbar-nav .nav-link {
            border-radius: 0.55rem;
            padding: 0.65rem 0.95rem;
        }
        .top-module-bar {
            background: #fff;
            border: 1px solid #ead7d7;
            border-radius: 1rem;
            box-shadow: 0 0.25rem 1rem rgba(15, 23, 42, 0.05);
            padding: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .top-module-bar a {
            display: inline-flex;
            align-items: center;
            padding: 0.65rem 0.95rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: #6b7280;
            font-weight: 600;
        }
        .top-module-bar a.active {
            background: #a52a2a;
            color: #fff;
        }
        .top-module-bar a:hover {
            background: #f8e4e4;
            color: #8b2323;
        }
        .top-module-bar a.active:hover {
            background: #a52a2a;
            color: #fff;
        }
        .page-card {
            border: 0;
            box-shadow: 0 0.25rem 1rem rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .status-badge.pending { background: #fff3cd; color: #8a5a00; }
        .status-badge.processing { background: #dbeafe; color: #1d4ed8; }
        .status-badge.approved { background: #dcfce7; color: #166534; }
        .status-badge.received { background: #ede9fe; color: #6d28d9; }
        .status-badge.stocked { background: #cffafe; color: #155e75; }
        .status-badge.rejected { background: #fee2e2; color: #b91c1c; }
        .status-badge.cancelled { background: #e5e7eb; color: #374151; }
        .summary-tile {
            border-left: 4px solid #a52a2a;
            border-radius: 1rem;
        }
        .summary-tile .display-6 {
            line-height: 1;
        }
        .table > :not(caption) > * > * {
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-light">
    @php
        $user = auth()->user();
        $isManagementUser = auth()->check() && in_array($user->VaiTro, ['Quan ly', 'Quản lý', 'Cua hang truong', 'Cửa hàng trưởng'], true);
        $orderRoute = auth()->check()
            ? (in_array($user->VaiTro, ['Quan ly', 'Quản lý'], true) ? route('don-hang.index') : route('purchase-orders.index'))
            : route('purchase-orders.index');
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark app-shell-nav mb-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="{{ auth()->check() ? url('/') : route('login') }}">Lotteria Kho</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @if ($isManagementUser)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('don-hang.*') || request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ $orderRoute }}">Đơn hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('xuatkho.*') ? 'active' : '' }}" href="{{ route('xuatkho.index') }}">Xuất kho</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('xuathuy.*') ? 'active' : '' }}" href="{{ route('xuathuy.index') }}">Xuất hủy</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kiemke.*') ? 'active' : '' }}" href="{{ route('kiemke.index') }}">Kiểm kê</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('giaitrinh.*') ? 'active' : '' }}" href="{{ route('giaitrinh.index') }}">Giải trình</a>
                        </li>
                        @can('isCuaHangTruong')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
                            </li>
                        @endcan
                    @elseif(auth()->check())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">Danh sách đơn hàng</a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center text-white gap-2">
                    @auth
                        <span class="me-2">Xin chào, <strong>{{ auth()->user()->HoTen }}</strong>!</span>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm fw-bold">Đăng xuất</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm fw-bold">Đăng nhập</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="container pb-4">
        @if ($isManagementUser)
            <div class="top-module-bar d-flex flex-wrap gap-2">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Tổng quan</a>
                <a class="{{ request()->routeIs('don-hang.*') || request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ $orderRoute }}">Đơn hàng</a>
                <a class="{{ request()->routeIs('xuatkho.*') ? 'active' : '' }}" href="{{ route('xuatkho.index') }}">Xuất kho</a>
                <a class="{{ request()->routeIs('xuathuy.*') ? 'active' : '' }}" href="{{ route('xuathuy.index') }}">Xuất hủy</a>
                <a class="{{ request()->routeIs('kiemke.*') ? 'active' : '' }}" href="{{ route('kiemke.index') }}">Kiểm kê</a>
                <a class="{{ request()->routeIs('giaitrinh.*') ? 'active' : '' }}" href="{{ route('giaitrinh.index') }}">Giải trình</a>
                @can('isCuaHangTruong')
                    <a class="{{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                    <a class="{{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
                @endcan
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning shadow-sm">{{ session('warning') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
