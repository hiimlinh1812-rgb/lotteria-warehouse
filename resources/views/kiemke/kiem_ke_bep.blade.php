@extends('layouts.app')

@section('title', 'Kiểm kê cuối ngày')

@php
    $destroyReasonOptions = [
        '' => '-- Không có hủy --',
        'Quá hạn sử dụng' => 'Quá hạn sử dụng',
        'Hư hỏng do nhiệt độ bếp' => 'Hư hỏng do nhiệt độ bếp',
        'Rơi vãi / Biến dạng vật lý' => 'Rơi vãi / Biến dạng vật lý',
    ];
@endphp

@section('content')
<style>
    .endday-shell {
        max-width: 1220px;
        margin: 0 auto;
    }

    .endday-card {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.25rem 1rem rgba(15, 23, 42, 0.12);
        background: #fff;
    }

    .endday-header {
        background: #ffc107;
        color: #b12a2a;
        font-weight: 800;
        text-align: center;
        padding: 1rem 1.25rem;
        font-size: 1.8rem;
        letter-spacing: 0.02em;
    }

    .endday-table thead th {
        background: #f5e8bf;
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
        border-color: #d6d0c2;
    }

    .endday-table thead th.dark-col {
        background: #21262d;
        color: #fff;
    }

    .sort-toggle {
        width: 100%;
        border: 0;
        background: transparent;
        color: inherit;
        font: inherit;
        font-weight: inherit;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        cursor: pointer;
    }

    .sort-toggle:focus {
        outline: 2px solid rgba(255, 255, 255, 0.45);
        outline-offset: 2px;
    }

    .sort-indicator {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .endday-table tbody td {
        vertical-align: middle;
        border-color: #e2e8f0;
    }

    .endday-table .code-cell {
        width: 130px;
        text-align: center;
        font-weight: 700;
    }

    .endday-table .name-cell {
        min-width: 260px;
        font-weight: 600;
    }

    .endday-table .input-cell,
    .endday-table .select-cell {
        min-width: 180px;
        background: #f9f0cf;
    }

    .endday-table .form-control,
    .endday-table .form-select {
        border-radius: 0.5rem;
    }

    .endday-actions {
        display: flex;
        justify-content: flex-end;
        padding: 0 1rem 1rem;
    }

    .endday-submit {
        min-width: 180px;
    }
</style>

<div class="endday-shell">
    <div class="endday-card">
        <div class="endday-header">📊 LOTTERIA BÀ TRIỆU - KIỂM KÊ CUỐI NGÀY</div>

        <div class="p-3 p-lg-4">
            @if($rejectedReport)
                <div class="alert alert-danger border-start border-4 border-danger shadow-sm">
                    <div class="fw-bold mb-1">Báo cáo trước đó đã bị từ chối</div>
                    <div class="small">Lý do quản lý ghi chú: {{ $rejectedReport->GhiChu ?: 'Số liệu chưa khớp, vui lòng kiểm đếm lại và gửi lại báo cáo.' }}</div>
                </div>
            @endif

            <form action="{{ route('kiemke.bep.store') }}" method="POST">
                @csrf
                @if($rejectedReport)
                    <input type="hidden" name="ma_phieu_cu" value="{{ $rejectedReport->MaPhieuKiemKe }}">
                @endif

                <div class="table-responsive mb-3">
                    <table class="table endday-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="dark-col">
                                    <button type="button" class="sort-toggle" id="sort-ma-nguyen-lieu" data-order="asc">
                                        <span>Mã Nguyên Liệu</span>
                                        <span class="sort-indicator" id="sort-indicator">▲</span>
                                    </button>
                                </th>
                                <th class="dark-col">Tên Nguyên Liệu</th>
                                <th>Số Lượng Hoàn Kho</th>
                                <th>Số Lượng Hàng Hủy</th>
                                <th>Lý Do Tiêu Hủy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nguyenLieuForm as $nl)
                                @php
                                    $selectedReason = old('kiem_ke.' . $nl['ma_nl'] . '.ly_do_huy', $nl['old_ly_do_huy']);
                                @endphp
                                <tr>
                                    <td class="code-cell">{{ $nl['ma_nl'] }}</td>
                                    <td class="name-cell">{{ $nl['ten_nl'] }}</td>
                                    <td class="input-cell">
                                        <input
                                            type="number"
                                            min="0"
                                            class="form-control @error('kiem_ke.' . $nl['ma_nl'] . '.hoan_kho') is-invalid @enderror"
                                            name="kiem_ke[{{ $nl['ma_nl'] }}][hoan_kho]"
                                            value="{{ old('kiem_ke.' . $nl['ma_nl'] . '.hoan_kho', $nl['old_hoan_kho']) }}"
                                            required
                                        >
                                    </td>
                                    <td class="input-cell">
                                        <input
                                            type="number"
                                            min="0"
                                            class="form-control hang-huy-input @error('kiem_ke.' . $nl['ma_nl'] . '.hang_huy') is-invalid @enderror"
                                            name="kiem_ke[{{ $nl['ma_nl'] }}][hang_huy]"
                                            value="{{ old('kiem_ke.' . $nl['ma_nl'] . '.hang_huy', $nl['old_hang_huy']) }}"
                                            data-reason-target="ly-do-{{ $nl['ma_nl'] }}"
                                            required
                                        >
                                    </td>
                                    <td class="select-cell">
                                        <select
                                            id="ly-do-{{ $nl['ma_nl'] }}"
                                            class="form-select destroy-reason-select @error('kiem_ke.' . $nl['ma_nl'] . '.ly_do_huy') is-invalid @enderror"
                                            name="kiem_ke[{{ $nl['ma_nl'] }}][ly_do_huy]"
                                            data-empty-value=""
                                        >
                                            @foreach($destroyReasonOptions as $value => $label)
                                                <option value="{{ $value }}" {{ $selectedReason === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kiem_ke.' . $nl['ma_nl'] . '.ly_do_huy')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">Chưa có dữ liệu nguyên liệu để lập báo cáo kiểm kê cuối ngày.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="endday-actions">
                    <button type="submit" class="btn btn-danger fw-bold endday-submit">Gửi Báo Cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sortButton = document.getElementById('sort-ma-nguyen-lieu');
        const sortIndicator = document.getElementById('sort-indicator');
        const tableBody = document.querySelector('.endday-table tbody');

        if (sortButton && sortIndicator && tableBody) {
            sortButton.addEventListener('click', function () {
                const rows = Array.from(tableBody.querySelectorAll('tr')).filter(function (row) {
                    return row.querySelectorAll('td').length > 1;
                });

                const currentOrder = sortButton.getAttribute('data-order') || 'asc';
                const nextOrder = currentOrder === 'asc' ? 'desc' : 'asc';

                rows.sort(function (rowA, rowB) {
                    const codeA = rowA.querySelector('.code-cell')?.textContent.trim() || '';
                    const codeB = rowB.querySelector('.code-cell')?.textContent.trim() || '';
                    return nextOrder === 'asc'
                        ? codeA.localeCompare(codeB, undefined, { numeric: true })
                        : codeB.localeCompare(codeA, undefined, { numeric: true });
                });

                rows.forEach(function (row) {
                    tableBody.appendChild(row);
                });

                sortButton.setAttribute('data-order', nextOrder);
                sortIndicator.textContent = nextOrder === 'asc' ? '▲' : '▼';
            });
        }

        document.querySelectorAll('.hang-huy-input').forEach(function (input) {
            const targetId = input.getAttribute('data-reason-target');
            const reasonInput = document.getElementById(targetId);
            const emptyValue = reasonInput.getAttribute('data-empty-value');

            const toggleRequired = function () {
                const quantity = Number(input.value || 0);
                if (quantity > 0) {
                    reasonInput.setAttribute('required', 'required');
                    reasonInput.classList.remove('is-valid');
                } else {
                    reasonInput.removeAttribute('required');
                    reasonInput.value = emptyValue;
                }
            };

            toggleRequired();
            input.addEventListener('input', toggleRequired);
        });
    });
</script>
@endsection
