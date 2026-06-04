<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lotteria Bà Triệu - Quản Lý Phê Duyệt Bếp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Times New Roman', Times, serif; }
        .phieu-huy-box { background-color: #fffdf0; border: 1px solid #ffcc00; border-radius: 6px; padding: 15px; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold text-dark">📊 TRANG QUẢN TRỊ KHO BẾP - LOTTERIA BÀ TRIỆU</h2>

    @foreach($danhSachPhiu as $phiu)
        <div class="card shadow mb-5 border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span>Mã Phiếu Kiểm: <strong class="text-warning font-monospace">{{ $phiu['MaPhieuKiemKe'] }}</strong> | Ngày Lập: {{ $phiu['NgayKiemKe'] }}</span>
                <span class="badge bg-warning text-dark">Trạng thái: {{ $phiu['TrangThai'] }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Nguyên Liệu</th>
                            <th>Tên Nguyên Liệu</th>
                            <th>Tồn Đầu</th>
                            <th>Nhập Kho</th>
                            <th>Xuất Ca</th>
                            <th class="table-secondary">Sổ Sách Hệ Thống</th>
                            <th class="table-warning">Thực Tế Đếm</th>
                            <th>Chênh Lệch</th>
                            <th>Kết Luận</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($phiu['Details'] as $detail)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $detail['MaNguyenLieu'] }}</td>
                            <td class="text-start fw-bold text-secondary">{{ $detail['TenNguyenLieu'] }}</td>
                            <td>{{ $detail['TonDau'] }}</td>
                            <td>{{ $detail['Nhap'] }}</td>
                            <td>{{ $detail['Xuat'] }}</td>
                            <td class="table-secondary fw-bold text-primary">{{ $detail['SoLuongHeThong'] }}</td>
                            <td class="table-warning fw-bold text-dark">{{ $detail['SoLuongThucTe'] }}</td>
                            <td class="fw-bold {{ $detail['ChenhLech'] < 0 ? 'text-danger' : ($detail['ChenhLech'] > 0 ? 'text-warning' : 'text-success') }}">
                                {{ $detail['ChenhLech'] > 0 ? '+'.$detail['ChenhLech'] : $detail['ChenhLech'] }}
                            </td>
                            <td>
                                <span class="badge {{ $detail['TinhTrang'] == 'Khớp' ? 'bg-success' : ($detail['TinhTrang'] == 'Thừa hàng' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $detail['TinhTrang'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($phiu['PhieuHuy'])
                    <div class="p-3 bg-light border-top">
                        <div class="phieu-huy-box shadow-sm">
                            <h6 class="text-danger fw-bold mb-3">📄 CHI TIẾT PHIẾU XUẤT HỦY ĐÍNH KÈM: <span class="text-dark font-monospace">{{ $phiu['PhieuHuy']->MaPhieuHuy }}</span></h6>
                            <table class="table table-sm table-bordered bg-white text-center mb-0">
                                <thead class="table-danger small text-dark">
                                    <tr>
                                        <th>Mã Mặt Hàng</th>
                                        <th>Tên Nguyên Liệu Hủy</th>
                                        <th style="width: 20%;">Số Lượng Tiêu Hủy</th>
                                        <th style="width: 40%;">Lý Do Tiêu Hủy Chi Tiết</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    @foreach($phiu['PhieuHuyDetails'] as $huy)
                                    <tr>
                                        <td class="font-monospace fw-bold">{{ $huy['MaNguyenLieu'] }}</td>
                                        <td class="text-start">{{ $huy['TenNguyenLieu'] }}</td>
                                        <td class="text-danger fw-bold">{{ $huy['SoLuongHuy'] }}</td>
                                        <td class="text-start text-muted italic">📌 {{ $huy['LyDo'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="card-footer bg-white text-end p-3">
                @if($phiu['TrangThai'] == 'Chờ duyệt')
                    <form action="{{ route('quanly.chotca', $phiu['MaPhieuKiemKe']) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm px-4 fw-bold">Xác Nhận & Chốt Ca Bếp</button>
                    </form>
                @else
                    <span class="text-success fw-bold">✓ Toàn bộ phân hệ đã được phê duyệt thành công</span>
                @endif
            </div>
        </div>
    @endforeach
</div>
</body>
</html>