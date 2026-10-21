<x-app-layout>
    <x-page-head title="Pengaturan Akun" sub="Ubah data akun dan password Anda" />

    <div class="row g-3">
        {{-- Data akun --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3" style="font-family:var(--font-display);font-weight:600">Data akun</h2>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success py-2 small" role="status">Perubahan disimpan.</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama lengkap</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="{{ $user->username }}" disabled>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="{{ $user->getRoleNames()->first() ?? '-' }}" disabled>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Ganti password --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3" style="font-family:var(--font-display);font-weight:600">Ganti password</h2>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success py-2 small" role="status">Password berhasil diganti.</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password saat ini</label>
                            <input id="current_password" name="current_password" type="password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password baru</label>
                            <input id="password" name="password" type="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Ulangi password baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Ganti password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
