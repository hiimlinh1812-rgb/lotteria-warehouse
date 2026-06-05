<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản lý Nhập kho') – Lotteria Bà Triệu</title>
    <link rel="stylesheet" href="{{ asset('css/nhap-kho.css') }}">
    @stack('styles')
</head>
<body>

{{-- Thanh điều hướng do thành viên khác đảm nhiệm --}}
@include('layouts.partials.navbar')     {{-- placeholder --}}

<main style="padding: 24px 32px; max-width: 1280px; margin: 0 auto;">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <span>✔</span> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">
            <span>⚠</span> {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <span>✖</span> {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

<script src="{{ asset('js/nhap-kho.js') }}"></script>
@stack('scripts')
</body>
</html>
