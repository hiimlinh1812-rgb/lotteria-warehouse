<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Phiếu Xuất Kho - Lotteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header-title { color: #a52a2a; font-weight: bold; }
        .bg-lotteria { background-color: #a52a2a; color: white; }
        /* Style cho combobox thả xuống */
        .search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            max-height: 250px;
            overflow-y: auto;
            border-radius: 0 0 0.375rem 0.375rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .search-item { cursor: pointer; }
        .search-item:hover { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="header-title">Tạo Phiếu Xuất Kho</h4>
            <small class="text-muted">Tìm kiếm và chọn nguyên liệu để thêm vào danh sách xuất</small>
        </div>
        <a href="{{ route('xuatkho.index') }}" class="btn btn-outline-secondary">Danh Sách Phiếu</a>
    </div>

    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body">
            <div class="position-relative">
                <input type="text" id="searchInput" class="form-control form-control-lg border-danger" 
                       placeholder="Gõ tên nguyên liệu để tìm kiếm..." autocomplete="off">
                
                <div id="searchDropdown" class="list-group search-dropdown w-100"></div>
            </div>
        </div>
    </div>

    <form action="{{ route('xuatkho.store') }}" method="POST">
        @csrf 
        <div class="card shadow-sm border-0">
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
</div>

<script>
    // Dữ liệu toàn bộ nguyên liệu (chỉ lấy hàng còn tồn kho để xuất)
    const nguyenLieus = @json($danhSachNguyenLieu->where('SoLuongTonKho', '>', 0)->values());
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        const tableBody = document.getElementById('tableBody');
        const emptyRow = document.getElementById('emptyRow');
        const countBadge = document.getElementById('countBadge');
        
        let selectedItems = new Set(); // Dùng Set để theo dõi các mã NL đã chọn (tránh trùng lặp)

        // 1. Xử lý khi gõ vào ô tìm kiếm
        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            searchDropdown.innerHTML = ''; // Xóa list cũ
            
            if (keyword.length === 0) {
                searchDropdown.style.display = 'none';
                return;
            }

            // Lọc danh sách nguyên liệu theo từ khóa
            const filtered = nguyenLieus.filter(nl => 
                nl.TenNguyenLieu.toLowerCase().includes(keyword) || 
                nl.MaNguyenLieu.toLowerCase().includes(keyword)
            );

            if (filtered.length > 0) {
                filtered.forEach(nl => {
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action search-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
                        <div><strong>${nl.TenNguyenLieu}</strong> <small class="text-muted">(${nl.MaNguyenLieu})</small></div>
                        <span class="badge bg-primary rounded-pill">Tồn: ${nl.SoLuongTonKho} ${nl.DonViTinh}</span>
                    `;
                    
                    // Xử lý sự kiện click vào 1 nguyên liệu trong combobox
                    item.addEventListener('click', function() {
                        addIngredientToTable(nl);
                        searchInput.value = ''; // Xóa ô tìm kiếm
                        searchDropdown.style.display = 'none'; // Ẩn combobox
                        searchInput.focus();
                    });
                    
                    searchDropdown.appendChild(item);
                });
                searchDropdown.style.display = 'block';
            } else {
                searchDropdown.innerHTML = '<div class="list-group-item text-muted">Không tìm thấy nguyên liệu...</div>';
                searchDropdown.style.display = 'block';
            }
        });

        // Ẩn combobox khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });

        // 2. Hàm chèn nguyên liệu xuống DataGridView (Bảng)
        function addIngredientToTable(nl) {
            // Kiểm tra xem đã thêm chưa
            if (selectedItems.has(nl.MaNguyenLieu)) {
                alert('Nguyên liệu này đã có trong danh sách!');
                return;
            }

            // Ẩn dòng "Chưa có nguyên liệu nào"
            if (emptyRow) emptyRow.style.display = 'none';

            // Tạo thẻ <tr> mới
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
            selectedItems.add(nl.MaNguyenLieu); // Đánh dấu là đã chọn
            updateBadge();
        }

        // 3. Xử lý sự kiện bấm nút Xóa trên bảng (Dùng Event Delegation)
        tableBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete')) {
                const maNL = e.target.getAttribute('data-id');
                const row = document.getElementById(`row-${maNL}`);
                
                if (row) {
                    row.remove(); // Xóa thẻ <tr> khỏi giao diện
                    selectedItems.delete(maNL); // Xóa khỏi danh sách theo dõi
                    updateBadge();

                    // Nếu xóa hết thì hiện lại dòng trống
                    if (selectedItems.size === 0 && emptyRow) {
                        emptyRow.style.display = 'table-row';
                    }
                }
            }
        });

        // Hàm cập nhật số lượng đã chọn
        function updateBadge() {
            countBadge.textContent = selectedItems.size + ' đã chọn';
        }
    });
</script>

</body>
</html>