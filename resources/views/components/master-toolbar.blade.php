@props(['action', 'placeholder' => 'Cari...', 'addLabel' => 'Tambah'])

<div class="km-bar">
    <form method="GET" action="{{ $action }}" class="km-search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}">
        <button type="submit">Cari</button>
    </form>

    <button type="button" class="km-btn primary" data-km-add>+ {{ $addLabel }}</button>
</div>
