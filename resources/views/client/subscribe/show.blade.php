@extends('layouts.client')

@section('title', 'Berlangganan')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Paket Berlangganan</h1>

    {{-- Notifikasi pesan --}}
    @if (session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            {{ session('warning') }}
        </div>
    @endif
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <p class="mb-4 text-sm text-gray-700">Pilih paket langganan untuk menikmati fitur premium seperti laporan advanced, grafik data, dan ekspor PDF tanpa batas. Pembayaran diproses melalui Midtrans sandbox.</p>

    @php
        // Tentukan nama plan untuk tampilan
        $planNames = [
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            'triwulan' => '3 Bulan',
        ];
        $planPrices = [
            'mingguan' => 7000,
            'bulanan' => 10000,
            'triwulan' => 20000,
        ];
    @endphp

    @if($subscription && $subscription->payment_status === 'paid')
        {{-- Langganan aktif --}}
        <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
            <p class="text-green-700 font-semibold mb-1">Langganan Aktif</p>
            <p class="text-sm">Paket: <strong>{{ $planNames[$subscription->plan] ?? ucfirst($subscription->plan) }}</strong></p>
            <p class="text-sm">Berlaku hingga: <strong>{{ $subscription->subscription_expires_at?->translatedFormat('d F Y') }}</strong></p>
        </div>
    @elseif($subscription && $subscription->payment_status === 'pending' && isset($snapToken))
        {{-- Pesanan sedang menunggu pembayaran --}}
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg mb-4">
            <p class="text-blue-700 font-semibold">Pembayaran Pending</p>
            <p class="text-sm">Paket: <strong>{{ $planNames[$subscription->plan] ?? ucfirst($subscription->plan) }}</strong></p>
            <p class="text-sm mb-2">Total: <strong>Rp{{ number_format($subscription->total_price, 0, ',', '.') }}</strong></p>
            <button id="pay-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold w-full py-2 rounded-lg">
                Bayar Sekarang
            </button>
        </div>
        {{-- Load script Snap dengan client key dari konfigurasi --}}
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script>
            document.getElementById('pay-button').addEventListener('click', function(e) {
                e.preventDefault();
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        console.log('Pembayaran sukses:', result);
                        window.location.href = '{{ route('subscribe.process') }}';
                    },
                    onPending: function(result) {
                        console.log('Pembayaran menunggu:', result);
                    },
                    onError: function(result) {
                        console.error('Terjadi kesalahan pembayaran:', result);
                    },
                    onClose: function() {
                        console.log('Pembayaran ditutup');
                    }
                });
            });
        </script>
    @else
        {{-- Belum ada pesanan -- tampilkan pilihan paket --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-6 flex flex-col">
                <h2 class="text-lg font-semibold mb-2">Paket Mingguan</h2>
                <p class="text-3xl font-bold text-blue-700 mb-1">Rp7.000</p>
                <ul class="text-sm text-gray-600 mb-4 space-y-1">
                    <li>&bull; Akses laporan advanced</li>
                    <li>&bull; Fitur grafik data</li>
                    <li>&bull; Kuota ekspor PDF tanpa batas</li>
                </ul>
                <form method="POST" action="{{ route('client.subscribe.plan', 'mingguan') }}" class="mt-auto">
                    @csrf
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Pilih</button>
                </form>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 flex flex-col">
                <h2 class="text-lg font-semibold mb-2">Paket Bulanan</h2>
                <p class="text-3xl font-bold text-blue-700 mb-1">Rp10.000</p>
                <ul class="text-sm text-gray-600 mb-4 space-y-1">
                    <li>&bull; Akses laporan advanced</li>
                    <li>&bull; Fitur grafik data</li>
                    <li>&bull; Kuota ekspor PDF tanpa batas</li>
                    <li>&bull; Kontrol laporan per bulan</li>
                </ul>
                <form method="POST" action="{{ route('client.subscribe.plan', 'bulanan') }}" class="mt-auto">
                    @csrf
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Pilih</button>
                </form>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 flex flex-col">
                <h2 class="text-lg font-semibold mb-2">Paket 3 Bulan</h2>
                <p class="text-3xl font-bold text-blue-700 mb-1">Rp20.000</p>
                <ul class="text-sm text-gray-600 mb-4 space-y-1">
                    <li>&bull; Akses laporan advanced</li>
                    <li>&bull; Fitur grafik data</li>
                    <li>&bull; Kuota ekspor PDF tanpa batas</li>
                    <li>&bull; Lebih hemat per bulan</li>
                </ul>
                <form method="POST" action="{{ route('client.subscribe.plan', 'triwulan') }}" class="mt-auto">
                    @csrf
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Pilih</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection