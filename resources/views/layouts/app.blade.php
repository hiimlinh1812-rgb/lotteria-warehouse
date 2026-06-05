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
        .text-lotteria { color: #a52a2a !important; }
        .btn-lotteria { background-color: #a52a2a; color: #fff; }
        .btn-lotteria:hover { background-color: #8b2323; color: #fff; }
        .app-shell-nav { background: linear-gradient(90deg, #a52a2a 0%, #b82b2b 100%); }
        .navbar-nav { gap: 0.25rem; }
        .navbar-nav .nav-link {
            border-radius: 0.55rem;
            padding: 0.65rem 0.95rem;
        }
        .navbar-nav .nav-link.active {
            font-weight: 700;
            background-color: rgba(255,255,255,0.18);
            border-radius: 4px;
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
        $role = $user->VaiTro ?? null;
        $isStoreChief = auth()->check() && in_array($role, ['Cua hang truong', 'Cửa hàng trưởng'], true);
        $isManager = auth()->check() && in_array($role, ['Quan ly', 'Quản lý'], true);
        $isEmployee = auth()->check() && in_array($role, ['Nhan vien', 'Nhân viên'], true);
        $isManagementUser = $isManager || $isStoreChief;
        $orderRoute = auth()->check() ? ($isManager ? route('don-hang.index') : route('purchase-orders.index')) : route('purchase-orders.index');
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark app-shell-nav mb-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="{{ auth()->check() ? url('/') : route('login') }}">Lotteria Kho</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @if ($isStoreChief)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">Đơn hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
                        </li>
                    @elseif ($isManager)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('don-hang.*') ? 'active' : '' }}" href="{{ route('don-hang.index') }}">Đơn hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('xuatkho.*') ? 'active' : '' }}" href="{{ route('xuatkho.index') }}">Xuất kho</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('xuat-huy.*') ? 'active' : '' }}" href="{{ route('xuat-huy.index') }}">Xuất hủy</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kiem-ke.*') ? 'active' : '' }}" href="{{ route('kiem-ke.index') }}">Kiểm kê</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('giai-trinh.*') ? 'active' : '' }}" href="{{ route('giai-trinh.index') }}">Giải trình</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quanly.kiemke.*') ? 'active' : '' }}" href="{{ route('quanly.kiemke.bep') }}">Duyệt kiểm kê bếp</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quanly.khochinh.*') ? 'active' : '' }}" href="{{ route('quanly.khochinh.duyet') }}">Duyệt kiểm kho chính</a>
                        </li>
                    @elseif ($isEmployee)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('nhanvien.*') ? 'active' : '' }}" href="{{ route('nhanvien.phieuxuat') }}">Phiếu xuất kho</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ds-don-hang.*') ? 'active' : '' }}" href="{{ route('ds-don-hang.index') }}">Danh sách đơn hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kiemke.bep') ? 'active' : '' }}" href="{{ route('kiemke.bep') }}">Kiểm kê cuối ngày</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('khochinh.kiemke') ? 'active' : '' }}" href="{{ route('khochinh.kiemke') }}">Kiểm kê định kỳ</a>
                        </li>
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
        @if ($isStoreChief)
            <div class="top-module-bar d-flex flex-wrap gap-2">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Tổng quan</a>
                <a class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">Đơn hàng</a>
                <a class="{{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu gốc</a>
                <a class="{{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
            </div>
        @elseif ($isManager)
            <div class="top-module-bar d-flex flex-wrap gap-2">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Tổng quan</a>
                <a class="{{ request()->routeIs('don-hang.*') ? 'active' : '' }}" href="{{ route('don-hang.index') }}">Đơn hàng</a>
                <a class="{{ request()->routeIs('xuatkho.*') ? 'active' : '' }}" href="{{ route('xuatkho.index') }}">Xuất kho</a>
                <a class="{{ request()->routeIs('xuat-huy.*') ? 'active' : '' }}" href="{{ route('xuat-huy.index') }}">Xuất hủy</a>
                <a class="{{ request()->routeIs('kiem-ke.*') ? 'active' : '' }}" href="{{ route('kiem-ke.index') }}">Kiểm kê</a>
                <a class="{{ request()->routeIs('giai-trinh.*') ? 'active' : '' }}" href="{{ route('giai-trinh.index') }}">Giải trình</a>
                <a class="{{ request()->routeIs('quanly.kiemke.*') ? 'active' : '' }}" href="{{ route('quanly.kiemke.bep') }}">Duyệt kiểm kê bếp</a>
                <a class="{{ request()->routeIs('quanly.khochinh.*') ? 'active' : '' }}" href="{{ route('quanly.khochinh.duyet') }}">Duyệt kiểm kho chính</a>
            </div>
        @elseif ($isEmployee)
            <div class="top-module-bar d-flex flex-wrap gap-2">
                <a class="{{ request()->routeIs('nhanvien.*') ? 'active' : '' }}" href="{{ route('nhanvien.phieuxuat') }}">Phiếu xuất kho</a>
                <a class="{{ request()->routeIs('ds-don-hang.*') ? 'active' : '' }}" href="{{ route('ds-don-hang.index') }}">Danh sách đơn hàng</a>
                <a class="{{ request()->routeIs('kiemke.bep') ? 'active' : '' }}" href="{{ route('kiemke.bep') }}">Kiểm kê cuối ngày</a>
                <a class="{{ request()->routeIs('khochinh.kiemke') ? 'active' : '' }}" href="{{ route('khochinh.kiemke') }}">Kiểm kê định kỳ</a>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success shadow-sm">{{ session('status') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning shadow-sm">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
