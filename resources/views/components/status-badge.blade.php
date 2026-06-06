{{--
    Component: status-badge
    Usage: <x-status-badge :status="$phieuNhan->TrangThai" />
--}}
@php
    $map = [
        'Chờ phê duyệt' => ['class' => 'pending', 'icon' => '📋'],
        'Từ chối' => ['class' => 'rejected', 'icon' => '❌'],
        'Đã hủy' => ['class' => 'cancelled', 'icon' => '🚫'],
        'Chờ nhận hàng' => ['class' => 'approved', 'icon' => '🕐'],
        'Đã nhận hàng' => ['class' => 'received', 'icon' => '✔'],
        'Chờ xử lý' => ['class' => 'processing', 'icon' => '⚠'],
        'Đang đổi trả' => ['class' => 'processing', 'icon' => '🔄'],
        'Đã nhập kho' => ['class' => 'stocked', 'icon' => '📥'],
        'Hoàn tất' => ['class' => 'received', 'icon' => '✅'],
        'Đang xử lý' => ['class' => 'processing', 'icon' => '⏳'],
        'Đã xử lý' => ['class' => 'stocked', 'icon' => '✔'],
        'Chờ nhập' => ['class' => 'pending', 'icon' => '📦'],
        'Đã nhập' => ['class' => 'stocked', 'icon' => '✔'],
        'Còn hạn' => ['class' => 'received', 'icon' => '✔'],
        'Cận hạn' => ['class' => 'processing', 'icon' => '⚠'],
        'Hết hạn' => ['class' => 'rejected', 'icon' => '✖'],
        'Đổi hàng' => ['class' => 'processing', 'icon' => '🔄'],
        'Trả hàng' => ['class' => 'rejected', 'icon' => '↩'],
    ];
    $cfg = $map[$status] ?? ['class' => 'pending', 'icon' => '–'];
@endphp
<span class="status-badge {{ $cfg['class'] }}">{{ $cfg['icon'] }} {{ $status }}</span>
