<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const SUPER_ADMIN = 'Super Admin';

    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles')
            ->withCount(['asetMasuk', 'transaksi'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $like = '%'.$request->q.'%';
                $query->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users'           => $users,
            'roleOptions'     => Role::where('guard_name', 'web')->orderBy('name')->pluck('name', 'name'),
            'superAdminAktif' => $this->superAdminAktif()->count(),
        ]);
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'kontak'   => $data['kontak'] ?? null,
            'status'   => $data['status'],
            'password' => $data['password'],   // di-hash otomatis oleh cast "hashed" pada model User
        ]);
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        $roleLama = $user->roles->first()?->name;

        if ($user->is(auth()->user()) && ($data['role'] !== $roleLama || $data['status'] !== 'aktif')) {
            return back()->with('error', 'Anda tidak dapat mengubah role atau menonaktifkan akun Anda sendiri.');
        }

        if ($this->superAdminAktifTerakhir($user) && ($data['role'] !== self::SUPER_ADMIN || $data['status'] !== 'aktif')) {
            return back()->with('error', "{$user->name} adalah satu-satunya Super Admin aktif, jadi role atau statusnya tidak bisa diubah.");
        }

        $user->update([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'kontak'   => $data['kontak'] ?? null,
            'status'   => $data['status'],
        ]);
        $user->syncRoles([$data['role']]);

        if ($data['status'] !== 'aktif') {
            $this->akhiriSesi($user);
        }

        return redirect()->route('users.index')->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    /** Aktifkan / nonaktifkan akun lewat tombol cepat di daftar */
    public function status(User $user)
    {
        $baru = $user->status === 'aktif' ? 'nonaktif' : 'aktif';

        if ($baru === 'nonaktif') {
            if ($user->is(auth()->user())) {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
            if ($this->superAdminAktifTerakhir($user)) {
                return back()->with('error', "{$user->name} adalah satu-satunya Super Admin aktif, jadi tidak bisa dinonaktifkan.");
            }
        }

        $user->update(['status' => $baru]);

        if ($baru === 'nonaktif') {
            $this->akhiriSesi($user);
        }

        return back()->with('success', $baru === 'aktif' ? "Akun {$user->name} diaktifkan." : "Akun {$user->name} dinonaktifkan dan tidak bisa login.");
    }

    /** Admin menetapkan password baru; pengguna diminta login ulang */
    public function password(Request $request, User $user)
    {
        $request->validateWithBag('reset', [
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $user->update(['password' => $request->input('new_password')]);

        if (! $user->is(auth()->user())) {
            $this->akhiriSesi($user);
        }

        return back()->with('success', "Password {$user->name} berhasil direset.");
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang Anda pakai.');
        }

        if ($this->superAdminAktifTerakhir($user)) {
            return back()->with('error', "{$user->name} adalah satu-satunya Super Admin aktif, jadi tidak bisa dihapus.");
        }

        if ($user->asetMasuk()->exists() || $user->transaksi()->exists()) {
            return back()->with('error', "{$user->name} sudah punya riwayat aset masuk atau transaksi, jadi tidak bisa dihapus. Nonaktifkan saja akunnya.");
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$nama} berhasil dihapus.");
    }

    private function superAdminAktif()
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', self::SUPER_ADMIN))->where('status', 'aktif');
    }

    /** True jika $user adalah satu-satunya Super Admin yang masih aktif */
    private function superAdminAktifTerakhir(User $user): bool
    {
        return $user->hasRole(self::SUPER_ADMIN)
            && $user->status === 'aktif'
            && $this->superAdminAktif()->where('id', '!=', $user->id)->doesntExist();
    }

    /** Putuskan sesi login yang sedang berjalan (bila sesi disimpan di database) */
    private function akhiriSesi(User $user): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }
}
