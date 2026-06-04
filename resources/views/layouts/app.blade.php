<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lotteria Warehouse')</title>
    <style>
        :root {
            color-scheme: light;
            --red: #c5161d;
            --red-dark: #9f1117;
            --ink: #1f2933;
            --muted: #667085;
            --line: #d9dee7;
            --soft: #f5f7fb;
            --white: #ffffff;
            --green: #0f8a4b;
            --amber: #a16207;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--soft);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }
        a { color: inherit; text-decoration: none; }
        .app-shell { min-height: 100vh; display: grid; grid-template-columns: 248px 1fr; }
        .sidebar { background: #20242c; color: #fff; padding: 22px 18px; }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-weight: 700; font-size: 18px; }
        .brand-mark { width: 36px; height: 36px; border-radius: 6px; background: var(--red); display: grid; place-items: center; font-weight: 800; }
        .nav-link { display: block; padding: 11px 12px; border-radius: 6px; color: #d6dae3; margin-bottom: 6px; }
        .nav-link.active, .nav-link:hover { background: #303642; color: #fff; }
        .nav-footer { margin-top: 18px; border-top: 1px solid #3a4250; padding-top: 14px; color: #d6dae3; }
        .main { padding: 24px 30px 42px; min-width: 0; }
        .topbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
        h1 { font-size: 26px; margin: 0 0 6px; letter-spacing: 0; }
        .subtle { color: var(--muted); margin: 0; line-height: 1.45; }
        .panel { background: var(--white); border: 1px solid var(--line); border-radius: 8px; padding: 18px; margin-bottom: 18px; }
        .toolbar { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; }
        .field { display: grid; gap: 6px; }
        label { color: #364152; font-weight: 700; font-size: 13px; }
        input, select, textarea {
            border: 1px solid #cfd6e1;
            border-radius: 6px;
            padding: 10px 11px;
            min-height: 40px;
            font: inherit;
            background: #fff;
            color: var(--ink);
        }
        textarea { min-height: 82px; resize: vertical; }
        .btn {
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 10px 14px;
            min-height: 40px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            background: #fff;
        }
        .btn-primary { background: var(--red); color: #fff; }
        .btn-primary:hover { background: var(--red-dark); }
        .btn-secondary { border-color: #cfd6e1; color: #293241; }
        .btn-danger { background: #b42318; color: #fff; }
        .btn-success { background: var(--green); color: #fff; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)); gap: 12px; }
        .stat { border: 1px solid var(--line); border-radius: 8px; padding: 14px; background: #fff; }
        .stat strong { display: block; font-size: 24px; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e6e9ef; text-align: left; vertical-align: top; }
        th { color: #4b5565; font-size: 12px; text-transform: uppercase; letter-spacing: .02em; background: #fafbfc; }
        tr:hover td { background: #fff8f8; }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 9px; font-weight: 700; font-size: 12px; background: #eef2f6; color: #344054; white-space: nowrap; }
        .badge.approved { background: #dcfae6; color: #067647; }
        .badge.pending { background: #fef7c3; color: #854a0e; }
        .badge.rejected { background: #fee4e2; color: #b42318; }
        .badge.processing { background: #e0f2fe; color: #075985; }
        .badge.received { background: #ece9fe; color: #5b21b6; }
        .alert { border-radius: 6px; padding: 12px 14px; margin-bottom: 16px; border: 1px solid transparent; }
        .alert-success { background: #ecfdf3; border-color: #abefc6; color: #067647; }
        .alert-warning { background: #fffaeb; border-color: #fedf89; color: #93370d; }
        .alert-error { background: #fef3f2; border-color: #fecdca; color: #b42318; }
        .muted-link { color: #d6dae3; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
        .item-row { display: grid; grid-template-columns: minmax(220px, 1fr) 140px 44px; gap: 10px; align-items: end; margin-bottom: 10px; }
        .icon-btn { width: 40px; padding: 0; border-color: #cfd6e1; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .pagination { margin-top: 14px; }
        .pagination nav > div:first-child { display: none; }
        .pagination a, .pagination span { margin-right: 6px; }
        @media (max-width: 860px) {
            .app-shell { grid-template-columns: 1fr; }
            .sidebar { position: static; }
            .main { padding: 18px; }
            .topbar, .grid-2, .grid-3, .item-row { grid-template-columns: 1fr; display: grid; }
            .stats { grid-template-columns: 1fr 1fr; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">L</div>
                <div>Lotteria Warehouse</div>
            </div>
            <nav>
                <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">Đặt hàng & phê duyệt</a>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="nav-link {{ request()->routeIs('nguyen-lieu.*') ? 'active' : '' }}" href="{{ route('nguyen-lieu.index') }}">Nguyên liệu</a>
                <a class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}" href="{{ route('tai-khoan.index') }}">Tài khoản</a>
            </nav>
            <div class="nav-footer">
                @auth
                    <div>{{ auth()->user()->HoTen }}</div>
                    <div style="margin-top: 4px; color: #98a2b3;">{{ auth()->user()->VaiTro }}</div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-top: 12px;">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="width: 100%;">Đăng xuất</button>
                    </form>
                @else
                    <a class="muted-link" href="{{ route('login') }}">Đăng nhập</a>
                @endauth
            </nav>
        </aside>

        <main class="main">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
