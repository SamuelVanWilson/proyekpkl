<?php
// This file is a modified copy of the original ProfileController located in
// app/Http/Controllers/Client/ProfileController.php.
//
// It adds a redirect to the profile index page after updating the user and
// passes the success message via session so that the profile page can display
// the alert. No other functionality has been changed.

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
        $latestSubscription = $user->subscriptions()->latest()->first();
        return view('client.profil.index', [
            'user' => $user,
            'subscription' => $latestSubscription,
        ]);
    }

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
        $subscription = $user->subscriptions()->latest()->first();
        $snapToken = null;
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

    public function process(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->where('payment_status', 'pending')->latest()->first();
        if ($subscription) {
            $subscription->payment_status = 'paid';
            $subscription->subscription_expires_at = Carbon::now()->addDays(30);
            $subscription->save();
            $user->subscription_plan = $subscription->plan;
            $user->subscription_expires_at = $subscription->subscription_expires_at;
            $user->save();
            return redirect()->route('client.dashboard')->with('success', 'Langganan Anda telah diaktifkan hingga ' . $subscription->subscription_expires_at->translatedFormat('d F Y') . '.');
        }
        return back()->with('error', 'Tidak ada pesanan langganan yang bisa diproses.');
    }

    public function start(Request $request, string $plan)
    {
        $user = Auth::user();
        switch ($plan) {
            case 'mingguan':
                $price = 7000;
                $duration = 7;
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
        $pending = $user->subscriptions()->where('payment_status', 'pending')->where('plan', $plan)->latest()->first();
        if ($pending) {
            $subscription = $pending;
            if (empty($subscription->number)) {
                $subscription->number = 'SUB-' . strtoupper(uniqid());
            }
            $subscription->total_price = $price;
            $subscription->subscription_expires_at = now()->addDays($duration);
            $subscription->plan = $plan;
            $subscription->save();
        } else {
            $subscription = $user->subscriptions()->create([
                'number' => 'SUB-' . strtoupper(uniqid()),
                'plan' => $plan,
                'total_price' => $price,
                'payment_status' => 'pending',
                'subscription_expires_at' => now()->addDays($duration),
            ]);
        }
        if (!$subscription->snap_token) {
            $midtransService = new CreateSnapTokenService($subscription);
            $subscription->snap_token = $midtransService->getSnapToken();
            $subscription->save();
        }
        return redirect()->route('subscribe.show')->with([
            'snapToken' => $subscription->snap_token,
            'subscription' => $subscription,
        ]);
    }

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

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        unset($validated['password']);
        $user->update($validated);
        // Redirect ke halaman profil utama dengan pesan sukses agar user tahu bahwa perubahan tersimpan.
        return redirect()->route('client.profil.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function deactivate(Request $request)
    {
        $user = Auth::user();
        $user->is_active = false;
        $user->subscription_expires_at = null;
        $user->subscription_plan = null;
        $user->save();
        Auth::logout();
        return redirect()->route('login')->with('success', 'Akun Anda telah dinonaktifkan. Hubungi pengembang untuk aktivasi ulang.');
    }
}