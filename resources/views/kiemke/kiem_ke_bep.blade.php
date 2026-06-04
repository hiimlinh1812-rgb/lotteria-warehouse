<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lotteria Bà Triệu - Lập Báo Cáo Kiểm Kê Bếp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" style="font-family: 'Times New Roman', Times, serif;">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-danger text-center py-3">
            <h3 class="mb-0 fw-bold">LOTTERIA BÀ TRIỆU - KIỂM KÊ BẾP CUỐI NGÀY</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('kiemke.bep.store') }}" method="POST">
                @csrf
                @if(isset($phieuNhap))
                    <input type="hidden" name="ma_phieu_cu" value="{{ $phieuNhap->MaPhieuKiemKe }}">
                @endif

                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã NL</th>
                            <th>Tên Nguyên Liệu</th>
                            <th class="table-warning text-dark">Hoàn Kho</th>
                            <th class="table-warning text-dark">Hàng Hủy</th>
                            <th class="table-warning text-dark">Lý Do Hủy (Bắt buộc nếu có lượng hủy)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nguyenLieuForm as $nl)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $nl['ma_nl'] }}</td>
                            <td class="text-start">{{ $nl['ten_nl'] }}</td>
                            <td>
                                <input type="number" name="kiem_ke[{{ $nl['ma_nl'] }}][hoan_kho]" class="form-control text-center form-control-sm" min="0" value="{{ $nl['old_hoan_kho'] > 0 ? $nl['old_hoan_kho'] : '0' }}" required>
                            </td>
                            <td>
                                <input type="number" name="kiem_ke[{{ $nl['ma_nl'] }}][hang_huy]" class="form-control text-center form-control-sm hang-huy-input" min="0" value="0" data-target="{{ $nl['ma_nl'] }}" required>
                            </td>
                            <td>
                                <select name="kiem_ke[{{ $nl['ma_nl'] }}][ly_do_huy]" id="ly_do_{{ $nl['ma_nl'] }}" class="form-select form-select-sm">
                                    <option value="">-- Không có --</option>
                                    <option value="Quá hạn sử dụng ca trực">Quá hạn sử dụng ca trực</option>
                                    <option value="Hư hỏng do nhiệt độ bếp">Hư hỏng do nhiệt độ bếp</option>
                                    <option value="Rơi vãi / Biến dạng vật lý">Rơi vãi / Biến dạng vật lý</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Gửi Báo Cáo Chốt Ca</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Logic kiểm tra ép buộc nhập lý do hủy động
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
</script>
</body>
</html>