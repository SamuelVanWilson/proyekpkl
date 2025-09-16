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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // KEMBALIKAN KOLOM PASSWORD

            // Kolom kustom yang kita butuhkan
            $table->string('alamat')->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('nomor_telepon')->nullable();

            $table->enum('role', ['admin', 'user'])->default('user');
            $table->boolean('is_active')->default(true);

            // --- KOLOM BARU UNTUK LANGGANAN ---
            $table->string('subscription_plan')->nullable(); // e.g., 'bulanan', 'mingguan'
            $table->timestamp('subscription_expires_at')->nullable(); // Tanggal kadaluarsa langganan
            $table->timestamp('offer_expires_at')->nullable(); // Tanggal kadaluarsa penawaran spesial

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
