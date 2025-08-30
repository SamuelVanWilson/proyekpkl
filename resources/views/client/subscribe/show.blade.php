@extends('layouts.client')

@section('title', 'Berlangganan')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Paket Berlangganan</h1>

        @if (session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                {{ session('warning') }}
            </div>
        @endif

        <p class="mb-4">
            Pilih paket langganan untuk menikmati fitur premium seperti laporan advanced, grafik data,
            dan ekspor PDF. Selama masa pengembangan, pembayaran akan dilakukan via sandbox Midtrans.
        </p>

        @isset($subscription)
            <div class="mb-4">
                <p class="mb-2">Paket dipilih: <strong>{{ ucfirst($subscription->plan) }}</strong></p>
                <p>Total yang harus dibayar: <strong>Rp{{ number_format($subscription->total_price, 0, ',', '.') }}</strong></p>
            </div>
        @endisset

        {{-- Tombol bayar menggunakan Snap Midtrans --}}
        @if (isset($snapToken) && $subscription->payment_status === 'pending')
            <button id="pay-button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Bayar Sekarang
            </button>

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
        @elseif (isset($subscription) && $subscription->payment_status === 'paid')
            <p class="text-green-600 font-semibold">Langganan Anda telah aktif hingga {{ $subscription->subscription_expires_at?->translatedFormat('d F Y') }}</p>
        @endif
    </div>
@endsection