<?php

return [
    // true  : memindai QR tanpa login menampilkan info singkat aset
    //         (nama, kode, kategori, merk/model, status, lokasi); detail lengkap tetap wajib login.
    // false : memindai QR langsung meminta login.
    'qr_publik' => env('ITAM_QR_PUBLIK', true),
];
