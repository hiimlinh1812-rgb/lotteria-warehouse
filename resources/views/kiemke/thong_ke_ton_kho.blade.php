@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold text-dark">📊 BÁO CÁO THỐNG KÊ TỒN KHO SAU KIỂM KÊ</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">Quay lại Dashboard</a>
    </div>

    @forelse($lichSuThongKe as $thongKe)
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold">Mã Biên Bản Chốt: <strong class="text-warning font-monospace">{{ $thongKe['MaPhieuKiemKe'] }}</strong></span>
                <span class="badge bg-white text-success fw-bold text-uppercase">Ngày kiểm ca: {{ \Carbon\Carbon::parse($thongKe['NgayKiemKe'])->format('d/m/Y') }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered text-center align-middle mb-0 table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Lô Hàng</th>
                            <th>Tên Nguyên Liệu Gốc</th>
                            <th>Số Sách Hệ Thống</th>
                            <th class="table-success text-dark">Số Thực Tế Đã Đồng Bộ</th>
                            <th>Chênh Lệch Đối Soát</th>
                            <th>Kết Luận Vận Hành</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($thongKe['Details'] as $detail)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $detail->MaLoHang }}</td>
                            <td class="text-start fw-bold text-secondary">{{ $detail->TenNguyenLieu }}</td>
                            <td class="text-primary fw-bold">{{ $detail->SoLuongHeThong }}</td>
                            <td class="table-success text-dark fw-bold">{{ $detail->SoLuongThucTe }}</td>
                            <td class="fw-bold {{ $detail->ChenhLech != 0 ? 'text-danger' : 'text-success' }}">
                                {{ $detail->ChenhLech > 0 ? '+'.$detail->ChenhLech : $detail->ChenhLech }}
                            </td>
                            <td>
                                <span class="badge {{ $detail->TinhTrang == 'Khớp' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $detail->TinhTrang }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($thongKe['GiaiTrinh'])
                    <div class="p-3 bg-light border-top">
                        <div class="alert alert-info mb-0 py-2 small shadow-sm border-start border-4 border-info">
                            📝 <strong>Văn bản giải trình đính kèm đợt lệch ({{ $thongKe['GiaiTrinh']->MaPhieuGiaiTrinh }}):</strong> 
                            <span class="fw-bold text-dark">{{ $thongKe['GiaiTrinh']->NoiDung }}</span> | Nguyên nhân: <span class="text-danger fw-bold">{{ $thongKe['GiaiTrinh']->NguyenNhan }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card shadow border-0 p-5 text-center my-4 bg-white">
            <div class="text-center mb-3"><span class="fs-1">📊</span></div>
            <h5 class="fw-bold text-secondary">Hệ thống chưa ghi nhận bất kỳ phiếu kiểm kê định kỳ nào ở trạng thái "Đã duyệt"!</h5>
            <p class="text-muted small mb-0">Số liệu thống kê sẽ tự động cập nhật hiển thị tại đây sau khi có phiếu đối soát được duyệt chốt.</p>
        </div>
    @endforelse
</div>
@endsection
