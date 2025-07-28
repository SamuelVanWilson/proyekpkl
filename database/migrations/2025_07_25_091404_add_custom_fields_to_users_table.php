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
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom 'name' dan 'password' jika tidak akan dipakai, 
            // ganti dengan 'kode_unik' sebagai kredensial utama.
            // Jika 'email' tetap unik untuk tiap klien, biarkan.
            $table->dropColumn('password'); 

            $table->string('nama_pabrik')->nullable()->after('email');
            $table->string('lokasi_pabrik')->nullable()->after('nama_pabrik');
            $table->string('kode_unik')->unique()->after('lokasi_pabrik'); // Wajib ada dan unik
            $table->string('nomor_telepon')->nullable()->after('kode_unik');
            $table->text('alamat_lengkap')->nullable()->after('nomor_telepon');
            $table->enum('role', ['admin', 'user'])->default('user')->after('alamat_lengkap');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
