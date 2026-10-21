@php
    $user = auth()->user();
    $initials = \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)
        ->map(fn ($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IT Asset Management') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:500,600,700|inter:400,500,600|ibm-plex-mono:500&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app">

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="sidebar" id="sidebar">
        @include('layouts.sidebar')
    </aside>

    <div class="main">
        {{-- Topbar / Header Utama --}}
        <div class="top-header" style="height: 60px; background: var(--card); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; flex-shrink: 0;">
            
            {{-- UJUNG KIRI: Tombol Mobile & Judul IT Asset Management --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="button" class="hamburger" data-sidebar-open aria-label="Buka menu">
                    <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" fill="none" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div style="font-family: var(--font-display); font-weight: 700; font-size: 16px; color: var(--navy-900);">
                    IT Asset Management
                </div>
            </div>

            {{-- UJUNG KANAN: Profil User --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;" title="Pengaturan akun">
                    <div style="text-align: right; line-height: 1.2;">
                        <div style="font-size: 12.5px; font-weight: 600; color: var(--text);">{{ $user->name }}</div>
                        <div style="font-size: 10.5px; color: var(--muted);">{{ $user->username }}</div>
                    </div>
                    <div class="avatar" style="background: var(--ember-500);">{{ $initials }}</div>
                </a>
            </div>
        </div>

        <main class="page">
            {{ $slot }}
        </main>
    </div>

    <x-flash />
</div>

<script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        function open()  { sidebar.classList.add('open');    overlay.classList.add('active'); }
        function close() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
        document.querySelectorAll('[data-sidebar-open]').forEach(function (b) { b.addEventListener('click', open); });
        document.querySelectorAll('[data-sidebar-close]').forEach(function (b) { b.addEventListener('click', close); });
        overlay.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    })();
</script>
@stack('scripts')
</body>
</html>