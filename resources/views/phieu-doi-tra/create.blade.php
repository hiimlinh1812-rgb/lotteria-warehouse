@extends('layouts.nhap-kho')

@section('title', 'Tạo yêu cầu Đổi/Trả – ' . $phieuNhan->MaPhieuNhan)

@section('content')

<div class="page-header">
    <div>
        <h1>🔄 Tạo yêu cầu Đổi / Trả hàng</h1>
        <div class="subtitle">Phiếu nhận: {{ $phieuNhan->MaPhieuNhan }} | Đơn: {{ $phieuNhan->MaDonDatHang }}</div>
    </div>
    <a href="{{ route('phieu-nhan-hang.show', $phieuNhan->MaPhieuNhan) }}" class="btn btn-secondary">← Quay lại</a>
</div>

<div class="alert alert-warning">
    <span>⚠</span>
    Hệ thống phát hiện <strong>{{ count($saiLechList) }}</strong> mặt hàng có sai lệch.
    Vui lòng kiểm tra thực tế và chọn phương án xử lý phù hợp cho từng mặt hàng.
</div>

@if($errors->any())
<div class="alert alert-danger">
    <div><strong>✖ Vui lòng kiểm tra lại:</strong>
        <ul style="margin-top:6px; padding-left:18px">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('phieu-doi-tra.store', $phieuNhan->MaPhieuNhan) }}">
    @csrf

    @foreach($saiLechList as $idx => $sl)
    @php $maNL = $sl['nguyenLieu']->MaNguyenLieu; @endphp
    <div class="card">
        <div class="card-header">
            <h3>🔸 {{ $sl['nguyenLieu']->TenNguyenLieu }}</h3>
            <span class="badge {{ $sl['chenhLech'] < 0 ? 'badge-danger' : 'badge-warning' }}">
                {{ $sl['chenhLech'] < 0 ? 'Thiếu ' . abs($sl['chenhLech']) : 'Thừa ' . $sl['chenhLech'] }}
                {{ $sl['nguyenLieu']->DonViTinh }}
            </span>
        </div>
        <div class="card-body">
            {{-- Thông tin sai lệch --}}
            <div class="info-grid" style="margin-bottom:20px">
                <div class="info-item"><label>Số lượng đặt</label><span>{{ $sl['soLuongDat'] }} {{ $sl['nguyenLieu']->DonViTinh }}</span></div>
                <div class="info-item"><label>Số lượng nhận được</label><span>{{ $sl['soLuongNhan'] }} {{ $sl['nguyenLieu']->DonViTinh }}</span></div>
                <div class="info-item">
                    <label>Chênh lệch</label>
                    <span class="{{ $sl['chenhLech'] < 0 ? 'text-danger' : 'text-warning' }}">
                        {{ $sl['chenhLech'] > 0 ? '+' : '' }}{{ $sl['chenhLech'] }} {{ $sl['nguyenLieu']->DonViTinh }}
                    </span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                {{-- Loại xử lý --}}
                <div class="form-group">
                    <label class="form-label">Loại xử lý <span class="required">*</span></label>
                    <select name="nguyen_lieu[{{ $maNL }}][loai_xu_ly]"
                            class="form-control @error('nguyen_lieu.'.$maNL.'.loai_xu_ly') is-invalid @enderror"
                            required>
                        <option value="">-- Chọn --</option>
                        <option value="Đổi hàng" @selected(old('nguyen_lieu.'.$maNL.'.loai_xu_ly')==='Đổi hàng')>🔄 Đổi hàng</option>
                        <option value="Trả hàng" @selected(old('nguyen_lieu.'.$maNL.'.loai_xu_ly')==='Trả hàng')>↩ Trả hàng</option>
                    </select>
                    @error('nguyen_lieu.'.$maNL.'.loai_xu_ly')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Số lượng xử lý --}}
                <div class="form-group">
                    <label class="form-label">Số lượng xử lý <span class="required">*</span></label>
                    <input type="number"
                           name="nguyen_lieu[{{ $maNL }}][so_luong]"
                           class="form-control qty-input @error('nguyen_lieu.'.$maNL.'.so_luong') is-invalid @enderror"
                           min="1"
                           max="{{ abs($sl['chenhLech']) }}"
                           value="{{ old('nguyen_lieu.'.$maNL.'.so_luong', abs($sl['chenhLech'])) }}"
                           required>
                    <div class="form-text">Tối đa: {{ abs($sl['chenhLech']) }} {{ $sl['nguyenLieu']->DonViTinh }}</div>
                    @error('nguyen_lieu.'.$maNL.'.so_luong')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Lý do --}}
                <div class="form-group">
                    <label class="form-label">Lý do <span class="required">*</span></label>
                    <input type="text"
                           name="nguyen_lieu[{{ $maNL }}][ly_do]"
                           class="form-control @error('nguyen_lieu.'.$maNL.'.ly_do') is-invalid @enderror"
                           placeholder="Nhập lý do cụ thể..."
                           value="{{ old('nguyen_lieu.'.$maNL.'.ly_do') }}"
                           required>
                    @error('nguyen_lieu.'.$maNL.'.ly_do')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px">
        <button type="submit" class="btn btn-primary btn-lg">📤 Gửi yêu cầu Đổi/Trả</button>
        <a href="{{ route('phieu-nhan-hang.show', $phieuNhan->MaPhieuNhan) }}"
           class="btn btn-secondary btn-lg">✖ Hủy</a>
    </div>
</form>
@endsection
