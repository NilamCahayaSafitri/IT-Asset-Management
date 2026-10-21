@props(['status'])

@php
    $s = $status instanceof \App\Enums\StatusAset ? $status : \App\Enums\StatusAset::tryFrom((string) $status);
    [$bg, $fg] = $s ? $s->warna() : ['#e7e9ec', '#5b6572'];
@endphp

<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;background:{{ $bg }};color:{{ $fg }}">{{ $s?->label() ?? $status }}</span>
