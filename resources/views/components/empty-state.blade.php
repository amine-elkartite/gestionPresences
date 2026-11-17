@props(['title', 'description' => '', 'icon' => 'calendar-check-2'])
<div {{ $attributes->class('empty-state') }}>
    <span class="empty-icon"><x-icon :name="$icon" /></span>
    <h3>{{ $title }}</h3>
    @if($description)<p>{{ $description }}</p>@endif
    {{ $slot }}
</div>
