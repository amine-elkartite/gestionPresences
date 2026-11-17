@props(['status'])
@php
    $labels = \App\Http\Controllers\AdminDashboardController::STATUS_LABELS;
    $tone = array_key_exists($status, $labels) ? $status : 'default';
@endphp
<span class="status-badge status-badge--{{ $tone }}"><span class="status-dot"></span>{{ $labels[$status] ?? $status }}</span>
