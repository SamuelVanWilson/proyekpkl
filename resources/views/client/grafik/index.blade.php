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
        {{-- Render grafik dinamis untuk setiap field numerik --}}
        @foreach($datasets as $field => $ds)
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-800">{{ $ds['label'] }}</h2>
            <div class="mt-4">
                <canvas id="chart-{{ $field }}" height="200"></canvas>
            </div>
        </div>
        @endforeach
    @endif
</div>

{{-- Memuat script Chart.js dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    @if(!$labels->isEmpty())
        const labels = @json($labels);
        const datasets = @json($datasets);
        // Daftar warna hijau yang berbeda
        const colors = [
            'rgb(5, 150, 105)',
            'rgb(16, 185, 129)',
            'rgb(4, 120, 87)',
            'rgb(22, 163, 74)',
            'rgb(34, 197, 94)',
            'rgb(52, 211, 153)',
        ];
        let colorIndex = 0;
        Object.keys(datasets).forEach(function(key) {
            const ctx = document.getElementById('chart-' + key).getContext('2d');
            const data = datasets[key].data;
            // Jika kehabisan warna, mulai lagi dari awal
            const color = colors[colorIndex % colors.length];
            colorIndex++;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: datasets[key].label,
                        data: data,
                        borderColor: color,
                        backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: color,
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: color,
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
        });
    @endif
</script>
@endsection
