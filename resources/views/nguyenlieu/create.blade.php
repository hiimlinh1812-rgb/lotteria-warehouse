@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 border-top border-danger border-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-danger fw-bold">THÊM NGUYÊN LIÊU MỚI</h5>
            </div>
            <div class="card-body p-4">
                <form action="/nguyen-lieu" method="POST">
                    @csrf <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã Nguyên Liệu</label>
                            <input type="text" name="MaNguyenLieu" class="form-control" placeholder="VD: NL001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên Nguyên Liệu</label>
                            <input type="text" name="TenNguyenLieu" class="form-control" placeholder="VD: Gà rán róc xương" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đơn vị tính</label>
                            <input type="text" name="DonViTinh" class="form-control" placeholder="VD: Kg, Túi, Hộp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nhóm hàng</label>
                            <select name="NhomHang" class="form-select">
                                <option value="Hàng đông">Hàng đông</option>
                                <option value="Hàng khô">Hàng khô</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mô tả thêm</label>
                        <textarea name="MoTa" class="form-control" rows="2" placeholder="Ghi chú về bảo quản..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="/nguyen-lieu" class="btn btn-secondary me-2">Hủy bỏ</a>
                        <button type="submit" class="btn btn-danger fw-bold">LƯU NGUYÊN LIÊU</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
