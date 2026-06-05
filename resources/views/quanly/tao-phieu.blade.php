@extends('layouts.app')

@section('title', 'Tạo Phiếu Xuất Kho')

@section('content')
<style>
    .header-title { color: #a52a2a; font-weight: bold; }
    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        max-height: 360px;
        overflow-y: auto;
        border-radius: 0 0 0.375rem 0.375rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .search-item { cursor: pointer; }
    .search-item:hover { background-color: #f8f9fa; }
    .search-help { color: #6c757d; font-size: 0.95rem; }
</style>

@php
    $nguyenLieuCoTheXuat = $danhSachNguyenLieu->where('SoLuongTonKho', '>', 0)->values();
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="header-title mb-1">Tạo Phiếu Xuất Kho</h4>
    </div>
    <a href="{{ route('xuatkho.index') }}" class="btn btn-outline-secondary">Danh Sách Phiếu</a>
</div>

<div class="card page-card mb-3">
    <div class="card-body">
        <label for="searchInput" class="form-label fw-semibold text-muted">Chọn nguyên liệu</label>
        <div class="position-relative">
            <input type="text" id="searchInput" class="form-control form-control-lg border-danger"
                   placeholder="Bấm vào đây để xem toàn bộ nguyên liệu hoặc nhập tên/mã để lọc..." autocomplete="off">
            <div id="searchDropdown" class="list-group search-dropdown w-100"></div>
        </div>
        <div class="search-help mt-2">
            Hiện có {{ $nguyenLieuCoTheXuat->count() }} nguyên liệu còn tồn kho trong cơ sở dữ liệu và sẵn sàng để xuất.
        </div>
    </div>
</div>

<form action="{{ route('xuatkho.store') }}" method="POST">
    @csrf
    <div class="card page-card">
        <div class="card-header bg-lotteria d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Danh Sách Nguyên Liệu Chờ Xuất</h6>
            <span class="badge bg-warning text-dark" id="countBadge">0 đã chọn</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="selectedTable">
                <thead class="table-light text-danger">
                    <tr>
                        <th>MÃ NL</th>
                        <th>TÊN NGUYÊN LIỆU</th>
                        <th>NHÓM HÀNG</th>
                        <th>TỒN KHO</th>
                        <th width="150">SỐ LƯỢNG XUẤT</th>
                        <th width="80" class="text-center">XÓA</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr id="emptyRow">
                        <td colspan="6" class="text-center py-5 text-muted">
                            <p class="mb-0">Chưa có nguyên liệu nào được chọn.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white text-end py-3">
            <button type="button" class="btn btn-outline-secondary me-2" onclick="location.reload()">Làm Mới Trang</button>
            <button type="submit" class="btn btn-danger" style="background-color: #a52a2a;">Xác Nhận Xuất Kho</button>
        </div>
    </div>
</form>

<script>
    const nguyenLieus = @json($nguyenLieuCoTheXuat);
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        const tableBody = document.getElementById('tableBody');
        const emptyRow = document.getElementById('emptyRow');
        const countBadge = document.getElementById('countBadge');

        let selectedItems = new Set();

        function renderDropdown(showAll = false) {
            const keyword = searchInput.value.toLowerCase().trim();
            searchDropdown.innerHTML = '';

            if (!showAll && keyword.length === 0) {
                searchDropdown.style.display = 'none';
                return;
            }

            const filtered = nguyenLieus.filter(nl => {
                if (selectedItems.has(nl.MaNguyenLieu)) {
                    return false;
                }

                if (showAll && keyword.length === 0) {
                    return true;
                }

                return nl.TenNguyenLieu.toLowerCase().includes(keyword) ||
                    nl.MaNguyenLieu.toLowerCase().includes(keyword);
            });

            if (filtered.length === 0) {
                const message = selectedItems.size === nguyenLieus.length
                    ? 'Bạn đã chọn hết nguyên liệu có thể xuất.'
                    : 'Không tìm thấy nguyên liệu chưa chọn...';
                searchDropdown.innerHTML = `<div class="list-group-item text-muted">${message}</div>`;
                searchDropdown.style.display = 'block';
                return;
            }

            filtered.forEach(nl => {
                const item = document.createElement('a');
                item.className = 'list-group-item list-group-item-action search-item d-flex justify-content-between align-items-center';
                item.innerHTML = `
                    <div>
                        <strong>${nl.TenNguyenLieu}</strong>
                        <small class="text-muted">(${nl.MaNguyenLieu})</small>
                        <div class="small text-muted">${nl.NhomHang || ''}</div>
                    </div>
                    <span class="badge bg-primary rounded-pill">Tồn: ${nl.SoLuongTonKho} ${nl.DonViTinh}</span>
                `;

                item.addEventListener('click', function() {
                    addIngredientToTable(nl);
                    searchInput.value = '';
                    renderDropdown(true);
                    searchInput.focus();
                });

                searchDropdown.appendChild(item);
            });

            searchDropdown.style.display = 'block';
        }

        function addIngredientToTable(nl) {
            if (selectedItems.has(nl.MaNguyenLieu)) {
                alert('Nguyên liệu này đã có trong danh sách!');
                return;
            }

            if (emptyRow) {
                emptyRow.style.display = 'none';
            }

            const tr = document.createElement('tr');
            tr.id = `row-${nl.MaNguyenLieu}`;
            tr.innerHTML = `
                <td>${nl.MaNguyenLieu}</td>
                <td class="fw-bold text-danger">${nl.TenNguyenLieu}</td>
                <td>${nl.NhomHang}</td>
                <td class="text-primary fw-bold">${nl.SoLuongTonKho} ${nl.DonViTinh}</td>
                <td>
                    <input type="number" name="nguyen_lieu[${nl.MaNguyenLieu}]"
                           class="form-control form-control-sm text-center"
                           min="1" max="${nl.SoLuongTonKho}" value="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="${nl.MaNguyenLieu}" title="Xóa dòng này">
                        Xóa
                    </button>
                </td>
            `;

            tableBody.appendChild(tr);
            selectedItems.add(nl.MaNguyenLieu);
            updateBadge();
        }

        tableBody.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-delete')) {
                return;
            }

            const maNL = e.target.getAttribute('data-id');
            const row = document.getElementById(`row-${maNL}`);

            if (row) {
                row.remove();
                selectedItems.delete(maNL);
                updateBadge();

                if (selectedItems.size === 0 && emptyRow) {
                    emptyRow.style.display = 'table-row';
                }

                if (document.activeElement === searchInput || searchDropdown.style.display === 'block') {
                    renderDropdown(true);
                }
            }
        });

        function updateBadge() {
            countBadge.textContent = `${selectedItems.size} đã chọn`;
        }

        searchInput.addEventListener('focus', function() {
            renderDropdown(true);
        });

        searchInput.addEventListener('click', function() {
            renderDropdown(true);
        });

        searchInput.addEventListener('input', function() {
            renderDropdown(false);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    });
</script>
@endsection
