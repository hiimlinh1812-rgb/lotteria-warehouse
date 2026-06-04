{{--
    Component: status-badge
    Usage: <x-status-badge :status="$phieuNhan->TrangThai" />
--}}
@php
    $map = [
        'Chờ nhận hàng'         => ['class' => 'badge-info',    'icon' => '🕐'],
        'Đã nhận hàng'          => ['class' => 'badge-success', 'icon' => '✔'],
        'Chờ xử lý'             => ['class' => 'badge-warning', 'icon' => '⚠'],
        'Đang xử lý đổi/trả'   => ['class' => 'badge-red',     'icon' => '🔄'],
        'Hoàn tất'              => ['class' => 'badge-success', 'icon' => '✅'],
        'Đang xử lý'            => ['class' => 'badge-warning', 'icon' => '⏳'],
        'Đã xử lý'              => ['class' => 'badge-success', 'icon' => '✔'],
        'Chờ nhập'              => ['class' => 'badge-info',    'icon' => '📦'],
        'Đã nhập'               => ['class' => 'badge-success', 'icon' => '✔'],
        'Còn hạn'               => ['class' => 'badge-success', 'icon' => '✔'],
        'Cận hạn'               => ['class' => 'badge-warning', 'icon' => '⚠'],
        'Hết hạn'               => ['class' => 'badge-danger',  'icon' => '✖'],
        'Đổi hàng'              => ['class' => 'badge-warning', 'icon' => '🔄'],
        'Trả hàng'              => ['class' => 'badge-red',     'icon' => '↩'],
    ];
    $cfg = $map[$status] ?? ['class' => 'badge-gray', 'icon' => '–'];
@endphp
<span class="badge {{ $cfg['class'] }}">{{ $cfg['icon'] }} {{ $status }}</span>
