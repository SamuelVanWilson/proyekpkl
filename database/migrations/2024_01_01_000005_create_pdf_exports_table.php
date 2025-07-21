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
        Schema::create('pdf_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('type')->default('stock_report'); // jenis laporan
            $table->json('filters')->nullable(); // filter yang digunakan saat export
            $table->json('data_snapshot')->nullable(); // snapshot data yang di-export
            $table->integer('total_items')->default(0);
            $table->integer('total_pages')->default(0);
            $table->string('file_path')->nullable(); // path file jika disimpan di server
            $table->timestamp('exported_at');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'exported_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_exports');
    }
};
