<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Cari user dengan email admin, jika tidak ada, buat baru.
        // Ini mencegah duplikasi jika seeder dijalankan berkali-kali.
        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@laporan.app',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Ganti 'password' dengan password yang aman
            'role' => 'admin',
            'nomor_telepon' => '628721871821',
            'tanggal_lahir' => '2000-01-01', // Data dummy
            'alamat' => 'DKI Jakarta',      // Data dummy
            'pekerjaan' => 'Administrator',  // Data dummy
        ]);
    }
}
