<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lotteria Bà Triệu - Kiểm Kê Định Kỳ Kho Chính</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" style="font-family: 'Times New Roman';">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white text-center py-3">
            <h3>LOTTERIA BÀ TRIỆU - KIỂM KÊ ĐỊNH KỲ KHO CHÍNH</h3>
            <span class="text-warning font-monospace">Dành cho Nhân viên chốt số liệu cuối tuần</span>
        </div>
        <div class="card-body">
            <form action="{{ route('khochinh.kiemke.store') }}" method="POST">
                @csrf
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-secondary">
                        <tr>
                            <th>Mã Lô Hàng</th>
                            <th>Tên Nguyên Liệu Tổng</th>
                            <th>Hạn Sử Dụng (HSD)</th>
                            <th>Trạng Thái Lô (HĐ2)</th>
                            <th style="width: 20%;">Số Lượng Thực Tế Đếm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($phiuKiemKeDienTu as $lo)
                        <tr>
                            <td class="fw-bold font-monospace text-primary">{{ $lo['ma_lo'] }}</td>
                            <td class="text-start">{{ $lo['ten_nl'] }}</td>
                            <td>{{ $lo['hsd'] }}</td>
                            <td>
                                <span class="badge {{ str_contains($lo['canh_bao_hsd'], 'HẾT HẠN') ? 'bg-danger' : (str_contains($lo['canh_bao_hsd'], 'CẬN HẠN') ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $lo['canh_bao_hsd'] }}
                                </span>
                            </td>
                            <td>
                                <input type="number" name="kiem_ke[{{ $lo['ma_lo'] }}][thuc_te]" class="form-control text-center" min="0" placeholder="Nhập số thực đếm..." required>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end">
                    <button type="submit" class="btn btn-danger px-5 fw-bold">Nhấn Hoàn Thành</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
