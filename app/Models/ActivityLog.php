<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    // catatan: tabel activity_logs di migration kamu sekarang cuma punya id + timestamps,
    // jadi belum ada kolom yang bisa diisi. Tambahkan kolom (user, aktivitas, dst) di migration dulu
    // baru isi $fillable di sini.
    protected $fillable = [];
}
