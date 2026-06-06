@extends('layouts.app')

@section('title', 'Tạo phiếu nhận hàng - ' . $orderData->MaDonDatHang)

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Tạo phiếu nhận hàng - {{ $orderData->MaDonDatHang }}</h2>
        <p class="text-muted mb-0">Ngày đặt: {{ \Illuminate\Support\Carbon::parse($orderData->NgayDat)->format('d/m/Y') }} - Người tạo: {{ $orderData->HoTen }}</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('ds-don-hang.index') }}">Quay lại</a>
</div>

<form method="post" action="{{ route('ds-don-hang.store', $orderData->MaDonDatHang) }}">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card page-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Thông tin phiếu nhận hàng</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="NgayNhan" class="form-label fw-semibold">Ngày nhận</label>
                            <input id="NgayNhan" name="NgayNhan" type="date" class="form-control bg-light" value="{{ old('NgayNhan') }}" data-default-today="true" readonly required style="pointer-events: none;" tabindex="-1">
                        </div>
                        <div class="col-md-8">
                            <label for="GhiChu" class="form-label fw-semibold">Ghi chú</label>
                            <input id="GhiChu" name="GhiChu" maxlength="255" class="form-control" placeholder="Nhập ghi chú nếu có...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card page-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">Chi tiết nguyên liệu nhận hàng</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-2">Mã nguyên liệu</th>
                                    <th class="col-3">Tên nguyên liệu</th>
                                    <th class="col-1">Số lượng đặt</th>
                                    <th class="col-1">Đơn vị</th>
                                    <th class="col-2">Số lượng thực nhận</th>
                                    <th class="col-2">Ngày sản xuất</th>
                                    <th class="col-2">Hạn sử dụng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $index => $item)
                                <tr>
                                    <td class="fw-bold">{{ $item->MaNguyenLieu }}</td>
                                    <td>{{ $item->TenNguyenLieu }}</td>
                                    <td>{{ $item->SoLuongDat }}</td>
                                    <td>{{ $item->DonViTinh }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][MaNguyenLieu]" value="{{ $item->MaNguyenLieu }}">
                                        <input type="number" min="0" name="items[{{ $index }}][SoLuongThucNhan]" class="form-control" value="{{ $item->SoLuongDat }}" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $index }}][NgaySanXuat]" class="form-control" value="{{ old('items.'.$index.'.NgaySanXuat') }}" required>
                                    </td>
                                    <td>
                                        <input type="date" name="items[{{ $index }}][HanSuDung]" class="form-control" value="{{ old('items.'.$index.'.HanSuDung') }}" required>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-lotteria fw-bold" type="submit">Xác nhận tạo phiếu nhận hàng</button>
                        <a class="btn btn-outline-secondary" href="{{ route('ds-don-hang.index') }}">Hủy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Đảm bảo ngày nhận luôn là ngày hiện tại khi mở form theo giờ local của trình duyệt
        const ngayNhanInput = document.getElementById('NgayNhan');
        if (ngayNhanInput && !ngayNhanInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const today = `${year}-${month}-${day}`;
            ngayNhanInput.value = today;
            console.log('Set NgayNhan to:', today);
        }

        // Tương tự cho Ngày sản xuất và Hạn sử dụng của các dòng nguyên liệu nếu chưa có giá trị
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayStr = `${year}-${month}-${day}`;

        document.querySelectorAll('input[type="date"]').forEach(input => {
            if (!input.value) {
                if (input.name.includes('NgaySanXuat')) {
                    // Mặc định lùi 30 ngày cho NSX
                    const d = new Date();
                    d.setDate(d.getDate() - 30);
                    input.value = d.toISOString().split('T')[0];
                } else if (input.name.includes('HanSuDung')) {
                    // Mặc định tiến 90 ngày cho HSD
                    const d = new Date();
                    d.setDate(d.getDate() + 90);
                    input.value = d.toISOString().split('T')[0];
                }
            }
        });

        // Validation NSX và HSD
        function validateNSX_HSD(row) {
            const nsxInput = row.querySelector('input[name*="[NgaySanXuat]"]');
            const hsdInput = row.querySelector('input[name*="[HanSuDung]"]');
            
            if (nsxInput && hsdInput) {
                const nsxValue = nsxInput.value;
                const hsdValue = hsdInput.value;

                // Reset validation
                nsxInput.setCustomValidity('');
                hsdInput.setCustomValidity('');

                if (nsxValue && hsdValue) {
                        if (nsxValue > todayStr) {
                            nsxInput.setCustomValidity('Ngày sản xuất không được lớn hơn ngày hiện tại');
                        }

                        if (nsxValue >= hsdValue) {
                            hsdInput.setCustomValidity('Ngày sản xuất phải nhỏ hơn Hạn sử dụng');
                        }
                }
            }
        }

        // Gán sự kiện validate cho tất cả các dòng
        document.querySelectorAll('tbody tr').forEach(row => {
            const inputs = row.querySelectorAll('input[type="date"]');
            inputs.forEach(input => {
                input.addEventListener('change', () => validateNSX_HSD(row));
            });
            // Validate lần đầu khi load
            validateNSX_HSD(row);
        });
    });
</script>
@endpush
