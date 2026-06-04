@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 border-top border-primary border-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-primary fw-bold">CẬP NHẬT NGUYÊN LIÊU</h5>
            </div>
            <div class="card-body p-4">
                <form action="/nguyen-lieu/{{ $nl->MaNguyenLieu }}" method="POST">
                    @csrf
                    @method('PUT') <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã Nguyên Liệu (Không được sửa)</label>
                            <input type="text" class="form-control" value="{{ $nl->MaNguyenLieu }}" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên Nguyên Liệu</label>
                            <input type="text" name="TenNguyenLieu" class="form-control" value="{{ $nl->TenNguyenLieu }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Đơn vị tính</label>
                            <input type="text" name="DonViTinh" class="form-control" value="{{ $nl->DonViTinh }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nhóm hàng</label>
                            <select name="NhomHang" class="form-select">
                                <option value="Hàng đông" {{ $nl->NhomHang == 'Hàng đông' ? 'selected' : '' }}>Hàng đông</option>
                                <option value="Hàng khô" {{ $nl->NhomHang == 'Hàng khô' ? 'selected' : '' }}>Hàng khô</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Số lượng tồn kho</label>
                            <input type="number" name="SoLuongTonKho" class="form-control" value="{{ $nl->SoLuongTonKho }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mô tả thêm</label>
                        <textarea name="MoTa" class="form-control" rows="2">{{ $nl->MoTa }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="/nguyen-lieu" class="btn btn-secondary me-2">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary fw-bold">CẬP NHẬT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection