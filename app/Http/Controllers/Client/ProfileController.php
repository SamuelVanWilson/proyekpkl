<?php
// File: app/Http/Controllers/Client/ProfileController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\Midtrans\CreateSnapTokenService;

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

    public function show()
    {
        $user = Auth::user();

        // Tentukan paket default dan harga. Paket bulanan: Rp10.000.
        $plan = 'bulanan';
        $totalPrice = 10000;

        // Cari atau buat pesanan langganan baru untuk user ini yang statusnya masih pending
        $subscription = $user->subscriptions()->where('payment_status', 'pending')->latest()->first();
        if (!$subscription) {
            $number = uniqid('SUB-');
            $subscription = $user->subscriptions()->create([
                'number' => $number,
                'plan' => $plan,
                'total_price' => $totalPrice,
            ]);
        }

        // Jika belum memiliki Snap token, generate menggunakan layanan Midtrans
        if (!$subscription->snap_token) {
            $midtransService = new CreateSnapTokenService($subscription);
            $snapToken = $midtransService->getSnapToken();
            $subscription->snap_token = $snapToken;
            $subscription->save();
        } else {
            $snapToken = $subscription->snap_token;
        }

        return view('client.subscribe.show', [
            'user' => $user,
            'subscription' => $subscription,
            'snapToken' => $snapToken,
        ]);
    }

    /**
     * Proses pembaruan status langganan secara manual (fallback) jika belum ada
     * integrasi callback Midtrans. Method ini akan menandai pesanan terakhir
     * sebagai dibayar dan mengaktifkan langganan.
     */
    public function process(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->where('payment_status', 'pending')->latest()->first();
        if ($subscription) {
            $subscription->payment_status = 'paid';
            $subscription->subscription_expires_at = Carbon::now()->addDays(30);
            $subscription->save();

            // Perbarui juga kolom langganan di tabel users
            $user->subscription_plan = $subscription->plan;
            $user->subscription_expires_at = $subscription->subscription_expires_at;
            $user->save();

            return redirect()->route('client.dashboard')->with('success', 'Langganan Anda telah diaktifkan hingga ' . $subscription->subscription_expires_at->translatedFormat('d F Y') . '.');
        }

        return back()->with('error', 'Tidak ada pesanan langganan yang bisa diproses.');
    }
}
