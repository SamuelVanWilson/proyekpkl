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

        // Ambil pesanan langganan yang masih pending jika ada
        $subscription = $user->subscriptions()->where('payment_status', 'pending')->latest()->first();
        $snapToken = null;

        // Hanya generate Snap token jika ada pesanan pending
        if ($subscription) {
            if (!$subscription->snap_token) {
                $midtransService = new CreateSnapTokenService($subscription);
                $snapToken = $midtransService->getSnapToken();
                $subscription->snap_token = $snapToken;
                $subscription->save();
            } else {
                $snapToken = $subscription->snap_token;
            }
        }

        return view('client.subscribe.show', [
            'user' => $user,
            'plan' => $plan,
            'totalPrice' => $totalPrice,
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

    /**
     * Perbarui profil pengguna.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);
        $user->update($validated);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Nonaktifkan akun pengguna.
     */
    public function deactivate(Request $request)
    {
        $user = Auth::user();
        // Tandai user sebagai tidak aktif
        $user->is_active = false;
        // Hanguskan langganan jika ada
        $user->subscription_expires_at = null;
        $user->subscription_plan = null;
        $user->save();

        // Logout user
        Auth::logout();
        return redirect()->route('login')->with('success', 'Akun Anda telah dinonaktifkan. Hubungi pengembang untuk aktivasi ulang.');
    }
}
