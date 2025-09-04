<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk menghapus constraint unik pada tabel daily_reports.
 *
 * Secara default tabel daily_reports menggunakan kombinasi (user_id, tanggal)
 * sebagai unique key sehingga setiap pengguna hanya dapat memiliki satu laporan
 * per hari. Permintaan pengguna adalah mendukung banyak laporan per hari,
 * sehingga kita perlu menghapus index unik tersebut. Down method akan
 * mengembalikan constraint unik sehingga perubahan ini dapat di‑rollback
 * jika diperlukan.
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menghapus constraint unik.
     */
    public function up(): void
    {
        /*
         * Ketika tabel daily_reports dibuat, kombinasi (user_id, tanggal) diberi
         * index unik untuk mencegah lebih dari satu laporan per hari. Namun
         * index ini juga dijadikan acuan untuk foreign key user_id. Jika kita
         * menghapus index unik ini tanpa menyediakan index cadangan untuk
         * kolom user_id, MySQL akan menolak karena foreign key membutuhkan
         * index. Solusinya: tambahkan index biasa pada kolom user_id lebih
         * dulu, kemudian hapus index unik. Setelah itu, foreign key akan
         * menggunakan index baru dan kita bisa memiliki beberapa laporan per hari.
         */
        Schema::disableForeignKeyConstraints();
        Schema::table('daily_reports', function (Blueprint $table) {
            // Tambah index biasa pada kolom user_id agar foreign key tetap valid
            $table->index('user_id', 'daily_reports_user_id_index');
            // Hapus index unik kombinasi user_id dan tanggal jika ada
            $table->dropUnique('daily_reports_user_id_tanggal_unique');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Batalkan migrasi dengan menambahkan kembali constraint unik.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            // Tambahkan kembali unique index jika belum ada
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('daily_reports');
            if (!array_key_exists('daily_reports_user_id_tanggal_unique', $indexes)) {
                $table->unique(['user_id', 'tanggal']);
            }
        });
    }
};