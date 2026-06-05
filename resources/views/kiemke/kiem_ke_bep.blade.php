@extends('layouts.app')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-warning text-danger text-center py-3">
        <h3 class="mb-0 fw-bold">📊 LOTTERIA BÀ TRIỆU - PHÂN HỆ KIỂM KÊ BẾP</h3>
    </div>
    <div class="card-body">
        
        <!-- ============================================================ -->
        <!-- ĐÃ CẬP NHẬT CẢNH BÁO VÀ LÝ DO TỪ CHỐI THEO YÊU CẦU CỦA LINH -->
        <!-- ============================================================ -->
        @if($phieuNhap)
            <div class="alert alert-danger border-start border-4 border-danger mb-4 shadow-sm bg-white">
                <div class="d-flex align-items-center mb-2">
                    <span class="fs-4 me-2">⚠️</span>
                    <h6 class="mb-0 fw-bold text-danger">
                        CẢNH BÁO: Báo cáo cuối ngày của bạn đã bị Quản lý từ chối duyệt. Vui lòng kiểm đếm thực tế, điều chỉnh lại số liệu chính xác dưới đây và gửi lại báo cáo!
                    </h6>
                </div>
                <div class="p-3 bg-light rounded border border-danger-subtle">
                    <p class="mb-0 text-dark font-monospace small">
                        📌 <strong>Lý do từ chối cụ thể từ Quản lý:</strong> 
                        <span class="text-danger fw-bold italic">"{{ $phieuNhap->GhiChu ?? 'Số liệu kiểm đếm thực tế có sai lệch với đối soát hệ thống ca trực.' }}"</span>
                    </p>
                </div>
            </div>
        @endif
        <!-- ============================================================ -->

        <form action="{{ route('kiemke.bep.store') }}" method="POST">
            @csrf
            @if($phieuNhap)
                <input type="hidden" name="ma_phieu_cu" value="{{ $phieuNhap->MaPhieuKiemKe }}">
            @endif

            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mã Nguyên Liệu</th>
                        <th>Tên Nguyên Liệu</th>
                        <th class="table-warning text-dark" style="width: 20%;">Số Lượng Hoàn Kho</th>
                        <th class="table-warning text-dark" style="width: 20%;">Số Lượng Hàng Hủy</th>
                        <th class="table-warning text-dark" style="width: 30%;">Lý Do Tiêu Hủy (Bắt buộc nếu có lượng hủy)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nguyenLieuForm as $nl)
                    <tr>
                        <td class="font-monospace fw-bold">{{ $nl['ma_nl'] }}</td>
                        <td class="text-start fw-bold text-secondary">{{ $nl['ten_nl'] }}</td>
                        <td>
                            <input type="number" name="kiem_ke[{{ $nl['ma_nl'] }}][hoan_kho]" class="form-control text-center form-control-sm" min="0" value="{{ $nl['old_hoan_kho'] }}" required>
                        </td>
                        <td>
                            <input type="number" name="kiem_ke[{{ $nl['ma_nl'] }}][hang_huy]" class="form-control text-center form-control-sm hang-huy-input" min="0" value="0" data-target="{{ $nl['ma_nl'] }}" required>
                        </td>
                        <td>
                            <select name="kiem_ke[{{ $nl['ma_nl'] }}][ly_do_huy]" id="ly_do_{{ $nl['ma_nl'] }}" class="form-select form-select-sm">
                                <option value="">-- Không có hủy --</option>
                                <option value="Quá hạn sử dụng ca trực">Quá hạn sử dụng </option>
                                <option value="Hư hỏng do nhiệt độ bếp">Hư hỏng do nhiệt độ bếp</option>
                                <option value="Rơi vãi / Biến dạng vật lý">Rơi vãi / Biến dạng vật lý</option>
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-danger px-5 fw-bold shadow-sm">Gửi Báo Cáo Chốt Ca Bếp</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.hang-huy-input').forEach(input => {
            input.addEventListener('input', function() {
                const maNL = this.getAttribute('data-target');
                const selectLyDo = document.getElementById('ly_do_' + maNL);
                if (parseInt(this.value) > 0) {
                    selectLyDo.setAttribute('required', 'required');
                    selectLyDo.classList.add('is-invalid');
                } else {
                    selectLyDo.removeAttribute('required');
                    selectLyDo.classList.remove('is-invalid');
                    selectLyDo.value = "";
                }
            });
        });
    });
</script>
@endsection