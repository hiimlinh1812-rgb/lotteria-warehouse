<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hệ Thống Quản Lý Kho - Lotteria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Tùy chỉnh màu chủ đạo của Lotteria */
        .bg-lotteria { background-color: #a52a2a !important; color: white; }
        .text-lotteria { color: #a52a2a !important; }
        .btn-lotteria { background-color: #a52a2a; color: white; }
        .btn-lotteria:hover { background-color: #8b2323; color: white; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-lotteria mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">🍔 Quản Lý Kho Lotteria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>