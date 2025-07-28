<?php
// File: app/Http/Controllers/Client/ProfileController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Nomor WhatsApp Anda (hardcode atau ambil dari .env)
        $adminWhatsapp = '6281234567890'; // Ganti dengan nomor Anda

        // Pesan template untuk permintaan ganti token
        $pesanWhatsapp = "Halo Admin, saya {$user->name} ({$user->nama_pabrik}) dengan kode unik {$user->kode_unik} ingin meminta penggantian kode unik.";
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$adminWhatsapp}&text=" . urlencode($pesanWhatsapp);

        // Pastikan Anda membuat view 'client.profil.index'
        return view('client.profil.index', compact('user', 'whatsappUrl'));
    }
}
