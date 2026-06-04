<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Duyệt Kiểm Kho Chính - Lotteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" style="font-family: 'Times New Roman', Times, serif;">
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold text-secondary">📊 TRANG DUYỆT ĐỐI SOÁT KHO CHÍNH (QUẢN LÝ)</h2>

    @foreach($danhSachPhiu as $phiu)
        <div class="card shadow mb-5">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span>Mã Phiếu: <strong class="text-warning font-monospace">{{ $phiu['MaPhieuKiemKe'] }}</strong> | Ngày Kiểm: {{ $phiu['NgayKiemKe'] }}</span>
                <span class="badge {{ $phiu['TrangThai'] == 'Đã duyệt' ? 'bg-success' : 'bg-warning text-dark' }}">Trạng thái: {{ $phiu['TrangThai'] }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Lô</th>
                            <th>Số Sổ Sách Hệ Thống</th>
                            <th>Số Lượng Thực Tế</th>
                            <th>Chênh Lệch Đối Soát</th>
                            <th>Kết Luận Vận Hành</th>
                            <th style="width: 25%;">Hành Động Hiệu Chỉnh (HĐ10)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($phiu['Details'] as $d)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $d->MaLoHang }}</td>
                            <td class="table-primary fw-bold text-primary">{{ $d->SoLuongHeThong }}</td>
                            <td class="table-warning fw-bold">{{ $d->SoLuongThucTe }}</td>
                            <td class="fw-bold {{ $d->ChenhLech < 0 ? 'text-danger' : ($d->ChenhLech > 0 ? 'text-warning' : 'text-success') }}">
                                {{ $d->ChenhLech }}
                            </td>
                            <td>
                                <span class="badge {{ $d->TinhTrang == 'Khớp' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $d->TinhTrang }}
                                </span>
                            </td>
                            <td>
                                @if($d->isEdited)
                                    <span class="text-secondary fw-bold text-decoration-underline">Đã hiệu chỉnh</span>
                                @elseif($d->ChenhLech != 0 && $phiu['TrangThai'] == 'Chờ duyệt')
                                    <form action="{{ route('quanly.khochinh.hieuchinh', $phiu['MaPhieuKiemKe']) }}" method="POST" class="d-flex justify-content-center p-1">
                                        @csrf
                                        <input type="hidden" name="ma_lo" value="{{ $d->MaLoHang }}">
                                        <input type="number" name="thuc_te_moi" class="form-control form-control-sm me-1 text-center" style="width: 90px;" placeholder="Số đúng" required>
                                        <button type="submit" class="btn btn-warning btn-sm fw-bold">Sửa lô</button>
                                    </form>
                                @else
                                    <span class="text-muted small">✓ Ổn định</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white p-3">
                @if($phiu['biLech'] && $phiu['TrangThai'] == 'Chờ duyệt')
                    <div class="p-2 border rounded bg-light shadow-sm">
                        <span class="badge bg-danger mb-2">📄 LẬP PHIẾU GIẢI TRÌNH THẤT THOÁT KHO CHÍNH (HĐ11)</span>
                        <form action="{{ route('quanly.khochinh.giaitrinh', $phiu['MaPhieuKiemKe']) }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-md-5">
                                <input type="text" name="noi_dung" class="form-control form-control-sm" placeholder="Nhập nội dung giải trình..." required>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="nguyen_nhan" class="form-control form-control-sm" placeholder="Nhập nguyên nhân thất thoát..." required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger btn-sm fw-bold w-100">Gửi giải trình</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="text-end text-success fw-bold py-1">✓ Số liệu toàn kho chính xác và khớp vật lý. Tiến trình kiểm kê định kỳ hoàn tất!</div>
                @endif
                
                @if($phiu['GiaiTrinh'])
                    <div class="alert alert-danger mb-0 mt-2 py-2 small shadow-sm">
                        🚨 <strong>Phiếu giải trình đính kèm hệ thống:</strong> {{ $phiu['GiaiTrinh']->MaPhieuGiaiTrinh }} | <strong>Lý do ghi nhận:</strong> {{ $phiu['GiaiTrinh']->NguyenNhan }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
</body>
</html>