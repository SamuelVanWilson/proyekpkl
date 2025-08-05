<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal')->index();

            // PERBAIKAN UTAMA:
            // Semua data dinamis (rekapitulasi & rincian) akan disimpan di sini.
            // Kolom-kolom lama yang tidak fleksibel kita hapus.
            $table->json('data')->nullable(); 

            $table->timestamps();

            // Unique constraint untuk satu laporan per hari per user
            $table->unique(['user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
