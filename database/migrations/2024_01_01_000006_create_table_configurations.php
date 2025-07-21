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
        Schema::create('table_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('table_name')->default('barangs'); // nama tabel yang dikonfigurasi
            $table->json('columns'); // konfigurasi kolom (nama, tipe, lebar, dll)
            $table->json('column_order')->nullable(); // urutan kolom
            $table->json('column_widths')->nullable(); // lebar kolom untuk tampilan
            $table->json('hidden_columns')->nullable(); // kolom yang disembunyikan
            $table->json('filters')->nullable(); // filter default
            $table->json('sorting')->nullable(); // pengaturan sorting default
            $table->boolean('is_default')->default(false); // apakah ini konfigurasi default
            $table->string('configuration_name')->nullable(); // nama konfigurasi
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'table_name']);
            // MySQL has a limit of 64 characters for index names, so we use a shorter name here
            $table->unique(['user_id', 'table_name', 'configuration_name'], 'table_config_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_configurations');
    }
};
