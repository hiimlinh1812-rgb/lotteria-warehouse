@extends('layouts.nhap-kho')

@section('title', 'Nhập số lượng thực nhận – ' . $phieuNhan->MaPhieuNhan)

@section('content')

<div class="page-header">
    <div>
        <h1>✏ Nhập số lượng thực nhận</h1>
        <div class="subtitle">Phiếu: {{ $phieuNhan->MaPhieuNhan }} | Đơn: {{ $phieuNhan->MaDonDatHang }}</div>
    </div>
    <a href="{{ route('phieu-nhan-hang.show', $phieuNhan->MaPhieuNhan) }}" class="btn btn-secondary">← Quay lại</a>
</div>

<div class="alert alert-info">
    <span>ℹ</span>
    Nhân viên kiểm đếm thực tế và nhập số lượng nhận được cho từng nguyên liệu.
    Hệ thống sẽ tự động so sánh với số lượng đặt sau khi xác nhận.
</div>

<form method="POST" action="{{ route('phieu-nhan-hang.luu-so-luong', $phieuNhan->MaPhieuNhan) }}"
      id="formNhapSoLuong">
    @csrf

    @if($errors->any())
    <div class="alert alert-danger">
        <div><strong>✖ Vui lòng kiểm tra lại:</strong>
            <ul style="margin-top:6px; padding-left:18px">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3>📋 Danh sách nguyên liệu cần kiểm đếm</h3>
            <span class="badge badge-info">{{ $chiTietDon->count() }} mặt hàng</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:30%">Nguyên liệu</th>
                        <th style="text-align:center; width:10%">ĐVT</th>
                        <th style="text-align:center; width:10%">SL Đặt</th>
                        <th style="text-align:center; width:12%">SL Thực nhận <span class="required">*</span></th>
                        <th style="width:13%">Ngày SX <span class="required">*</span></th>
                        <th style="width:13%">Hạn SD <span class="required">*</span></th>
                        <th style="text-align:center; width:12%">Chênh lệch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chiTietDon as $ct)
                    <tr id="row_{{ $ct->MaNguyenLieu }}">
                        <td>
                            <strong>{{ $ct->nguyenLieu->TenNguyenLieu ?? $ct->MaNguyenLieu }}</strong>
                            <div style="font-size:11.5px; color:var(--text-muted)">
                                {{ $ct->nguyenLieu->NhomHang ?? '' }}
                            </div>
                        </td>
                        <td style="text-align:center">{{ $ct->nguyenLieu->DonViTinh ?? '' }}</td>
                        <td style="text-align:center">
                            <strong style="font-size:16px">{{ $ct->SoLuongDat }}</strong>
                            <input type="hidden"
                                   name="nguyen_lieu[{{ $ct->MaNguyenLieu }}][so_luong_dat]"
                                   value="{{ $ct->SoLuongDat }}">
                        </td>
                        <td style="text-align:center">
                            <input type="number"
                                   name="nguyen_lieu[{{ $ct->MaNguyenLieu }}][so_luong_nhan]"
                                   class="form-control qty-input sl-nhan"
                                   data-ma="{{ $ct->MaNguyenLieu }}"
                                   data-dat="{{ $ct->SoLuongDat }}"
                                   min="0"
                                   value="{{ old('nguyen_lieu.'.$ct->MaNguyenLieu.'.so_luong_nhan', $ct->SoLuongDat) }}"
                                   required>
                        </td>
                        <td>
                            <input type="date"
                                   name="nguyen_lieu[{{ $ct->MaNguyenLieu }}][nsx]"
                                   class="form-control"
                                   value="{{ old('nguyen_lieu.'.$ct->MaNguyenLieu.'.nsx') }}"
                                   required>
                        </td>
                        <td>
                            <input type="date"
                                   name="nguyen_lieu[{{ $ct->MaNguyenLieu }}][hsd]"
                                   class="form-control"
                                   value="{{ old('nguyen_lieu.'.$ct->MaNguyenLieu.'.hsd') }}"
                                   required>
                        </td>
                        <td style="text-align:center" id="cl_{{ $ct->MaNguyenLieu }}">
                            <span class="text-muted">–</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tóm tắt sai lệch --}}
    <div class="card" id="summaryCard" style="display:none">
        <div class="card-header"><h3>⚠ Tóm tắt sai lệch phát hiện</h3></div>
        <div class="card-body" id="summaryBody"></div>
    </div>

    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:8px">
        <button type="submit" class="btn btn-primary btn-lg">✔ Xác nhận hoàn thành nhập liệu</button>
        <a href="{{ route('phieu-nhan-hang.show', $phieuNhan->MaPhieuNhan) }}"
           class="btn btn-secondary btn-lg">✖ Hủy</a>
        <span class="text-muted" style="font-size:12px">
            Hệ thống sẽ tự động so sánh và thông báo nếu có sai lệch.
        </span>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Live chênh lệch
document.querySelectorAll('.sl-nhan').forEach(input => {
    input.addEventListener('input', calcDiff);
    calcDiff.call(input);
});

function calcDiff() {
    const ma       = this.dataset.ma;
    const dat      = parseInt(this.dataset.dat) || 0;
    const nhan     = parseInt(this.value) || 0;
    const cl       = nhan - dat;
    const cell     = document.getElementById('cl_' + ma);
    const row      = document.getElementById('row_' + ma);

    if (this.value === '') {
        cell.innerHTML = '<span class="text-muted">–</span>';
        row.classList.remove('row-mismatch');
        return;
    }

    if (cl === 0) {
        cell.innerHTML = '<span class="text-success">✔ Khớp</span>';
        row.classList.remove('row-mismatch');
    } else if (cl < 0) {
        cell.innerHTML = `<span class="text-danger">${cl} (thiếu)</span>`;
        row.classList.add('row-mismatch');
    } else {
        cell.innerHTML = `<span class="text-warning">+${cl} (thừa)</span>`;
        row.classList.add('row-mismatch');
    }
    updateSummary();
}

function updateSummary() {
    const mismatched = document.querySelectorAll('.row-mismatch');
    const card = document.getElementById('summaryCard');
    const body = document.getElementById('summaryBody');

    if (mismatched.length === 0) {
        card.style.display = 'none';
        return;
    }

    card.style.display = '';
    let html = `<div class="alert alert-warning"><span>⚠</span> Phát hiện <strong>${mismatched.length}</strong> mặt hàng sai lệch.
    Sau khi xác nhận, hệ thống sẽ chuyển sang trạng thái <em>"Chờ xử lý"</em> để tạo yêu cầu đổi/trả.</div>`;
    body.innerHTML = html;
}
</script>
@endpush
