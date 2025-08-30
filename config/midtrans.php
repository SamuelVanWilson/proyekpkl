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
    // ID merchant Anda, dapat ditemukan pada dashboard Midtrans di menu
    // Settings > Access Key. Nama kunci ini sengaja menggunakan ejaan
    // "mercant" untuk mengikuti contoh dalam dokumentasi.
    'mercant_id' => env('MIDTRANS_MERCHAT_ID'),

    // Client key digunakan di sisi frontend (JavaScript) untuk memanggil Snap.
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // Server key digunakan di sisi backend untuk membuat transaksi Snap.
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    // Aktifkan ke production jika Anda sudah siap menerima pembayaran nyata.
    'is_production' => false,

    // Sanitasi parameter menonaktifkan karakter yang tidak aman dalam permintaan.
    'is_sanitized' => false,

    // Aktifkan 3DSecure untuk transaksi kartu kredit jika diperlukan.
    'is_3ds' => false,
];