Fitur yang Harus Ada:
1. User Authentication:
Admin bisa melihat data client
Terdapat fitur login untuk client yang dubutuhkan (username dan kodeunik)
Gunakan Laravel Breeze atau Laravel UI

2. Data Barang:
Dapat Mengedit , Menghapus, Menambahkan, dan Mencari Data Barang
Terdapat perhitungan otomatis (seperti kakulator)
Data disimpan ke database MySQL (jadi ada tombol save nya juga)
Alangkah baiknya di database menggunakan tipe data json

3. Preview PDF:
Setelah data diinput, ada tombol Preview PDF
Preview berupa tampilan iframe (PDF hasil render)
Tidak langsung download

4. Export PDF:
Jika preview sudah sesuai, user bisa klik Download PDF
PDF harus sesuai dengan data terbaru
Gunakan package barryvdh/laravel-dompdf

5. Live Sinkronisasi:
Setiap data diubah (edit barang), PDF preview akan diperbarui saat tombol Preview diklik
Tidak perlu reload seluruh halaman

6. UI:
Gunakan Blade
Menngunakan TailwindCSS 
Tampilan CRUD harus memiliki tampilan excel
Terdapat Template Formulir Nya Untuk Menaruh Perhitungan Otomatis

7. Graphic Chart data harian:
Menggunakan library Laravel ChartJS
Grafik Data Harian harus terhubung dengan yang ada di database

9. Kriteria Teknis:
Laravel 11 kompatibel
Semua library harus open-source dan gratis
Menggunakan livewire
boleh menggunakan javascript
