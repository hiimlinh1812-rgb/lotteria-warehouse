@extends('layouts.app')

@section('title', 'Danh sách đơn hàng cần nhận')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Danh sách đơn hàng cần nhận</h2>
    </div>
</div>

<div class="card page-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Người tạo</th>
                        <th>Trạng thái</th>
                        <th>Số mặt hàng</th>
                        <th>Tổng số lượng</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="fw-bold">{{ $order->MaDonDatHang }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($order->NgayDat)->format('d/m/Y') }}</td>
                            <td>{{ $order->HoTen }}</td>
                            <td><x-status-badge :status="$order->TrangThai" /></td>
                            <td>{{ $order->SoMatHang }}</td>
                            <td>{{ $order->TongSoLuong }}</td>
                            <td class="text-end">
                                <a class="btn btn-lotteria btn-sm fw-bold" href="{{ route('ds-don-hang.show', $order->MaDonDatHang) }}">Tiếp nhận</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Không có đơn hàng nào cần nhận.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div>{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
