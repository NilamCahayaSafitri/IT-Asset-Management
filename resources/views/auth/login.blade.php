<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in - {{ config('app.name', 'ITAM') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:500,600,700|inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="login-screen" style="background: linear-gradient(160deg, rgba(16,28,46,.82), rgba(32,52,79,.70)), url('{{ asset('images/cluster.jpg') }}') center / cover no-repeat;">
    <div class="login-card" style="text-align: center;">
        <div class="login-logo" style="display: flex; flex-direction: column; align-items: center; margin-bottom: 20px;">
            <img src="{{ asset('images/logo-pge.png') }}" alt="Logo Pema Global Energi" style="max-width: 180px; height: auto; margin-bottom: 12px;">
            
            <div style="text-align: center;">
                <div class="login-title" style="font-size: 16px; font-weight: 700; color: #1a1f29;">IT Asset Management</div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success py-2 small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" style="text-align: left;">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="nama@perusahaan.co.id" required autofocus autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••" required autocomplete="current-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small text-muted" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" style="justify-content: center;">Log in</button>
        </form>

        <div class="login-foot" style="margin-top: 20px;">PGE ICT Area Operations &copy; {{ date('Y') }}</div>
    </div>
</div>
</body>
</html>
