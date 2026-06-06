@extends('layouts.app')

@section('content')
<div class="container w-50">
    <h2 class="mb-4">Thêm tài khoản</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

@section('content')
<div class="container w-50">
    <h2 class="mb-4">Thêm tài khoản</h2>
    <form action="{{ route('tai-khoan.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="HoTen" class="form-control" value="{{ old('HoTen') }}" required>
        </div>
        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="SoDienThoai" class="form-control" 
                   value="{{ old('SoDienThoai') }}" 
                   pattern="0[0-9]{9}" 
                   title="Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số"
                   required>
            <small class="text-muted">Định dạng: 0xxxxxxxxx (10 chữ số)</small>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="MatKhau" class="form-control" minlength="6" required>
            <small class="text-muted">Ít nhất 6 ký tự</small>
        </div>
        <div class="mb-3">
            <label>Vai trò</label>
            <select name="VaiTro" class="form-control">
                <option value="Cửa hàng trưởng">Cửa hàng trưởng</option>
                <option value="Quản lý">Quản lý</option>
                <option value="Nhân viên">Nhân viên</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Lưu tài khoản</button>
        <a href="{{ route('tai-khoan.index') }}" class="btn btn-secondary">Hủy bỏ</a>
    </form>
</div>
@endsection