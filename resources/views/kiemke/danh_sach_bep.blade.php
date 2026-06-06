@extends('layouts.app')

@section('title', 'Duyệt kiểm kê cuối ngày')

@php
    $statusClasses = [
        'Chờ duyệt' => 'bg-warning text-dark',
        'Từ chối' => 'bg-danger',
        'Đã duyệt' => 'bg-success',
    ];
@endphp

@section('content')
<style>
    .bep-review-shell { display: flex; flex-direction: column; gap: 1.5rem; }
    .bep-review-card { border: 0; border-radius: 1rem; overflow: hidden; box-shadow: 0 0.35rem 1.2rem rgba(15, 23, 42, 0.12); }
    .bep-review-header { background: #252934; color: #fff; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .bep-review-code { font-size: 1.05rem; font-weight: 800; color: #fff; }
    .bep-review-meta { color: rgba(255, 255, 255, 0.92); }
    .bep-review-table thead th { background: #f8fafc; text-align: center; vertical-align: middle; border-color: #d9dee7; font-weight: 700; white-space: nowrap; }
    .bep-review-table tbody td { vertical-align: middle; border-color: #e5e7eb; text-align: center;}
    .bep-review-table .name-col { text-align: left; font-weight: 600; }
    .waste-box { border: 2px solid #f1d96f; border-radius: 0.9rem; background: #fffdf2; padding: 1rem; }
    .waste-title { color: #c2410c; font-weight: 800; margin-bottom: 0.85rem; }
    .waste-table thead th { background: #fde2e2; border-color: #f1c9c9; font-weight: 700; }
    .review-actions { border-top: 1px solid #eceff3; padding: 1rem 1.25rem 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; }
    .review-action-row { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; }
    .review-reject-form { flex: 1 1 560px; }
    .review-note { min-width: 320px; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Duyệt báo cáo kiểm kê cuối ngày (Bếp)</h2>
    </div>
</div>

@if(empty($danhSachPhiu))
    <div class="card page-card">
        <div class="card-body text-center text-muted py-5">Chưa có báo cáo kiểm kê cuối ngày nào để hiển thị.</div>
    </div>
@else
    <div class="bep-review-shell">
        @foreach($danhSachPhiu as $phieu)
            <div class="card bep-review-card">
                <div class="bep-review-header">
                    <div>
                        <div class="bep-review-code">
                            Mã phiếu: <span class="text-warning">{{ $phieu['MaPhieuKiemKe'] }}</span>
                            <span class="fw-normal bep-review-meta">| Ngày lập: {{ \Carbon\Carbon::parse($phieu['NgayKiemKe'])->format('Y-m-d') }}</span>
                            @if(!empty($phieu['NhanVienLap']))
                                <span class="fw-normal bep-review-meta">| Nhân viên: {{ $phieu['NhanVienLap'] }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $statusClasses[$phieu['TrangThai']] ?? 'bg-secondary' }}">Trạng thái: {{ $phieu['TrangThai'] }}</span>
                </div>

                <div class="card-body p-0">
                    <form action="{{ route('quanly.chotca', $phieu['MaPhieuKiemKe']) }}" method="POST" id="form-chotca-{{ $phieu['MaPhieuKiemKe'] }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table bep-review-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã NL</th>
                                        <th class="text-start">Tên Nguyên Liệu</th>
                                        <th class="bg-light text-primary">Kho Tổng Xuất</th>
                                        <th class="bg-warning bg-opacity-25">Bếp Báo Hủy</th>
                                        <th class="bg-success bg-opacity-25">Bếp Hoàn Kho</th>
                                        <th>Kết Luận (Quản lý chọn)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($phieu['Details'] as $detail)
                                        <tr>
                                            <td class="fw-bold">{{ $detail['MaNguyenLieu'] }}</td>
                                            <td class="name-col">{{ $detail['TenNguyenLieu'] }}</td>
                                            <td class="fw-bold text-primary fs-5">{{ $detail['XuatTrongNgay'] }}</td>
                                            <td class="fw-bold text-danger">{{ $detail['HangHuy'] }}</td>
                                            <td class="fw-bold text-success fs-5">{{ $detail['ThucTeDem'] }}</td>
                                            
                                            <td style="min-width: 220px;">
                                                @if($phieu['TrangThai'] === 'Chờ duyệt')
                                                    <div class="d-flex gap-3 justify-content-center">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="ket_luan[{{ $detail['MaNguyenLieu'] }}]" id="khop_{{ $phieu['MaPhieuKiemKe'] }}_{{ $detail['MaNguyenLieu'] }}" value="Khớp" required>
                                                            <label class="form-check-label text-success fw-bold" for="khop_{{ $phieu['MaPhieuKiemKe'] }}_{{ $detail['MaNguyenLieu'] }}">Khớp</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="ket_luan[{{ $detail['MaNguyenLieu'] }}]" id="lech_{{ $phieu['MaPhieuKiemKe'] }}_{{ $detail['MaNguyenLieu'] }}" value="Lệch" required>
                                                            <label class="form-check-label text-danger fw-bold" for="lech_{{ $phieu['MaPhieuKiemKe'] }}_{{ $detail['MaNguyenLieu'] }}">Không Khớp</label>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">Đã xử lý</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($phieu['PhieuHuy'])
                            <div class="p-3 pt-4">
                                <div class="waste-box">
                                    <div class="waste-title">CHI TIẾT PHIẾU XUẤT HỦY ĐÍNH KÈM: {{ $phieu['PhieuHuy']->MaPhieuHuy }}</div>
                                    <div class="table-responsive">
                                        <table class="table waste-table table-sm align-middle mb-0 bg-white text-center">
                                            <thead>
                                                <tr>
                                                    <th>Mã Mặt Hàng</th>
                                                    <th class="text-start">Tên Nguyên Liệu Hủy</th>
                                                    <th>Số Lượng Tiêu Hủy</th>
                                                    <th class="text-start">Lý Do Tiêu Hủy Chi Tiết</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($phieu['PhieuHuyDetails'] as $huy)
                                                    <tr>
                                                        <td class="fw-bold">{{ $huy['MaNguyenLieu'] }}</td>
                                                        <td class="text-start">{{ $huy['TenNguyenLieu'] }}</td>
                                                        <td class="text-danger fw-bold fs-6">{{ $huy['SoLuongHuy'] }}</td>
                                                        <td class="text-start">{{ $huy['LyDo'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>

                    @if($phieu['GhiChu'])
                        <div class="px-3 pb-3">
                            <div class="alert alert-light border mb-0">
                                <div class="fw-bold mb-1">Ghi chú từ chối trước đó:</div>
                                <div class="text-danger">{{ $phieu['GhiChu'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="review-actions">
                    @if($phieu['TrangThai'] === 'Chờ duyệt')
                        <div class="review-action-row">
                            <form action="{{ route('quanly.kiemke.tuchoi', $phieu['MaPhieuKiemKe']) }}" method="POST" class="review-reject-form">
                                @csrf
                                <div class="d-flex gap-2 flex-wrap">
                                    <input type="text" name="ghi_chu_tu_choi" class="form-control review-note" placeholder="Nhập lý do từ chối (bắt buộc nếu không khớp)..." required>
                                    <button type="submit" class="btn btn-outline-danger px-4 fw-bold">Từ chối & Yêu cầu làm lại</button>
                                </div>
                            </form>

                            <div class="text-end">
                                <button type="submit" form="form-chotca-{{ $phieu['MaPhieuKiemKe'] }}" class="btn btn-success px-4 fw-bold">Duyệt & Chốt Ca</button>
                            </div>
                        </div>
                    @elseif($phieu['TrangThai'] === 'Từ chối')
                        <div class="text-danger fw-semibold">Báo cáo đã bị từ chối. Đang chờ bếp làm lại báo cáo mới.</div>
                    @elseif($phieu['TrangThai'] === 'Đã duyệt')
                        <div class="text-success fw-semibold">Báo cáo đã được quản lý xác nhận khớp và chốt ca thành công.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chotCaForms = document.querySelectorAll('form[id^="form-chotca-"]');
        
        chotCaForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                // Tạm thời chặn form gửi đi ngay lập tức để hiển thị popup
                e.preventDefault(); 

                const lechRadios = form.querySelectorAll('input[type="radio"][value="Lệch"]:checked');
                
                if (lechRadios.length > 0) {
                    // Popup báo lỗi khi có ít nhất 1 dòng đánh giá Lệch
                    Swal.fire({
                        icon: 'error',
                        title: 'Phát hiện lệch số liệu!',
                        text: 'Bạn đã đánh giá có nguyên liệu KHÔNG KHỚP. Vui lòng nhập lý do và bấm nút "TỪ CHỐI & YÊU CẦU LÀM LẠI" ở bên dưới thay vì Duyệt chốt ca!',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Đã hiểu'
                    });
                } else {
                    // Popup xác nhận chốt ca tuyệt đẹp
                    Swal.fire({
                        title: 'Xác nhận duyệt báo cáo?',
                        text: "Bạn xác nhận số lượng thực tế Bếp báo cáo hoàn toàn khớp với kiểm tra thực tế? Số lượng hoàn kho sẽ được cộng thẳng vào tồn kho hệ thống.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754', // Màu xanh success Bootstrap
                        cancelButtonColor: '#6c757d', // Màu xám secondary Bootstrap
                        confirmButtonText: '✓ Vâng, duyệt và chốt ca!',
                        cancelButtonText: 'Hủy bỏ'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Nếu Quản lý bấm Vâng thì mới thực sự submit gửi về Controller
                            form.submit(); 
                        }
                    });
                }
            });
        });
    });
</script>
@endsection