<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus unique index (user_id, tanggal) jika ada.
     */
    public function up(): void
    {
        // Nama index yang mau di-drop (biasanya auto: {table}_{col1}_{col2}_unique)
        $indexName = 'daily_reports_user_id_tanggal_unique';

        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = database()')
            ->where('table_name', 'daily_reports')
            ->where('index_name', $indexName)
            ->exists();

        if ($exists) {

            DB::statement("ALTER TABLE `daily_reports` DROP INDEX `$indexName`");
        }
    }

    /**
     * Tambahkan kembali unique index kalau belum ada (untuk rollback).
     */
    public function down(): void
    {
        $indexName = 'daily_reports_user_id_tanggal_unique';

        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = database()')
            ->where('table_name', 'daily_reports')
            ->where('index_name', $indexName)
            ->exists();

        if (! $exists) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->unique(['user_id', 'tanggal'], 'daily_reports_user_id_tanggal_unique');
            });
        }
    }
};
