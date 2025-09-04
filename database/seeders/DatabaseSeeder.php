<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'pro@gmail.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Merdeka No. 45, Jakarta',
            'tanggal_lahir' => '2000-05-12',
            'pekerjaan' => 'Mahasiswa',
            'nomor_telepon' => '081234567890',
            'role' => 'user',
            'is_active' => true,
            'subscription_plan' => 'bulanan',
            'subscription_expires_at' => now()->addMonth(),
            'offer_expires_at' => now()->addWeek(),
        ]);

        User::create([
            'name' => 'Siti Aminah',
            'email' => 'biasa@example.com',
            'password' => Hash::make('password'),
            'alamat' => 'Jl. Diponegoro No. 20, Bandung',
            'tanggal_lahir' => '1998-11-03',
            'pekerjaan' => 'Karyawan',
            'nomor_telepon' => '082345678901',
            'role' => 'user',
            'is_active' => true,
            'subscription_plan' => null,
            'subscription_expires_at' => null,
            'offer_expires_at' => null,
        ]);
    }
}
