@php

    // Urutan mengikuti prototype; Kategori Aset ditambahkan sebagai tab terakhir.
    $tabs = [
        ['Pegawai',       'master.pegawai.index',       'master.pegawai.*'],
        ['Vendor',        'master.vendor.index',        'master.vendor.*'],
        ['Departemen',    'master.departemen.index',    'master.departemen.*'],
        ['Ruangan',       'master.ruangan.index',       'master.ruangan.*'],
        ['Kategori Aset', 'master.kategori-aset.index', 'master.kategori-aset.*'],
    ];
@endphp

<div class="subtabs" role="tablist">
    @foreach ($tabs as [$label, $route, $pattern])
        @if (Route::has($route))
            <a href="{{ route($route) }}" class="subtab {{ request()->routeIs($pattern) ? 'active' : '' }}">{{ $label }}</a>
        @else
            <span class="subtab soon" title="Belum dibuat">{{ $label }}</span>
        @endif
    @endforeach
</div>
