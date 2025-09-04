<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom daily_report_id ke tabel pdf_exports.
 *
 * Kolom ini akan mengacu pada laporan harian yang di‑export sehingga
 * admin dapat menelusuri laporan mana yang diunduh. Kolom dibuat
 * nullable untuk menjaga kompatibilitas dengan export lama yang
 * belum memiliki keterkaitan ini.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pdf_exports', function (Blueprint $table) {
            // Tambahkan kolom daily_report_id apabila belum ada
            if (!Schema::hasColumn('pdf_exports', 'daily_report_id')) {
                $table->foreignId('daily_report_id')->nullable()->after('user_id')->constrained('daily_reports')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_exports', function (Blueprint $table) {
            if (Schema::hasColumn('pdf_exports', 'daily_report_id')) {
                $table->dropForeign(['daily_report_id']);
                $table->dropColumn('daily_report_id');
            }
        });
    }
};