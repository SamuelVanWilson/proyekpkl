@extends('layouts.client')

@section('title', 'Pilih Paket Langganan')

@section('content')
<div
    class="p-4 md:p-6"
    x-data="subscriptionTimer('{{ auth()->user()->offer_expires_at }}')"
>
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Buka Potensi Penuh</h1>
            <p class="mt-3 text-lg text-gray-600">Pilih paket yang paling sesuai untuk meningkatkan produktivitas Anda.</p>
        </div>

        {{-- Countdown Timer Penawaran (hanya muncul jika penawaran aktif) --}}
        <div x-show="offerActive" class="mb-8 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-r-lg" role="alert">
            <p class="font-bold">Penawaran Spesial Berakhir Dalam:</p>
            <p class="text-2xl font-mono" x-text="countdown"></p>
        </div>

        {{-- Pilihan Paket --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Paket 7 Hari --}}
            <div class="border border-gray-200 rounded-2xl p-6 flex flex-col">
                <h3 class="text-lg font-semibold text-gray-900">Mingguan</h3>
                <p class="mt-1 text-gray-600">Coba semua fitur premium.</p>
                <p class="mt-6 text-4xl font-bold text-gray-900">Rp 8.000</p>
                <p class="mt-1 text-sm text-gray-500">per 7 hari</p>
                <button class="mt-8 w-full py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition">Pilih Paket</button>
            </div>

            {{-- Paket 1 Bulan (Paling Populer) --}}
            <div class="border-2 border-green-600 rounded-2xl p-6 flex flex-col relative">
                <div class="absolute top-0 -translate-y-1/2 left-1/2 -translate-x-1/2 px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">PALING POPULER</div>
                <h3 class="text-lg font-semibold text-gray-900">Bulanan</h3>
                <p class="mt-1 text-gray-600">Hemat lebih banyak.</p>
                <p class="mt-6">
                    <span class="text-xl text-gray-500 line-through">Rp 15.000</span>
                    <span class="text-4xl font-bold text-gray-900 ml-2">Rp 10.000</span>
                </p>
                <p class="mt-1 text-sm text-gray-500">per bulan</p>
                <button class="mt-8 w-full py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition">Pilih Paket</button>
            </div>

            {{-- Paket 3 Bulan --}}
            <div class="border border-gray-200 rounded-2xl p-6 flex flex-col">
                <h3 class="text-lg font-semibold text-gray-900">Triwulan</h3>
                <p class="mt-1 text-gray-600">Nilai terbaik untuk jangka panjang.</p>
                 <p class="mt-6">
                    <span class="text-xl text-gray-500 line-through">Rp 40.000</span>
                    <span class="text-4xl font-bold text-gray-900 ml-2">Rp 30.000</span>
                </p>
                <p class="mt-1 text-sm text-gray-500">per 3 bulan</p>
                <button class="mt-8 w-full py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition">Pilih Paket</button>
            </div>
        </div>
    </div>
</div>

<script>
    function subscriptionTimer(expiryDate) {
        return {
            expiry: new Date(expiryDate).getTime(),
            countdown: 'Menghitung...',
            offerActive: true,
            init() {
                if (isNaN(this.expiry) || this.expiry < new Date().getTime()) {
                    this.offerActive = false;
                    return;
                }
                this.updateCountdown();
                setInterval(() => {
                    this.updateCountdown();
                }, 1000);
            },
            updateCountdown() {
                const now = new Date().getTime();
                const distance = this.expiry - now;

                if (distance < 0) {
                    this.countdown = 'Penawaran telah berakhir!';
                    this.offerActive = false;
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                this.countdown = `${days}h ${hours}j ${minutes}m ${seconds}d`;
            }
        }
    }
</script>
@endsection
