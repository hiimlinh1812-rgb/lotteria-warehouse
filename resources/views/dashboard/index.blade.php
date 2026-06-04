@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Chào mừng quay lại hệ thống Lotteria!</h2>
    <div class="row mt-4">
    <div class="col-md-6 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h5>Phiếu chờ duyệt</h5>
                <h3>2</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h5>Phiếu xuất hủy</h5>
                <h3>0</h3>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h5>Thống kê tồn kho</h5>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <h5>Phiếu giải trình thất thoát</h5>
                <h3>0</h3>
            </div>
        </div>
    </div>
</div>
</div>
</div>

@endsection