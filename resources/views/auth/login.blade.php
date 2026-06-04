<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Quản lý kho Lotteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-toggle {
            position: absolute; 
            right: 15px; 
            top: 40px; 
            cursor: pointer; 
            color: #6c757d;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card shadow-lg" style="width: 25rem; border-top: 5px solid #dc3545;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h3 class="text-danger fw-bold">LOTTERIA KHO</h3>
                <p class="text-muted">Đăng nhập hệ thống quản lý</p>
            </div>
            
            @if($errors->any())
                <div class="alert alert-danger text-center">{{ $errors->first() }}</div>
            @endif
            
            <form action="/login" method="POST">
                @csrf 
                
                <div class="mb-3">
                    <label for="SoDienThoai" class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" class="form-control" id="SoDienThoai" name="SoDienThoai" 
                           placeholder="Nhập số điện thoại..." required>
                </div>
                
                <div class="mb-4 position-relative">
                    <label for="MatKhau" class="form-label fw-semibold">Mật khẩu</label>
                    <input type="password" class="form-control pe-5" id="MatKhau" name="MatKhau" 
                           placeholder="Nhập mật khẩu..." required>
                    
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fa fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                
                <button type="submit" class="btn btn-danger w-100 fw-bold">ĐĂNG NHẬP</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("MatKhau");
            const icon = document.getElementById("toggleIcon");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>