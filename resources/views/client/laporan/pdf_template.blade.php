<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        @if(!empty($report->data))
            Laporan Biasa
        @else
            Laporan {{ $report->lokasi }}
        @endif
    </title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .rekap-table td:first-child { font-weight: bold; width: 40%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(!empty($report->data))
                <h2>Laporan Harian</h2>
            @else
                <h2>Laporan Penimbangan</h2>
            @endif
        </div>

        {{-- Jika laporan memiliki data (laporan biasa), tampilkan tabel dinamis; jika tidak, tampilkan rekapitulasi seperti biasa --}}
        @if(!empty($report->data))
            <h3>Informasi Laporan</h3>
            <table class="rekap-table">
                <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td></tr>
            </table>

            <h3>Data Laporan</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        @foreach($report->data['columns'] as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->data['rows'] as $rowIndex => $row)
                        <tr>
                            <td>{{ $rowIndex + 1 }}</td>
                            @foreach($report->data['columns'] as $col)
                                <td>{!! $row[$col] ?? '' !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <h3>Rekapitulasi</h3>
            <table class="rekap-table">
                <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td></tr>
                <tr><td>Lokasi</td><td>{{ $report->lokasi }}</td></tr>
                <tr><td>Pemilik Sawah</td><td>{{ $report->pemilik_sawah }}</td></tr>
                <tr><td>Jumlah Karung</td><td>{{ $report->jumlah_karung }} Karung</td></tr>
                <tr><td>Total Bruto</td><td>{{ number_format($report->total_bruto, 2) }} Kg</td></tr>
                <tr><td>Karung Kosong</td><td>{{ number_format($report->karung_kosong, 2) }} Kg</td></tr>
                <tr><td><strong>Total Netto</strong></td><td><strong>{{ number_format($report->total_netto, 2) }} Kg</strong></td></tr>
                <tr><td>Harga Per Kilo</td><td>Rp {{ number_format($report->harga_per_kilo) }}</td></tr>
                <tr><td>Harga Bruto</td><td>Rp {{ number_format($report->harga_bruto) }}</td></tr>
                <tr><td>Uang Muka</td><td>Rp {{ number_format($report->uang_muka) }}</td></tr>
                <tr><td><strong>Total Uang</strong></td><td><strong>Rp {{ number_format($report->total_uang) }}</strong></td></tr>
            </table>

            <h3>Rincian Timbangan</h3>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Detail Timbangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->rincianBarang as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->data['total'] ?? 'N/A' }} Kg</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
