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
            $table->integer('total_barang_masuk')->default(0);
            $table->integer('total_barang_keluar')->default(0);
            $table->decimal('nilai_barang_masuk', 15, 2)->default(0);
            $table->decimal('nilai_barang_keluar', 15, 2)->default(0);
            $table->integer('total_item')->default(0); // total jenis barang
            $table->integer('total_stok')->default(0); // total keseluruhan stok
            $table->json('detail_per_kategori')->nullable(); // breakdown per kategori
            $table->json('top_products')->nullable(); // produk dengan pergerakan terbanyak
            $table->timestamps();

            // Unique constraint untuk satu laporan per hari per user
            $table->unique(['user_id', 'tanggal']);

            // Additional indexes
            $table->index(['user_id', 'tanggal']);
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
