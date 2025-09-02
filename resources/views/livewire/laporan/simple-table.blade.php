<div>
    {{-- Pesan Sukses dan Error --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 text-sm font-medium p-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 text-red-800 text-sm font-medium p-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Toolbar format teks --}}
    <div x-data="{ formatOpen: false, alignOpen: false }" class="flex flex-wrap items-center gap-2 mb-4">
        {{-- Format dropdown --}}
        <div class="relative inline-block text-left">
            <button type="button" @click="formatOpen = !formatOpen" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm font-medium flex items-center gap-1">
                <ion-icon name="create-outline" class="text-lg"></ion-icon>
                <span>Format</span>
                <ion-icon name="chevron-down-outline" class="text-xs"></ion-icon>
            </button>
            <div x-show="formatOpen" @click.away="formatOpen = false" class="absolute z-20 mt-1 w-40 bg-white border border-gray-200 rounded-md shadow-lg py-1">
                <a href="#" onclick="document.execCommand('bold', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Tebal</a>
                <a href="#" onclick="document.execCommand('italic', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Miring</a>
                <a href="#" onclick="document.execCommand('underline', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Garis Bawah</a>
                <a href="#" onclick="document.execCommand('strikeThrough', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Coret</a>
                <div class="border-t border-gray-200 my-1"></div>
                {{-- Font family selector inside dropdown --}}
                <div class="px-3 py-2">
                    <label class="block text-xs mb-1">Font</label>
                    <select onchange="document.execCommand('fontName', false, this.value); formatOpen=false;" class="w-full bg-gray-100 border border-gray-200 rounded-md text-sm py-1 px-2">
                        <option value="Arial">Arial</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Courier New">Courier New</option>
                        <option value="Helvetica">Helvetica</option>
                    </select>
                </div>
                <div class="px-3 py-2">
                    <label class="block text-xs mb-1">Ukuran</label>
                    <select onchange="document.execCommand('fontSize', false, this.value); formatOpen=false;" class="w-full bg-gray-100 border border-gray-200 rounded-md text-sm py-1 px-2">
                        <option value="1">8pt</option>
                        <option value="2">10pt</option>
                        <option value="3" selected>12pt</option>
                        <option value="4">14pt</option>
                        <option value="5">18pt</option>
                        <option value="6">24pt</option>
                        <option value="7">36pt</option>
                    </select>
                </div>
            </div>
        </div>
        {{-- Align dropdown --}}
        <div class="relative inline-block text-left">
            <button type="button" @click="alignOpen = !alignOpen" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm font-medium flex items-center gap-1">
                <ion-icon name="text-outline" class="text-lg"></ion-icon>
                <span>Align</span>
                <ion-icon name="chevron-down-outline" class="text-xs"></ion-icon>
            </button>
            <div x-show="alignOpen" @click.away="alignOpen = false" class="absolute z-20 mt-1 w-32 bg-white border border-gray-200 rounded-md shadow-lg py-1">
                <a href="#" onclick="document.execCommand('justifyLeft', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Kiri</a>
                <a href="#" onclick="document.execCommand('justifyCenter', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Tengah</a>
                <a href="#" onclick="document.execCommand('justifyRight', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Kanan</a>
                <a href="#" onclick="document.execCommand('justifyFull', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Justify</a>
            </div>
        </div>
    </div>

    {{-- Input Judul Laporan --}}
    <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-gray-700">Judul Laporan</label>
        <input type="text" id="title" wire:model="title" placeholder="Masukkan judul laporan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

    {{-- Input Tanggal --}}
    <div class="mb-4">
        <label for="date" class="block text-sm font-medium text-gray-700">Tanggal Laporan</label>
        <input type="date" id="date" wire:model="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        @error('date')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tabel Data Dinamis --}}
    <!--
        Gunakan overflow-y-auto dan tinggi maksimum agar tabel tidak membuat halaman terlalu panjang. 
        max-h-96 (24rem) memastikan ketinggian tabel terbatas pada perangkat mobile.
    -->
    <div class="overflow-x-auto overflow-y-auto max-h-96">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-2 px-3 border-b bg-gray-100 text-center">#</th>
                    @foreach ($columns as $col)
                        <th class="py-2 px-3 border-b text-center">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    <tr>
                        <td class="py-2 px-3 border-b bg-gray-50 text-center">{{ $rowIndex + 1 }}</td>
                        @foreach ($columns as $col)
                            <td class="py-1 px-1 border-b border-r">
                                <div
                                    contenteditable="true"
                                    class="min-w-[100px] outline-none p-1"
                                    wire:input.debounce.500ms="updateCell({{ $rowIndex }}, '{{ $col }}', $event.target.innerHTML)"
                                    >{!! $rows[$rowIndex][$col] ?? '' !!}</div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tombol Aksi --}}
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
        {{-- Grup baris: plus dan minus --}}
        <div class="flex justify-between items-center rounded-lg overflow-hidden bg-gray-200 text-gray-700 text-sm font-medium">
            <button type="button" wire:click="addRow" class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1">
                <span class="text-base font-bold">+</span><span>Baris</span>
            </button>
            <span class="h-full w-px bg-gray-300"></span>
            <button type="button" wire:click="removeLastRow" @if(count($rows) <= 1) disabled @endif class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="text-base font-bold">−</span><span>Baris</span>
            </button>
        </div>
        {{-- Grup kolom: plus dan minus --}}
        <div class="flex justify-between items-center rounded-lg overflow-hidden bg-gray-200 text-gray-700 text-sm font-medium">
            <button type="button" wire:click="addColumn" @if(count($columns) >= 26) disabled @endif class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="text-base font-bold">+</span><span>Kolom</span>
            </button>
            <span class="h-full w-px bg-gray-300"></span>
            <button type="button" wire:click="removeLastColumn" @if(count($columns) <= 1) disabled @endif class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="text-base font-bold">−</span><span>Kolom</span>
            </button>
        </div>
        {{-- Simpan --}}
        <button type="button" wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full">
            Simpan
        </button>
        {{-- Preview: tombol non‑aktif jika laporan belum disimpan (reportId null) --}}
        <button
            type="button"
            wire:click="preview"
            @if(!$reportId) disabled @endif
            class="px-4 py-2 rounded-lg text-sm font-medium w-full
                   @if(!$reportId)
                       bg-green-300 text-gray-500 cursor-not-allowed
                   @else
                       bg-green-600 hover:bg-green-700 text-white
                   @endif">
            Preview
        </button>
    </div>

    {{-- Skrip untuk menyimpan draft ke localStorage dan memuatnya kembali saat halaman dimuat --}}
    <script>
        document.addEventListener('livewire:load', function () {
            // Jika sedang membuat laporan baru (reportId null) dan ada draft, muat dari localStorage
            if (!@this.get('reportId')) {
                const draft = localStorage.getItem('simple-report-draft');
                if (draft) {
                    try {
                        const data = JSON.parse(draft);
                        if (data.columns && data.rows) {
                            @this.set('columns', data.columns);
                            @this.set('rows', data.rows);
                            @this.set('title', data.title ?? '');
                            @this.set('date', data.date ?? @this.get('date'));
                        }
                    } catch (e) {}
                }
            } else {
                // Jika laporan sudah disimpan, hapus draft lama
                localStorage.removeItem('simple-report-draft');
            }

            // Update draft setiap kali data tabel berubah. Event tableUpdated dikirim dari Livewire
            document.addEventListener('tableUpdated', function () {
                if (!@this.get('reportId')) {
                    const data = {
                        columns: @this.get('columns'),
                        rows: @this.get('rows'),
                        title: @this.get('title'),
                        date: @this.get('date')
                    };
                    localStorage.setItem('simple-report-draft', JSON.stringify(data));
                }
            });

            // Jika laporan baru saja disimpan, hapus draf
            document.addEventListener('reportSaved', function () {
                localStorage.removeItem('simple-report-draft');
            });
        });
    </script>
</div>