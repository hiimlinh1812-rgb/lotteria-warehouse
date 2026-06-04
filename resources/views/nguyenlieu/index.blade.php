@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">DANH MỤC NGUYÊN LIÊU</h5>
        <a href="/nguyen-lieu/create" class="btn btn-light btn-sm fw-bold text-danger">+ Thêm mới</a>
    </div>
    <div class="card-body">
        <table class="table table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Mã NL</th>
                    <th>Tên nguyên liệu</th>
                    <th>Nhóm hàng</th>
                    <th>Đơn vị</th>
                    <th>Tồn kho</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($danhSachNL as $nl)
                <tr>
                    <td>{{ $nl->MaNguyenLieu }}</td>
                    <td class="fw-bold">{{ $nl->TenNguyenLieu }}</td>
                    <td><span class="badge bg-secondary">{{ $nl->NhomHang }}</span></td>
                    <td>{{ $nl->DonViTinh }}</td>
                    <td class="text-danger fw-bold">{{ $nl->SoLuongTonKho }}</td>
                    <td class="d-flex gap-1">
                        <a href="/nguyen-lieu/{{ $nl->MaNguyenLieu }}/edit" class="btn btn-sm btn-outline-primary">Sửa</a>
                        
                        <form action="/nguyen-lieu/{{ $nl->MaNguyenLieu }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nguyên liệu này khỏi kho?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection