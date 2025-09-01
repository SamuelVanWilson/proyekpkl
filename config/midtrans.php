<?php

/*
|--------------------------------------------------------------------------
| Midtrans Configuration
|--------------------------------------------------------------------------
|
| File konfigurasi ini menyimpan pengaturan dasar untuk integrasi Midtrans.
| Nilai-nilai ini akan dibaca oleh kelas layanan di app/Services/Midtrans
| untuk mengonfigurasi koneksi ke API Snap. Pastikan Anda mengisi variabel
| lingkungan MIDTRANS_CLIENT_KEY dan MIDTRANS_SERVER_KEY pada berkas .env
| sesuai dengan kredensial sandbox atau production yang disediakan oleh
| Midtrans【538212355415917†L305-L332】.
|
*/
return [
    'merchant_id'   => env('MIDTRANS_MERCHANT_ID'),
    // Client key digunakan di sisi frontend (JavaScript) untuk memanggil Snap.
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    // Server key digunakan di sisi backend untuk membuat transaksi Snap.
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    // Aktifkan ke production jika Anda sudah siap menerima pembayaran nyata.
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    // Sanitasi parameter menonaktifkan karakter yang tidak aman dalam permintaan.
    'is_sanitized'  => (bool) (env('MIDTRANS_IS_SANITIZED', true)),
    // Aktifkan 3DSecure untuk transaksi kartu kredit jika diperlukan.
    'is_3ds'        => (bool) (env('MIDTRANS_IS_3DS', true)),
    'snap_url'      => (bool) env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];