@extends('layouts.app') 

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh sách nhân viên</h2>
        <a href="{{ route('tai-khoan.create') }}" class="btn btn-success">Thêm tài khoản</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-danger">
            <tr>
                <th>Mã TK</th>
                <th>Họ tên</th>
                <th>Số điện thoại</th>
                <th>Vai trò</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dsTaiKhoan as $tk)
            <tr>
                <td>{{ $tk->MaTaiKhoan }}</td>
                <td>{{ $tk->HoTen }}</td>
                <td>{{ $tk->SoDienThoai }}</td>
                <td>{{ $tk->VaiTro }}</td>
                <td>
                    <a href="{{ route('tai-khoan.edit', $tk->MaTaiKhoan) }}" class="btn btn-sm btn-primary">Sửa</a>
                    <form action="{{ route('tai-khoan.destroy', $tk->MaTaiKhoan) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')">Xóa</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection