@extends('layouts.app')

@section('content')
<div class="container w-50">
    <h2 class="mb-4">Sửa thông tin nhân viên</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('tai-khoan.update', $taiKhoan->MaTaiKhoan) }}" method="POST">
        @csrf 
        @method('PUT')

        <div class="mb-3">
            <label>Mã tài khoản</label>
            <input type="text" class="form-control bg-light" value="{{ $taiKhoan->MaTaiKhoan }}" readonly>
        </div>

        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen', $taiKhoan->HoTen) }}" required>
        </div>

        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="SoDienThoai" class="form-control" 
                   value="{{ old('SoDienThoai', $taiKhoan->SoDienThoai) }}" 
                   pattern="0[0-9]{9}" 
                   title="Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số"
                   required>
            <small class="text-muted">Định dạng: 0xxxxxxxxx (10 chữ số)</small>
        </div>

        <div class="mb-3">
            <label>Vai trò</label>
            <select name="VaiTro" class="form-control">
                <option value="Cửa hàng trưởng" {{ old('VaiTro', $taiKhoan->VaiTro) == 'Cửa hàng trưởng' ? 'selected' : '' }}>Cửa hàng trưởng</option>
                <option value="Quản lý" {{ old('VaiTro', $taiKhoan->VaiTro) == 'Quản lý' ? 'selected' : '' }}>Quản lý</option>
                <option value="Nhân viên" {{ old('VaiTro', $taiKhoan->VaiTro) == 'Nhân viên' ? 'selected' : '' }}>Nhân viên</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Mật khẩu (Để trống nếu không đổi)</label>
            <input type="password" name="MatKhau" class="form-control" minlength="6">
            <small class="text-muted">Nếu đổi, ít nhất 6 ký tự</small>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật nhân viên</button>
        <a href="{{ route('tai-khoan.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection