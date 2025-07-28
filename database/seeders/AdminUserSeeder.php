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
        User::firstOrCreate(
            [
                'email' => 'admin@laporanapp.com',
            ],
            [
                'name' => 'Administrator',
                'nama_pabrik' => 'Sistem Admin',
                'email' => 'admin@laporanapp.com',
                
                // Ini adalah kunci untuk login ke halaman admin
                'kode_unik' => 'ADMIN-SUPER', 

                'role' => 'admin', // Menandakan user ini adalah admin
                'is_active' => true,
            ]
        );
    }
}
