@extends('layouts.app')

@section('content')
<div class="container w-50">
    <h2 class="mb-4">Sửa thông tin nhân viên</h2>
    
    <form action="{{ route('tai-khoan.update', $taiKhoan->MaTaiKhoan) }}" method="POST">
        @csrf 
        @method('PUT')

        <div class="mb-3">
            <label>Mã tài khoản</label>
            <input type="text" class="form-control" value="{{ $taiKhoan->MaTaiKhoan }}" readonly>
        </div>

        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="HoTen" class="form-control" value="{{ $taiKhoan->HoTen }}" required>
        </div>

        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="SoDienThoai" class="form-control" value="{{ $taiKhoan->SoDienThoai }}" required>
        </div>

        <div class="mb-3">
            <label>Vai trò</label>
            <select name="VaiTro" class="form-control">
                <option value="Cửa hàng trưởng" {{ $taiKhoan->VaiTro == 'Cửa hàng trưởng' ? 'selected' : '' }}>Cửa hàng trưởng</option>
                <option value="Quản lý" {{ $taiKhoan->VaiTro == 'Quản lý' ? 'selected' : '' }}>Quản lý</option>
                <option value="Nhân viên" {{ $taiKhoan->VaiTro == 'Nhân viên' ? 'selected' : '' }}>Nhân viên</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Mật khẩu (Để trống nếu không đổi)</label>
            <input type="password" name="MatKhau" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật nhân viên</button>
        <a href="{{ route('tai-khoan.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection