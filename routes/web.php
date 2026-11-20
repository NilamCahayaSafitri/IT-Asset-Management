<?php

use App\Http\Controllers\AsetController;
use App\Http\Controllers\AsetMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\KategoriAsetController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Tujuan QR aset (lihat Aset::urlScan). Terbuka untuk umum: pengguna yang berhak langsung ke detail lengkap,
// tamu melihat info singkat (diatur lewat ITAM_QR_PUBLIK di .env).
Route::get('a/{kode}', [AsetController::class, 'scan'])
    ->where('kode', '[A-Za-z0-9-]+')
    ->middleware('throttle:60,1')
    ->name('aset.scan');

Route::middleware('auth')->group(function () {
    // Bawaan Breeze (menu "Pengaturan" / klik profil di sidebar)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
     | Halaman SEMENTARA (placeholder) untuk menu yang modulnya belum dibuat.
     | Saat modulnya jadi, hapus Route::view-nya dan ganti dengan route controller
     | memakai NAMA ROUTE yang sama.
     */
    Route::middleware('permission:kelola-aset')->group(function () {
        Route::resource('aset', AsetController::class)
            ->parameters(['aset' => 'aset'])
            ->only(['index', 'show', 'update', 'destroy']);

        Route::resource('aset-masuk', AsetMasukController::class)
            ->parameters(['aset-masuk' => 'asetMasuk'])
            ->only(['index', 'store', 'destroy']);
    });

    // Transaksi: surat Equipment Allocation Form (permission: kelola-transaksi)
    Route::middleware('permission:kelola-transaksi')->group(function () {
        Route::get('transaksi/{transaksi}/cetak', [TransaksiController::class, 'cetak'])->name('transaksi.cetak');
        Route::get('transaksi/{transaksi}/pdf', [TransaksiController::class, 'pdf'])->name('transaksi.pdf');
        Route::patch('transaksi/{transaksi}/selesai', [TransaksiController::class, 'selesai'])->name('transaksi.selesai');
        Route::resource('transaksi', TransaksiController::class)
            ->parameters(['transaksi' => 'transaksi'])
            ->only(['index', 'store', 'show']);
    });

    Route::view('/laporan', 'placeholder', [
        'title' => 'Laporan',
        'sub'   => 'Rekap aset dan transaksi',
        'plan'  => ['Laporan per kategori, status, dan ruangan', 'Export Excel dan PDF'],
    ])->middleware('permission:lihat-laporan')->name('laporan.index');

    // Kelola User (permission: kelola-user, hanya Super Admin)
    Route::middleware('permission:kelola-user')->group(function () {
        Route::patch('users/{user}/status', [UserController::class, 'status'])->name('users.status');
        Route::patch('users/{user}/password', [UserController::class, 'password'])->name('users.password');
        Route::resource('users', UserController::class)
            ->parameters(['users' => 'user'])
            ->only(['index', 'store', 'update', 'destroy']);
    });

    // Master Data: satu menu, banyak tab (permission: kelola-master)
    Route::middleware('permission:kelola-master')
        ->prefix('master-data')
        ->name('master.')
        ->group(function () {
            Route::redirect('/', '/master-data/pegawai')->name('index');

            Route::resource('pegawai', PegawaiController::class)
                ->parameters(['pegawai' => 'pegawai'])
                ->only(['index', 'store', 'update', 'destroy']);

            Route::resource('vendor', VendorController::class)
                ->parameters(['vendor' => 'vendor'])
                ->only(['index', 'store', 'update', 'destroy']);

            Route::resource('departemen', DepartemenController::class)
                ->parameters(['departemen' => 'departemen'])
                ->only(['index', 'store', 'update', 'destroy']);

            Route::resource('ruangan', RuanganController::class)
                ->parameters(['ruangan' => 'ruangan'])
                ->only(['index', 'store', 'update', 'destroy']);

            Route::resource('kategori-aset', KategoriAsetController::class)
                ->parameters(['kategori-aset' => 'kategoriAset'])
                ->only(['index', 'store', 'update', 'destroy']);
        });
});

require __DIR__.'/auth.php';
