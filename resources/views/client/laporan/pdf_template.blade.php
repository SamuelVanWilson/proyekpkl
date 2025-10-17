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
                // Ambil path logo dari meta. Template ini memuat logo dari storage (storage/app/public)
                $logoPath = $meta['logo'] ?? null;
                $logoData = null;
                // Jika ada logo, encode sebagai base64 agar DomPDF dapat menampilkannya (mendukung JPG, JPEG, PNG)
                if ($logoPath) {
                    $filePath = storage_path('app/public/' . $logoPath);
                    if (file_exists($filePath)) {
                        $mime     = mime_content_type($filePath);
                        $contents = file_get_contents($filePath);
                        $logoData = 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                }
            @endphp
            @if($logoData)
                <div style="text-align:center; margin-bottom:10px;">
                    <img src="{{ $logoData }}" style="max-height:60px; max-width:220px; object-fit:contain;">
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
                                            // Untuk tipe kilogram tidak ada desimal, gunakan pemisah ribuan untuk konsistensi.
                                            $formatted = number_format((float)$val, 0, ',', '.') . ' Kg';
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
                    @php
                        // Jika tidak ada kolom bernama 'tanggal' pada konfigurasi rekap tetapi model laporan memiliki kolom tanggal,
                        // tampilkan tanggal laporan sebagai fallback. Ini memastikan informasi tanggal tetap muncul di PDF.
                        $existsTanggalField = false;
                        foreach($configRekap as $f) {
                            if (($f['name'] ?? null) === 'tanggal') {
                                $existsTanggalField = true;
                                break;
                            }
                        }
                    @endphp
                    @if(!$existsTanggalField && !empty($report->tanggal))
                        <tr>
                            <td>Tanggal</td>
                            <td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td>
                        </tr>
                    @endif
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
                                            // Untuk tipe kilogram tidak ada desimal, gunakan pemisah ribuan untuk konsistensi.
                                            $formatted = number_format((float)$val, 0, ',', '.') . ' Kg';
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
                    @php
                        // Cek apakah kolom bernama 'tanggal' ada di konfigurasi rekap. Jika tidak ada tetapi entitas
                        // laporan memiliki kolom tanggal, tampilkan tanggal laporan sebagai baris tambahan.
                        $existsTanggalField = false;
                        foreach($configRekap as $f) {
                            if (($f['name'] ?? null) === 'tanggal') {
                                $existsTanggalField = true;
                                break;
                            }
                        }
                    @endphp
                    @if(!$existsTanggalField && !empty($report->tanggal))
                        <tr>
                            <td>Tanggal</td>
                            <td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td>
                        </tr>
                    @endif
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
                @php
                    $detailSchema = $report->data['detail_schema'] ?? [];
                    $detailValues = $report->data['detail_values'] ?? [];
                @endphp
                <table class="rekap-table">
                    @if(!empty($detailSchema))
                        @foreach($detailSchema as $field)
                            @php
                                $key = $field['key'] ?? '';
                                $val = $detailValues[$key] ?? '';
                                $type = $field['type'] ?? 'text';
                                $formatted = $val;
                                if($type === 'date' && !empty($val)) {
                                    try {
                                        $formatted = \Carbon\Carbon::parse($val)->isoFormat('D MMMM Y');
                                    } catch(Exception $e) {
                                        $formatted = $val;
                                    }
                                } elseif($type === 'number' && $val !== '') {
                                    $formatted = number_format((float)$val, 0, ',', '.');
                                }
                            @endphp
                            <tr>
                                <td>{{ $field['label'] }}</td>
                                <td>{{ $formatted }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td></tr>
                    @endif
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
                    @php $rowNo = 0; @endphp
                    @foreach($rows as $rowIndex => $row)
                        @continue($rowIndex == $headerRowIndex)
                        @php $rowNo++; @endphp
                        <tr>
                            <td>{{ $rowNo }}</td>
                            @foreach($columns as $col)
                                <td>{!! $row[$col] ?? '' !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($detailPosition === 'bottom')
                <h3>Informasi Laporan</h3>
                @php
                    $detailSchema = $report->data['detail_schema'] ?? [];
                    $detailValues = $report->data['detail_values'] ?? [];
                @endphp
                <table class="rekap-table">
                    @if(!empty($detailSchema))
                        @foreach($detailSchema as $field)
                            @php
                                $key = $field['key'] ?? '';
                                $val = $detailValues[$key] ?? '';
                                $type = $field['type'] ?? 'text';
                                $formatted = $val;
                                if($type === 'date' && !empty($val)) {
                                    try {
                                        $formatted = \Carbon\Carbon::parse($val)->isoFormat('D MMMM Y');
                                    } catch(Exception $e) {
                                        $formatted = $val;
                                    }
                                } elseif($type === 'number' && $val !== '') {
                                    $formatted = number_format((float)$val, 0, ',', '.');
                                }
                            @endphp
                            <tr>
                                <td>{{ $field['label'] }}</td>
                                <td>{{ $formatted }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('D MMMM Y') }}</td></tr>
                    @endif
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
