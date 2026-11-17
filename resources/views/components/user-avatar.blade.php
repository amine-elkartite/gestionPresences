@props(['name' => 'Administrateur'])
@php
    $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = count($words) > 1
        ? mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1)
        : mb_substr($name, 0, 2);
    $tone = abs(crc32($name)) % 5;
@endphp
<span {{ $attributes->class(['user-avatar', 'avatar-tone-'.$tone]) }} aria-hidden="true">{{ mb_strtoupper($initials) }}</span>
