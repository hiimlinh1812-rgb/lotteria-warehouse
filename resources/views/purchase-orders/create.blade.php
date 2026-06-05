@extends('layouts.app')

@section('title', 'Tạo đơn đặt hàng')

@php
    $managerMode = request()->routeIs('don-hang.*');
    $routePrefix = $managerMode ? 'don-hang' : 'purchase-orders';
    $oldItems = old('items', [['MaNguyenLieu' => '', 'SoLuongDat' => 1]]);
    $itemCount = count($oldItems);
    $selectedAccount = (string) old('MaTaiKhoan', $currentAccountCode ?? '');
@endphp

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Tạo đơn đặt hàng</h2>
        <p class="text-muted mb-0">Mã dự kiến: <strong>{{ $nextCode }}</strong>. Sau khi hoàn tất, đơn sẽ chuyển sang trạng thái <strong>Chờ phê duyệt</strong>.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route($routePrefix . '.index') }}">Quay lại trang Đơn hàng</a>
</div>

<form method="post" action="{{ route($routePrefix . '.store') }}">
    @csrf

    <div class="card page-card mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-1 fw-bold">Thông tin đơn hàng</h5>
            <p class="text-muted mb-0">Khai báo người lập đơn, ngày đặt và ghi chú nghiệp vụ.</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="NgayDat" class="form-label fw-semibold">Ngày đặt</label>
                    <input id="NgayDat" type="date" name="NgayDat" class="form-control" value="{{ old('NgayDat', now()->toDateString()) }}" required>
                </div>
                <div class="col-md-4">
                    <label for="MaTaiKhoan" class="form-label fw-semibold">Người lập đơn</label>
                    <select id="MaTaiKhoan" name="MaTaiKhoan" class="form-select" required>
                        <option value="">Chọn tài khoản</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->MaTaiKhoan }}" {{ $selectedAccount === (string) $account->MaTaiKhoan ? 'selected' : '' }}>
                                {{ $account->MaTaiKhoan }} - {{ $account->HoTen }} ({{ $account->VaiTro }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="GhiChu" class="form-label fw-semibold">Ghi chú</label>
                    <input id="GhiChu" name="GhiChu" maxlength="255" class="form-control" value="{{ old('GhiChu') }}" placeholder="Ví dụ: đơn gấp cho ca sáng">
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold">Nguyên liệu cần đặt</h5>
                <p class="text-muted mb-0">Thêm từng nguyên liệu và số lượng cần mua cho đơn hàng.</p>
            </div>
            <button class="btn btn-outline-secondary" type="button" onclick="addItemRow()">+ Thêm dòng</button>
        </div>
        <div class="card-body px-4 pb-4">
            <div id="items" data-next-index="{{ $itemCount }}" class="d-flex flex-column gap-3">
                @foreach ($oldItems as $index => $oldItem)
                    <div class="row g-3 align-items-end border rounded-4 p-3 item-row">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nguyên liệu</label>
                            <select name="items[{{ $index }}][MaNguyenLieu]" class="form-select" required>
                                <option value="">Chọn nguyên liệu</option>
                                @foreach ($ingredients as $ingredient)
                                    <option value="{{ $ingredient->MaNguyenLieu }}" {{ ($oldItem['MaNguyenLieu'] ?? '') === $ingredient->MaNguyenLieu ? 'selected' : '' }}>
                                        {{ $ingredient->MaNguyenLieu }} - {{ $ingredient->TenNguyenLieu }} | Tồn: {{ $ingredient->SoLuongTonKho }} {{ $ingredient->DonViTinh }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Số lượng</label>
                            <input type="number" min="1" max="999999" name="items[{{ $index }}][SoLuongDat]" class="form-control" value="{{ $oldItem['SoLuongDat'] ?? 1 }}" required>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button class="btn btn-outline-danger" type="button" onclick="removeItemRow(this)" title="Xóa dòng">X</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-lotteria fw-bold" type="submit">Hoàn tất</button>
                <a class="btn btn-outline-secondary" href="{{ route($routePrefix . '.index') }}">Hủy</a>
            </div>
        </div>
    </div>
</form>

<template id="item-template">
    <div class="row g-3 align-items-end border rounded-4 p-3 item-row">
        <div class="col-md-8">
            <label class="form-label fw-semibold">Nguyên liệu</label>
            <select data-name="MaNguyenLieu" class="form-select" required>
                <option value="">Chọn nguyên liệu</option>
                @foreach ($ingredients as $ingredient)
                    <option value="{{ $ingredient->MaNguyenLieu }}">
                        {{ $ingredient->MaNguyenLieu }} - {{ $ingredient->TenNguyenLieu }} | Tồn: {{ $ingredient->SoLuongTonKho }} {{ $ingredient->DonViTinh }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Số lượng</label>
            <input data-name="SoLuongDat" type="number" min="1" max="999999" value="1" class="form-control" required>
        </div>
        <div class="col-md-1 d-grid">
            <button class="btn btn-outline-danger" type="button" onclick="removeItemRow(this)" title="Xóa dòng">X</button>
        </div>
    </div>
</template>

<script>
    const itemsContainer = document.getElementById('items');
    let itemIndex = Number(itemsContainer.dataset.nextIndex || 0);

    function addItemRow() {
        const template = document.getElementById('item-template').content.cloneNode(true);
        template.querySelectorAll('[data-name]').forEach((input) => {
            input.name = 'items[' + itemIndex + '][' + input.dataset.name + ']';
            input.removeAttribute('data-name');
        });
        itemsContainer.appendChild(template);
        itemIndex++;
        itemsContainer.dataset.nextIndex = String(itemIndex);
    }

    function removeItemRow(button) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 1) {
            rows[0].querySelector('select').value = '';
            rows[0].querySelector('input[type="number"]').value = 1;
            return;
        }
        button.closest('.item-row').remove();
    }
</script>
@endsection
