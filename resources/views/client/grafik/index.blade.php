@extends('layouts.client')

@section('title', 'Grafik Laporan')

@section('content')

{{-- Header Halaman --}}
<div class="bg-white pt-10 pb-6 px-4 safe-area-top border-b border-gray-200 sticky top-0 z-10">
    <h1 class="text-3xl font-bold text-gray-900 text-center">
        Analitik
    </h1>
    <p class="text-center text-sm text-gray-500 mt-1">Laporan 30 hari terakhir</p>
</div>

{{-- Konten Grafik --}}
<div class="p-4 space-y-6">
    
    @if($labels->isEmpty())
        {{-- Tampilan jika tidak ada data laporan --}}
        <div class="text-center py-20 px-4">
            <ion-icon name="analytics-outline" class="text-5xl text-gray-300 mx-auto"></ion-icon>
            <h3 class="mt-2 text-lg font-medium text-gray-800">Data Tidak Cukup</h3>
            <p class="mt-1 text-sm text-gray-500">
                Buat laporan terlebih dahulu untuk melihat grafik.
            </p>
        </div>
    @else
        {{-- Grafik Total Uang --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-800">Pendapatan (Rp)</h2>
            <div class="mt-4">
                <canvas id="grafikTotalUang" height="200"></canvas>
            </div>
        </div>

        {{-- Grafik Total Netto --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-800">Total Berat Netto (Kg)</h2>
            <div class="mt-4">
                <canvas id="grafikTotalNetto" height="200"></canvas>
            </div>
        </div>
    @endif
</div>

{{-- Memuat script Chart.js dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Pastikan script ini hanya berjalan jika ada data
    @if(!$labels->isEmpty())
        const labels = @json($labels);
        const dataTotalUang = @json($dataTotalUang);
        const dataTotalNetto = @json($dataTotalNetto);

        // --- Konfigurasi Grafik Pendapatan ---
        const ctxUang = document.getElementById('grafikTotalUang').getContext('2d');
        new Chart(ctxUang, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Uang (Rp)',
                    data: dataTotalUang,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // --- Konfigurasi Grafik Berat Netto ---
        const ctxNetto = document.getElementById('grafikTotalNetto').getContext('2d');
        new Chart(ctxNetto, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Netto (Kg)',
                    data: dataTotalNetto,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: 'rgb(16, 185, 129)',
                }]
            },
             options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    @endif
</script>
@endsection
