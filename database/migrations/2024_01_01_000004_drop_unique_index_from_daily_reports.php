<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $table = 'daily_reports';
        $singleIndex = 'daily_reports_user_id_index';
        $uniqueIndex = 'daily_reports_user_id_tanggal_unique';

        // 1) Pastikan ada index tunggal di user_id (supaya FK ke users tetap punya index penopang)
        $hasSingle = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $singleIndex)
            ->exists();

        if (! $hasSingle) {
            Schema::table($table, function (Blueprint $t) use ($singleIndex) {
                $t->index('user_id', $singleIndex);
            });
        }

        // 2) Baru drop unique komposit (user_id, tanggal)
        $hasUnique = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $uniqueIndex)
            ->exists();

        if ($hasUnique) {
            Schema::table($table, function (Blueprint $t) use ($uniqueIndex) {
                $t->dropUnique($uniqueIndex);
            });
        }
    }

    public function down(): void
    {
        $table = 'daily_reports';
        $singleIndex = 'daily_reports_user_id_index';
        $uniqueIndex = 'daily_reports_user_id_tanggal_unique';

        // Pulihkan unique komposit bila belum ada
        $hasUnique = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $uniqueIndex)
            ->exists();

        if (! $hasUnique) {
            Schema::table($table, function (Blueprint $t) use ($uniqueIndex) {
                $t->unique(['user_id','tanggal'], $uniqueIndex);
            });
        }

        // (Opsional) Hapus index tunggal user_id jika kamu ingin rollback bersih
        $hasSingle = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $singleIndex)
            ->exists();

        if ($hasSingle) {
            Schema::table($table, function (Blueprint $t) use ($singleIndex) {
                $t->dropIndex($singleIndex);
            });
        }
    }
};
