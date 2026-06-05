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
        .app-main-tabs {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            overflow-x: auto;
            overflow-y: hidden;
            flex: 1 1 auto;
            scrollbar-width: thin;
            padding-bottom: 0.15rem;
        }
        .app-main-tabs::-webkit-scrollbar {
            height: 8px;
        }
        .app-main-tabs .nav-link {
            border-radius: 0.55rem;
            padding: 0.65rem 0.95rem;
            white-space: nowrap;
            flex: 0 0 auto;
            color: rgba(255,255,255,0.82);
        }
        .app-main-tabs .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.12);
        }
        .app-main-tabs .nav-link.active {
            font-weight: 700;
            background-color: rgba(255,255,255,0.18);
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
        $menuItems = [];

        if ($isStoreChief) {
            $menuItems = [
                ['label' => 'Tổng quan', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Đơn hàng', 'route' => route('purchase-orders.index'), 'active' => request()->routeIs('purchase-orders.*')],
                ['label' => 'Phiếu xuất hủy', 'route' => route('xuat-huy.index'), 'active' => request()->routeIs('xuat-huy.*')],
                ['label' => 'Nguyên liệu gốc', 'route' => route('nguyen-lieu.index'), 'active' => request()->routeIs('nguyen-lieu.*')],
                ['label' => 'Tài khoản', 'route' => route('tai-khoan.index'), 'active' => request()->routeIs('tai-khoan.*')],
            ];
        } elseif ($isManager) {
            $menuItems = [
                ['label' => 'Tổng quan', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Đơn hàng', 'route' => route('don-hang.index'), 'active' => request()->routeIs('don-hang.*')],
                ['label' => 'Xuất kho', 'route' => route('xuatkho.index'), 'active' => request()->routeIs('xuatkho.*')],
                ['label' => 'Xuất hủy', 'route' => route('xuat-huy.index'), 'active' => request()->routeIs('xuat-huy.*')],
                ['label' => 'Kiểm kê', 'route' => route('kiem-ke.index'), 'active' => request()->routeIs('kiem-ke.*')],
                ['label' => 'Duyệt kiểm kê bếp', 'route' => route('quanly.kiemke.bep'), 'active' => request()->routeIs('quanly.kiemke.*')],
                ['label' => 'Duyệt kiểm kê định kỳ', 'route' => route('quanly.khochinh.duyet'), 'active' => request()->routeIs('quanly.khochinh.*')],
            ];
        } elseif ($isEmployee) {
            $menuItems = [
                ['label' => 'Phiếu xuất kho', 'route' => route('nhanvien.phieuxuat'), 'active' => request()->routeIs('nhanvien.*')],
                ['label' => 'Danh sách đơn hàng', 'route' => route('ds-don-hang.index'), 'active' => request()->routeIs('ds-don-hang.*')],
                ['label' => 'Kiểm kê cuối ngày', 'route' => route('kiemke.bep'), 'active' => request()->routeIs('kiemke.bep', 'kiem-ke-ngay.index')],
                ['label' => 'Kiểm kê định kỳ', 'route' => route('khochinh.kiemke'), 'active' => request()->routeIs('khochinh.kiemke', 'kiem-ke-dinh-ky.index')],
            ];
        } elseif (auth()->check()) {
            $menuItems = [
                ['label' => 'Danh sách đơn hàng', 'route' => route('purchase-orders.index'), 'active' => request()->routeIs('purchase-orders.*')],
            ];
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark app-shell-nav mb-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="{{ auth()->check() ? url('/') : route('login') }}">Lotteria Kho</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse gap-3" id="navbarNav">
                @if (! empty($menuItems))
                    <div class="app-main-tabs">
                        @foreach ($menuItems as $item)
                            <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['route'] }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                @endif

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

    <div class="container-fluid px-4 pb-4">
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
