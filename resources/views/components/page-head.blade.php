@props(['title', 'sub' => null])

<div class="page-head">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if ($sub)
            <div class="page-sub">{{ $sub }}</div>
        @endif
    </div>
    {{ $slot }}
</div>
