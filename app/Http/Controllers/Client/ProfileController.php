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

        // Cek apakah user memiliki langganan aktif dan ambil subscription terakhir
        $latestSubscription = $user->subscriptions()->latest()->first();

        return view('client.profil.index', [
            'user' => $user,
            'subscription' => $latestSubscription,
        ]);
    }

    /**
     * Tampilkan halaman edit profil.
     *
     * Halaman ini terpisah dari tampilan profil utama agar UI lebih bersih.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('client.profil.edit', [
            'user' => $user,
        ]);
    }

    public function show()
    {
        $user = Auth::user();
        // Halaman ini menampilkan pilihan paket langganan. Semua logika pembuatan
        // pesanan dilakukan di method start(). Kita tetap kirim subscription
        // terbaru agar user tahu status mereka (pending atau paid).
        $subscription = $user->subscriptions()->latest()->first();
        $snapToken = null;

        // Jika ada pesanan pending, pastikan Snap token tersedia
        if ($subscription && $subscription->payment_status === 'pending') {
            if (!$subscription->snap_token) {
                $midtransService = new CreateSnapTokenService($subscription);
                $subscription->snap_token = $midtransService->getSnapToken();
                $subscription->save();
            }
            $snapToken = $subscription->snap_token;
        }

        return view('client.subscribe.show', [
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
     * Mulai proses langganan berdasarkan paket yang dipilih.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $plan  Paket yang dipilih: mingguan, bulanan, triwulan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Request $request, string $plan)
    {
        $user = Auth::user();

        // Tentukan harga dan durasi berdasarkan paket
        switch ($plan) {
            case 'mingguan':
                $price = 7000;
                $duration = 7; // hari
                break;
            case 'triwulan':
                $price = 20000;
                $duration = 90;
                break;
            case 'bulanan':
            default:
                $price = 10000;
                $duration = 30;
                $plan = 'bulanan';
                break;
        }

        // Cek apakah sudah ada pesanan pending untuk paket yang sama
        $pending = $user->subscriptions()->where('payment_status', 'pending')->where('plan', $plan)->latest()->first();
        if ($pending) {
            // Jika sudah ada, gunakan pesanan tersebut
            $subscription = $pending;
        } else {
            // Buat pesanan baru
            $subscription = $user->subscriptions()->create([
                'plan' => $plan,
                'price' => $price,
                'duration' => $duration,
                'total_price' => $price,
                'payment_status' => 'pending',
                'subscription_expires_at' => now()->addDays($duration),
            ]);
        }

        // Dapatkan Snap Token dari Midtrans
        if (!$subscription->snap_token) {
            $midtransService = new CreateSnapTokenService($subscription);
            $subscription->snap_token = $midtransService->getSnapToken();
            $subscription->save();
        }

        return redirect()->route('client.subscribe.show')->with([
            'snapToken' => $subscription->snap_token,
            'subscription' => $subscription,
        ]);
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
            'password' => 'nullable|confirmed|min:8',
        ]);

        // Jika password diisi, hash dan simpan
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        // Hapus password dari validated agar tidak di‑update massal
        unset($validated['password']);

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
