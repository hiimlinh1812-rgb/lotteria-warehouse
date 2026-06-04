@extends('layouts.app')

@section('title', 'Sửa đơn mua ' . $order->MaDonDatHang)

@section('content')
    <div class="topbar">
        <div>
            <h1>Sửa đơn mua {{ $order->MaDonDatHang }}</h1>
            <p class="subtle">Chỉ đơn mua đang Chờ phê duyệt hoặc Đang xử lý mới được cập nhật.</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('purchase-orders.show', $order->MaDonDatHang) }}">Chi tiết</a>
            <a class="btn btn-secondary" href="{{ route('purchase-orders.index') }}">Danh sách</a>
        </div>
    </div>

    <form method="post" action="{{ route('purchase-orders.update', $order->MaDonDatHang) }}">
        @csrf
        @method('put')

        <div class="panel">
            <div class="grid-3">
                <div class="field">
                    <label for="NgayDat">Ngày đặt</label>
                    <input id="NgayDat" type="date" name="NgayDat" value="{{ old('NgayDat', \Illuminate\Support\Carbon::parse($order->NgayDat)->toDateString()) }}" required>
                </div>
                <div class="field">
                    <label for="MaTaiKhoan">Người lập đơn</label>
                    <select id="MaTaiKhoan" name="MaTaiKhoan" required>
                        <option value="">Chọn tài khoản</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->MaTaiKhoan }}" @selected(old('MaTaiKhoan', $order->MaTaiKhoan) === $account->MaTaiKhoan)>
                                {{ $account->MaTaiKhoan }} - {{ $account->HoTen }} ({{ $account->VaiTro }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="GhiChu">Ghi chú</label>
                    <input id="GhiChu" name="GhiChu" maxlength="255" value="{{ old('GhiChu', $order->GhiChu) }}" placeholder="Nhu cầu mua hàng">
                </div>
            </div>
        </div>

        <div class="panel">
            <h2 style="font-size:18px;margin:0 0 14px;">Nguyên liệu cần đặt</h2>
            <div id="items">
                @php
                    $oldItems = old('items', $items ?: [['MaNguyenLieu' => '', 'SoLuongDat' => 1]]);
                @endphp
                @foreach ($oldItems as $index => $oldItem)
                    <div class="item-row">
                        <div class="field">
                            <label>Nguyên liệu</label>
                            <select name="items[{{ $index }}][MaNguyenLieu]" required>
                                <option value="">Chọn nguyên liệu</option>
                                @foreach ($ingredients as $ingredient)
                                    <option value="{{ $ingredient->MaNguyenLieu }}" @selected(($oldItem['MaNguyenLieu'] ?? '') === $ingredient->MaNguyenLieu)>
                                        {{ $ingredient->MaNguyenLieu }} - {{ $ingredient->TenNguyenLieu }} | Tồn: {{ $ingredient->SoLuongTonKho }} {{ $ingredient->DonViTinh }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Số lượng</label>
                            <input type="number" min="1" max="999999" name="items[{{ $index }}][SoLuongDat]" value="{{ $oldItem['SoLuongDat'] ?? 1 }}" required>
                        </div>
                        <button class="btn icon-btn" type="button" onclick="removeItemRow(this)" title="Xóa dòng">x</button>
                    </div>
                @endforeach
            </div>

            <div class="actions" style="margin-top:12px;">
                <button class="btn btn-secondary" type="button" onclick="addItemRow()">+ Thêm dòng</button>
                <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
            </div>
        </div>
    </form>

    <template id="item-template">
        <div class="item-row">
            <div class="field">
                <label>Nguyên liệu</label>
                <select data-name="MaNguyenLieu" required>
                    <option value="">Chọn nguyên liệu</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->MaNguyenLieu }}">
                            {{ $ingredient->MaNguyenLieu }} - {{ $ingredient->TenNguyenLieu }} | Tồn: {{ $ingredient->SoLuongTonKho }} {{ $ingredient->DonViTinh }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Số lượng</label>
                <input data-name="SoLuongDat" type="number" min="1" max="999999" value="1" required>
            </div>
            <button class="btn icon-btn" type="button" onclick="removeItemRow(this)" title="Xóa dòng">x</button>
        </div>
    </template>

    <script>
        let itemIndex = {{ count($oldItems) }};

        function addItemRow() {
            const template = document.getElementById('item-template').content.cloneNode(true);
            template.querySelectorAll('[data-name]').forEach((input) => {
                input.name = `items[${itemIndex}][${input.dataset.name}]`;
                input.removeAttribute('data-name');
            });
            document.getElementById('items').appendChild(template);
            itemIndex++;
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
