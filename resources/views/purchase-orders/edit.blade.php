@extends('layouts.app')

@section('title', 'Sửa đơn mua ' . $order->MaDonDatHang)

@php
    $editableItems = old('items', ! empty($items) ? $items->map(function ($item) {
        return [
            'MaNguyenLieu' => $item->MaNguyenLieu,
            'SoLuongDat' => $item->SoLuongDat,
        ];
    })->all() : [['MaNguyenLieu' => '', 'SoLuongDat' => 1]]);
@endphp

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="text-lotteria fw-bold mb-1">Sửa đơn mua {{ $order->MaDonDatHang }}</h2>
        <p class="text-muted mb-0">Chỉ đơn ở trạng thái <strong>Chờ phê duyệt</strong> hoặc <strong>Đang xử lý</strong> mới được cập nhật.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Chi tiết</a>
        <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">Danh sách</a>
    </div>
</div>

<form method="post" action="{{ route('purchase-orders.update', $order->MaDonDatHang) }}">
    @csrf
    @method('put')

    <div class="card page-card mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-1 fw-bold">Thông tin đơn hàng</h5>
            <p class="text-muted mb-0">Cập nhật thông tin chung trước khi lưu thay đổi.</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="NgayDat" class="form-label fw-semibold">Ngày đặt</label>
                    <input id="NgayDat" type="date" name="NgayDat" class="form-control" value="{{ old('NgayDat', \Illuminate\Support\Carbon::parse($order->NgayDat)->toDateString()) }}" required>
                </div>
                <div class="col-md-4">
                    <label for="MaTaiKhoan" class="form-label fw-semibold">Người lập đơn</label>
                    <select id="MaTaiKhoan" name="MaTaiKhoan" class="form-select" required>
                        <option value="">Chọn tài khoản</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->MaTaiKhoan }}" {{ old('MaTaiKhoan', $order->MaTaiKhoan) == $account->MaTaiKhoan ? 'selected' : '' }}>
                                {{ $account->MaTaiKhoan }} - {{ $account->HoTen }} ({{ $account->VaiTro }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="GhiChu" class="form-label fw-semibold">Ghi chú</label>
                    <input id="GhiChu" name="GhiChu" maxlength="255" class="form-control" value="{{ old('GhiChu', $order->GhiChu) }}" placeholder="Ví dụ: điều chỉnh lại số lượng">
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold">Nguyên liệu cần đặt</h5>
                <p class="text-muted mb-0">Bạn có thể thêm, xóa hoặc cập nhật số lượng từng nguyên liệu.</p>
            </div>
            <button class="btn btn-outline-secondary" type="button" onclick="addItemRow()">+ Thêm dòng</button>
        </div>
        <div class="card-body px-4 pb-4">
            <div id="items" data-next-index="{{ count($editableItems) }}" class="d-flex flex-column gap-3">
                @foreach ($editableItems as $index => $oldItem)
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
                <button class="btn btn-lotteria fw-bold" type="submit">Lưu thay đổi</button>
                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Quay lại</a>
            </div>
        </div>
    </div>
</form>

<template id="item-template">
    <div class="row g-3 align-items-end border rounded-4 p-3 item-row">
        <div class="col-md-8">
            <label class="form-label fw-semibold">Nguyên liệu</label>
            <select data-name="MaNguyenLieu" class="form-select" required></select>
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

    <script id="ingredient-options-data" type="application/json">
        @json($ingredients->map(function ($ingredient) {
            return [
                'value' => $ingredient->MaNguyenLieu,
                'label' => $ingredient->MaNguyenLieu . ' - ' . $ingredient->TenNguyenLieu . ' | Tồn: ' . $ingredient->SoLuongTonKho . ' ' . $ingredient->DonViTinh,
            ];
        })->values())
    </script>

    <script>
        const itemsContainer = document.getElementById('items');
        const ingredientOptionsElement = document.getElementById('ingredient-options-data');
        const ingredientOptions = JSON.parse(ingredientOptionsElement.textContent || '[]');
        let itemIndex = Number(itemsContainer.dataset.nextIndex || 0);

        function renderIngredientOptions(select, selectedValue) {
            select.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Chọn nguyên liệu';
            select.appendChild(placeholder);

            ingredientOptions.forEach((option) => {
                const element = document.createElement('option');
                element.value = option.value;
                element.textContent = option.label;

                if (selectedValue && selectedValue === option.value) {
                    element.selected = true;
                }

                select.appendChild(element);
            });
        }

        function addItemRow() {
            const template = document.getElementById('item-template').content.cloneNode(true);
            template.querySelectorAll('[data-name]').forEach((input) => {
                input.name = 'items[' + itemIndex + '][' + input.dataset.name + ']';

                if (input.tagName === 'SELECT') {
                    renderIngredientOptions(input, '');
                }

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
