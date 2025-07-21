 # Struktur Database - Sistem Manajemen Stok Barang

## Overview
Sistem ini menggunakan struktur database yang fleksibel dengan JSON untuk memungkinkan user mengedit kolom tabel sesuai kebutuhan mereka.

## Tabel-Tabel Database

### 1. **users** (Updated)
Tabel untuk menyimpan informasi user/pabrik.

**Kolom Tambahan:**
- `nama_pabrik` (string, nullable) - Nama pabrik
- `lokasi_pabrik` (string, nullable) - Lokasi pabrik
- `kode_unik` (string, unique) - Kode unik seperti password untuk identifikasi
- `nomor_telepon` (string, nullable) - Nomor telepon
- `alamat_lengkap` (text, nullable) - Alamat lengkap pabrik
- `jenis_industri` (string, nullable) - Jenis industri
- `npwp` (string, nullable) - NPWP perusahaan
- `siup` (string, nullable) - SIUP perusahaan
- `informasi_tambahan` (json, nullable) - Data tambahan yang fleksibel
- `is_active` (boolean, default: true) - Status aktif user
- `role` (enum: ['admin', 'user'], default: 'user') - Role user

### 2. **barangs** (Modified)
Tabel utama untuk menyimpan data barang dengan struktur fleksibel.

**Struktur:**
- `id` (bigint, primary key)
- `data` (json) - Semua data barang dalam format JSON (nama, kategori, jumlah, harga, dll)
- `user_id` (foreign key) - Relasi ke user/pabrik
- `timestamps`

**Contoh struktur JSON dalam kolom `data`:**
```json
{
  "nama_barang": "Laptop ASUS",
  "kategori": "Elektronik",
  "jumlah": 50,
  "berat": 2.5,
  "harga_beli": 5000000,
  "harga_jual": 7000000,
  "satuan": "unit",
  "lokasi": "Gudang A",
  "supplier": "PT. Tech Indonesia",
  "custom_field_1": "value1",
  "custom_field_2": "value2"
}
```

### 3. **stock_movements**
Tabel untuk mencatat semua pergerakan stok (masuk/keluar/penyesuaian).

**Kolom:**
- `id` (bigint, primary key)
- `barang_id` (foreign key) - Relasi ke barang
- `user_id` (foreign key) - User yang melakukan transaksi
- `type` (enum: ['masuk', 'keluar', 'penyesuaian']) - Tipe pergerakan
- `quantity` (integer) - Jumlah perubahan
- `data_before` (json, nullable) - Snapshot data sebelum perubahan
- `data_after` (json, nullable) - Snapshot data setelah perubahan
- `reference_no` (string, nullable) - Nomor referensi
- `keterangan` (text, nullable) - Catatan
- `tanggal_transaksi` (date) - Tanggal transaksi
- `timestamps`

### 4. **daily_reports**
Tabel untuk menyimpan laporan harian yang akan digunakan untuk grafik.

**Kolom:**
- `id` (bigint, primary key)
- `user_id` (foreign key)
- `tanggal` (date, indexed)
- `total_barang_masuk` (integer, default: 0)
- `total_barang_keluar` (integer, default: 0)
- `nilai_barang_masuk` (decimal 15,2, default: 0)
- `nilai_barang_keluar` (decimal 15,2, default: 0)
- `total_item` (integer, default: 0) - Total jenis barang
- `total_stok` (integer, default: 0) - Total keseluruhan stok
- `detail_per_kategori` (json, nullable) - Breakdown per kategori
- `top_products` (json, nullable) - Produk dengan pergerakan terbanyak
- `timestamps`

**Unique constraint:** `user_id` + `tanggal`

### 5. **pdf_exports**
Tabel untuk mencatat history export PDF.

**Kolom:**
- `id` (bigint, primary key)
- `user_id` (foreign key)
- `filename` (string) - Nama file PDF
- `type` (string, default: 'stock_report') - Jenis laporan
- `filters` (json, nullable) - Filter yang digunakan
- `data_snapshot` (json, nullable) - Snapshot data yang di-export
- `total_items` (integer, default: 0)
- `total_pages` (integer, default: 0)
- `file_path` (string, nullable) - Path file jika disimpan
- `exported_at` (timestamp)
- `timestamps`

### 6. **table_configurations**
Tabel untuk menyimpan konfigurasi tabel yang dapat disesuaikan oleh user.

**Kolom:**
- `id` (bigint, primary key)
- `user_id` (foreign key)
- `table_name` (string, default: 'barangs')
- `columns` (json) - Konfigurasi kolom
- `column_order` (json, nullable) - Urutan kolom
- `column_widths` (json, nullable) - Lebar kolom
- `hidden_columns` (json, nullable) - Kolom tersembunyi
- `filters` (json, nullable) - Filter default
- `sorting` (json, nullable) - Sorting default
- `is_default` (boolean, default: false)
- `configuration_name` (string, nullable)
- `timestamps`

**Unique constraint:** `user_id` + `table_name` + `configuration_name`

## Keuntungan Struktur Database Ini

1. **Fleksibilitas**: User dapat menambah/mengubah kolom tanpa perlu mengubah struktur database
2. **Multi-tenant**: Setiap user/pabrik memiliki data terpisah
3. **Audit Trail**: Semua perubahan tercatat di stock_movements
4. **Performance**: Index pada kolom-kolom penting untuk query cepat
5. **Scalability**: JSON column memungkinkan penambahan field tanpa migration
6. **Reporting**: Daily reports table memudahkan pembuatan grafik dan analisis

## Cara Menjalankan Migration

```bash
# Jalankan semua migration
php artisan migrate

# Rollback jika perlu
php artisan migrate:rollback

# Fresh migration (hati-hati, akan menghapus semua data)
php artisan migrate:fresh
```

## Catatan Penting

1. Pastikan MySQL/MariaDB mendukung JSON column type
2. Backup database sebelum menjalankan migration di production
3. Kolom `kode_unik` pada users table harus diisi dan unique
4. Semua foreign key menggunakan cascade delete untuk menjaga integritas data
