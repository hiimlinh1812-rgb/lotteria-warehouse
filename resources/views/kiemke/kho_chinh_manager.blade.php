@extends('layouts.app')

@section('content')
<h2 class="mb-4 text-center fw-bold text-dark">🏛️ DUYỆT KIỂM KÊ ĐỊNH KỲ</h2>

@forelse($danhSachPhiu as $phiu)
    <div class="card shadow mb-5 border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Mã Phiếu: <strong class="text-warning font-monospace">{{ $phiu['MaPhieuKiemKe'] }}</strong> | Ngày Kiểm: {{ $phiu['NgayKiemKe'] }}</span>
            <span class="badge {{ $phiu['TrangThai'] == 'Đã duyệt' ? 'bg-success' : 'bg-warning text-dark' }}">Trạng thái: {{ $phiu['TrangThai'] }}</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Lô</th>
                        <th>Sổ Sách Hệ Thống</th>
                        <th>Số Lượng Thực Tế</th>
                        <th>Chênh Lệch Đối Soát</th>
                        <th>Kết Luận Vận Hành</th>
                        <th>Hành Động Hiệu Chỉnh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($phiu['Details'] as $detail)
                    <tr>
                        <td class="font-monospace fw-bold">{{ $detail->MaLoHang }}</td>
                        <td class="fw-bold text-primary">{{ $detail->SoLuongHeThong }}</td>
                        <td class="fw-bold text-dark">{{ $detail->SoLuongThucTe }}</td>
                        <td class="fw-bold {{ $detail->ChenhLech < 0 ? 'text-danger' : ($detail->ChenhLech > 0 ? 'text-warning' : 'text-success') }}">
                            {{ $detail->ChenhLech > 0 ? '+'.$detail->ChenhLech : $detail->ChenhLech }}
                        </td>
                        <td>
                            <span class="badge {{ $detail->TinhTrang == 'Khớp' ? 'bg-success' : ($detail->TinhTrang == 'Thừa hàng' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ $detail->TinhTrang }}
                            </span>
                        </td>
                        <td>
                            @if(($detail->isEdited ?? false) || $phiu['TrangThai'] !== 'Chờ duyệt')
                                <span class="text-muted small">✓ Đã hiệu chỉnh</span>
                            @else
                                <form action="{{ route('quanly.khochinh.hieuchinh', $phiu['MaPhieuKiemKe']) }}" method="POST" class="d-flex justify-content-center gap-1">
                                    @csrf
                                    <input type="hidden" name="ma_lo" value="{{ $detail->MaLoHang }}">
                                    <input type="number" name="thuc_te_moi" class="form-control form-control-sm text-center" style="width: 70px;" value="{{ $detail->SoLuongThucTe }}" min="0" required>
                                    <button type="submit" class="btn btn-warning btn-sm fw-bold">Sửa lô</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- ĐÃ FIX: Chuyển đổi class để không bao giờ bị JavaScript tự động ẩn xóa mất -->
            @if($phiu['GiaiTrinh'])
                <div class="p-3 bg-light border-top">
                    <div class="bg-info-subtle text-dark border-start border-4 border-info p-3 mb-0 small shadow-sm rounded-end">
                        📌 <strong>Hồ sơ giải trình đính kèm:</strong> 
                        <span class="fw-bold text-dark">{{ $phiu['GiaiTrinh']->NoiDung }}</span> | Nguyên nhân: <span class="text-danger fw-bold">{{ $phiu['GiaiTrinh']->NguyenNhan }}</span>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="card-footer bg-white text-end p-3">
            @if($phiu['TrangThai'] == 'Chờ duyệt')
                @if(!$phiu['biLech'])
                    <form action="{{ route('quanly.khochinh.duyetXacNhan', $phiu['MaPhieuKiemKe']) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-4 shadow-sm">
                            ✓ Duyệt & Gửi Phiếu Thống Kê Cho Cửa Hàng Trưởng
                        </button>
                    </form>
                @else
                    <form action="{{ route('quanly.khochinh.chuyenHuongGiaiTrinh', $phiu['MaPhieuKiemKe']) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm fw-bold px-4 shadow-sm">
                            ⚠️ Duyệt Chốt & Tiến Hành Tạo Phiếu Giải Trình Thất Thoát
                        </button>
                    </form>
                @endif
            @elseif($phiu['TrangThai'] == 'Đã duyệt')
                <span class="text-success fw-bold">✓ Phiếu thống kê định kỳ đã gửi lên Cửa hàng trưởng thành công</span>
            @endif
        </div>
    </div>
@empty
    <div class="card shadow border-0 p-5 text-center my-4 bg-white">
        <div class="text-center mb-3"><span class="fs-1">📦</span></div>
        <h5 class="fw-bold text-secondary">Hiện tại chưa có phiếu kiểm kê định kỳ nào cần phê duyệt!</h5>
    </div>
@endforelse

<!-- SCRIPT TỰ ĐỘNG ẨN FLASH ALERT (CHỈ XÓA THÔNG BÁO HỆ THỐNG, KHÔNG XÓA DỮ LIỆU) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            // Chỉ quét các alert thông báo hệ thống nếu có xuất hiện ngoài card dữ liệu
            let systemAlerts = document.querySelectorAll('.alert-dismissible, .session-alert');
            systemAlerts.forEach(function(alert) {
                alert.style.transition = "opacity 0.5s ease-out";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            });
        }, 30000); 
    });
</script>
@endsection
