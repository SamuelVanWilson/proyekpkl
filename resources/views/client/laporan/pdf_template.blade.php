<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        @php
            // Judul dokumen: gunakan judul meta jika ada, jika tidak gunakan fallback
            $docTitle = 'Laporan';
            if (!empty($report->data) && isset($report->data['meta']['title']) && trim($report->data['meta']['title']) !== '') {
                $docTitle = $report->data['meta']['title'];
            } elseif (empty($report->data) && !empty($report->lokasi)) {
                $docTitle = 'Laporan ' . $report->lokasi;
            }
        @endphp
        {{ $docTitle }}
    </title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .rekap-table td:first-child { font-weight: bold; width: 40%; }
        .rekap-table td { vertical-align: top; }
        .table-rincian th { text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $meta = $report->data['meta'] ?? [];
                $title = $meta['title'] ?? null;
                $logoPath = $meta['logo'] ?? null;
            @endphp
            @if($logoPath)
                <div style="text-align:center; margin-bottom:10px;">
                    <img src="{{ Storage::url($report->data['meta']['logo']) }}" style="max-height:60px;">
                </div>
            @endif
            <h2>{{ $title ?? 'Laporan Harian' }}</h2>
        </div>

        {{-- Terdapat tiga kemungkinan: laporan advanced (rincian), laporan biasa (rows/columns), atau laporan default (model lawas) --}}
        @if(isset($report->data['rincian']))
            @php
                // Advanced report: gunakan rincian & rekap serta konfigurasi dari TableConfiguration
                $meta = $report->data['meta'] ?? [];
                $detailPosition = $meta['detail_pos'] ?? 'top';
                $rincian = $report->data['rincian'] ?? [];
                $rekap   = $report->data['rekap'] ?? [];
                // Ambil konfigurasi kolom dari database
                $config = \App\Models\TableConfiguration::where('user_id', $report->user_id)
                            ->where('table_name', 'daily_reports')->first();
                $configRincian = $config->columns['rincian'] ?? [];
                $configRekap   = $config->columns['rekap']   ?? [];
            @endphp
            {{-- Informasi Laporan di posisi atas --}}
            @if($detailPosition === 'top')
                <h3>Informasi Laporan</h3>
                <table class="rekap-table">
                    @foreach($configRekap as $field)
                        <tr>
                            <td>{{ $field['label'] ?? $field['name'] }}</td>
                            <td>
                                @php
                                    $val = $rekap[$field['name']] ?? '';
                                    $type = $field['type'] ?? 'text';
                                    $formatted = $val;
                                    switch ($type) {
                                        case 'rupiah':
                                            $formatted = 'Rp ' . number_format((float)$val, 0, ',', '.');
                                            break;
                                        case 'dollar':
                                            $formatted = '$ ' . number_format((float)$val, 2, '.', ',');
                                            break;
                                        case 'kg':
                                            $formatted = number_format((float)$val, 2, '.', ',') . ' Kg';
                                            break;
                                        case 'g':
                                            $formatted = number_format((float)$val, 0, ',', '.') . ' g';
                                            break;
                                        case 'number':
                                            $formatted = number_format((float)$val, 0, ',', '.');
                                            break;
                                    }
                                @endphp
                                {!! $formatted !!}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
            <h3>Data Laporan</h3>
            <table class="table-rincian">
                <thead>
                    <tr>
                        <th style="width:20px;">#</th>
                        @foreach($configRincian as $col)
                            <th>{!! $col['label'] ?? $col['name'] !!}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rincian as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            @foreach($configRincian as $col)
                                <td>{!! $row[$col['name']] ?? '' !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Informasi Laporan di posisi bawah --}}
            @if($detailPosition === 'bottom')
                <h3>Informasi Laporan</h3>
                <table class="rekap-table">
                    @foreach($configRekap as $field)
                        <tr>
                            <td>{{ $field['label'] ?? $field['name'] }}</td>
                            <td>
                                @php
                                    $val = $rekap[$field['name']] ?? '';
                                    $type = $field['type'] ?? 'text';
                                    $formatted = $val;
                                    switch ($type) {
                                        case 'rupiah':
                                            $formatted = 'Rp ' . number_format((float)$val, 0, ',', '.');
                                            break;
                                        case 'dollar':
                                            $formatted = '$ ' . number_format((float)$val, 2, '.', ',');
                                            break;
                                        case 'kg':
                                            $formatted = number_format((float)$val, 2, '.', ',') . ' Kg';
                                            break;
                                        case 'g':
                                            $formatted = number_format((float)$val, 0, ',', '.') . ' g';
                                            break;
                                        case 'number':
                                            $formatted = number_format((float)$val, 0, ',', '.');
                                            break;
                                    }
                                @endphp
                                {!! $formatted !!}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        @elseif(!empty($report->data))
            @php
                // Laporan sederhana (table A/B/C) masih menggunakan rows & columns
                $meta = $report->data['meta'] ?? [];
                $headerRowIndex = isset($meta['header_row']) && $meta['header_row'] > 0 ? $meta['header_row'] - 1 : 0;
                $detailPosition = $meta['detail_pos'] ?? 'top';
                $rows = $report->data['rows'] ?? [];
                $columns = $report->data['columns'] ?? [];
                // Ambil judul kolom dari baris pilihan
                $headerValues = [];
                if (isset($rows[$headerRowIndex])) {
                    foreach ($columns as $col) {
                        $headerValues[] = strip_tags($rows[$headerRowIndex][$col] ?? '');
                    }
                } else {
                    $headerValues = $columns;
                }
            @endphp
            @if($detailPosition === 'top')
                <h3>Informasi Laporan</h3>
                <table class="rekap-table">
                    <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td></tr>
                </table>
            @endif
            <h3>Data Laporan</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        @foreach($headerValues as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $rowIndex => $row)
                        @continue($rowIndex == $headerRowIndex)
                        <tr>
                            <td>{{ $rowIndex + 1 }}</td>
                            @foreach($columns as $col)
                                <td>{!! $row[$col] ?? '' !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($detailPosition === 'bottom')
                <h3>Informasi Laporan</h3>
                <table class="rekap-table">
                    <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td></tr>
                </table>
            @endif
        @else
            {{-- Model lama tanpa data (laporan penimbangan) --}}
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
