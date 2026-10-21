@php
    $menu = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'can' => null,
         'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
        ['label' => 'Data Aset', 'route' => 'aset.index', 'active' => 'aset.*', 'can' => 'kelola-aset',
         'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        ['label' => 'Aset Masuk', 'route' => 'aset-masuk.index', 'active' => 'aset-masuk.*', 'can' => 'kelola-aset',
         'icon' => '<line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>'],
        ['label' => 'Transaksi', 'route' => 'transaksi.index', 'active' => 'transaksi.*', 'can' => 'kelola-transaksi',
         'icon' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>'],
        ['label' => 'Laporan', 'route' => 'laporan.index', 'active' => 'laporan.*', 'can' => 'lihat-laporan',
         'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        ['label' => 'Kelola User', 'route' => 'users.index', 'active' => 'users.*', 'can' => 'kelola-user',
         'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
        ['label' => 'Master Data', 'route' => 'master.index', 'active' => 'master.*', 'can' => 'kelola-master',
         'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
    ];
@endphp

<div class="brand" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 18px 16px; border-bottom: 1px solid rgba(255,255,255,.08); position: relative; text-align: center;">
    
    <button type="button" class="sidebar-close" data-sidebar-close aria-label="Tutup menu" style="position: absolute; right: 12px; top: 12px;">&times;</button>
    
    <div style="background: #ffffff; padding: 6px 14px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
        <img src="{{ asset('images/logo-pge.png') }}" alt="Logo PEMA Global Energi" style="height: 38px; width: auto; display: block;">
    </div>

    <div style="font-size: 10px; color: var(--steel-300); letter-spacing: 0.6px; text-transform: uppercase; font-weight: 600;">
        PGE ICT Area Operations
    </div>
</div>

<nav class="nav" aria-label="Menu utama">
    @foreach ($menu as $item)
        @if (! $item['can'] || $user->can($item['can']))
            @if (Route::has($item['route']))
                @php $active = request()->routeIs($item['active']); @endphp
                <a href="{{ route($item['route']) }}" class="nav-item {{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>{{ $item['label'] }}
                </a>
            @else
                <div class="nav-item soon" title="Halaman ini belum dibuat">
                    <svg viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>{{ $item['label'] }}
                </div>
            @endif
        @endif
    @endforeach
</nav>

<div class="side-foot">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-item as-button logout" style="width: 100%;">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout
        </button>
    </form>
</div>