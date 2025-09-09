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
        <input type="text" id="title" wire:model="title" placeholder="Masukkan judul laporan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
        @error('title')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
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
    <div class="overflow-x-auto overflow-y-auto max-h-96">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        class="py-2 px-3 border-b bg-gray-100 text-center cursor-pointer select-none"
                        wire:click="selectRow(null)"
                        @class(['bg-green-100 text-green-800' => $selectedRowIndex === null])
                    >
                        #
                    </th>
                    @foreach ($columns as $colIndex => $col)
                        <th
                            class="py-2 px-3 border-b text-center cursor-pointer select-none"
                            wire:click="selectColumn({{ $colIndex }})"
                            @class(['bg-green-100 text-green-800' => $selectedColumnIndex === $colIndex])
                        >{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $rowIndex => $row)
                    <tr @class(['bg-green-50' => $selectedRowIndex === $rowIndex])>
                        <td
                            class="py-2 px-3 border-b bg-gray-50 text-center cursor-pointer select-none"
                            wire:click="selectRow({{ $rowIndex }})"
                            @class(['bg-green-100 text-green-800' => $selectedRowIndex === $rowIndex])
                        >{{ $rowIndex + 1 }}</td>
                        @foreach ($columns as $colIndex => $col)
                            <td class="py-1 px-1 border-b border-r {{ $selectedColumnIndex === $colIndex ? 'bg-green-50' : '' }}">
                                <div
                                    contenteditable="true"
                                    class="min-w-[100px] outline-none p-1"
                                    wire:input.debounce.1000ms="updateCell({{ $rowIndex }}, '{{ $col }}', $event.target.innerHTML)"
                                    wire:key="cell-{{ $rowIndex }}-{{ $col }}"
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
        <button type="button" wire:click="addRow" class="flex items-center justify-center px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium">
            + Baris
        </button>
        <button type="button" wire:click="addColumn" class="flex items-center justify-center px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium" @if(count($columns) >= 26) disabled @endif>
            + Kolom
        </button>
        <button type="button" wire:click="save" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full">
            Simpan
        </button>
        <button type="button" wire:click="preview" class="px-4 py-2 rounded-lg text-sm font-medium w-full {{ $reportId ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-green-300 text-gray-500 cursor-not-allowed' }}" @if(!$reportId) disabled @endif>
            Preview
        </button>
    </div>

    {{-- Hapus baris/kolom terpilih --}}
    <div class="mt-3 flex flex-col sm:flex-row gap-2">
        <button type="button"
            wire:click="deleteSelectedRow"
            @if(is_null($selectedRowIndex)) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium border border-red-500 text-red-700 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Hapus Baris Terpilih
        </button>
        <button type="button"
            wire:click="deleteSelectedColumn"
            @if(is_null($selectedColumnIndex)) disabled @endif
            class="px-3 py-2 rounded-lg text-sm font-medium border border-red-500 text-red-700 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Hapus Kolom Terpilih
        </button>
    </div>

    {{-- Link ke konfigurasi tabel untuk laporan ini --}}
    @if($reportId)
        <div class="mt-4">
            <a href="{{ route('client.laporan.simple.config.edit', $reportId) }}"
               class="inline-block px-4 py-2 bg-gray-100 hover:bg-gray-200 border rounded-md text-sm font-medium text-gray-800">
                Konfigurasi Tabel
            </a>
        </div>
    @endif

    {{-- Konfigurasi Detail Laporan --}}
    <div class="mt-6 p-4 border border-gray-300 rounded-lg">
        <h3 class="font-semibold mb-3 text-gray-700">Table Configuration (Detail Laporan)</h3>
        {{-- Form konfigurasi field --}}
        <div class="space-y-2 mb-3">
            @foreach($detailSchema as $i => $field)
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                    <input type="text" class="input-modern w-full sm:w-1/3" placeholder="Label"
                           wire:model.lazy="detailSchema.{{ $i }}.label">
                    <select class="input-modern w-full sm:w-1/4" wire:model="detailSchema.{{ $i }}.type">
                        <option value="text">Teks</option>
                        <option value="number">Angka</option>
                        <option value="date">Tanggal</option>
                    </select>
                    {{-- Tombol hapus field --}}
                    @if($field['key'] !== 'title' && $field['key'] !== 'tanggal_raw')
                        <button type="button" wire:click="removeDetailField({{ $i }})"
                            class="px-2 py-1 rounded bg-red-500 text-white text-xs hover:bg-red-600">Hapus</button>
                    @endif
                </div>
            @endforeach
            <button type="button" wire:click="addDetailField"
                    class="mt-2 px-3 py-1.5 rounded bg-gray-200 text-gray-700 text-sm hover:bg-gray-300">+ Tambah Field</button>
        </div>
        {{-- Form pengisian detail --}}
        <div class="space-y-3">
            @foreach($detailSchema as $field)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                    @if($field['type'] === 'number')
                        <input type="number" class="input-modern w-full" wire:model.lazy="detailValues.{{ $field['key'] }}">
                    @elseif($field['type'] === 'date')
                        <input type="date" class="input-modern w-full" wire:model.lazy="detailValues.{{ $field['key'] }}">
                    @else
                        <input type="text" class="input-modern w-full" wire:model.lazy="detailValues.{{ $field['key'] }}">
                    @endif
                </div>
            @endforeach
        </div>
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
                            @this.set('rows',    data.rows);
                            @this.set('title',   data.title ?? '');
                            @this.set('date',    data.date ?? @this.get('date'));
                        }
                    } catch (e) {}
                }
            } else {
                // Jika laporan sudah disimpan, hapus draft lama
                localStorage.removeItem('simple-report-draft');
            }
            // Update draft setiap kali data tabel berubah
            document.addEventListener('tableUpdated', function () {
                if (!@this.get('reportId')) {
                    const data = {
                        columns: @this.get('columns'),
                        rows:    @this.get('rows'),
                        title:   @this.get('title'),
                        date:    @this.get('date'),
                    };
                    localStorage.setItem('simple-report-draft', JSON.stringify(data));
                }
            });
            // Jika laporan baru saja disimpan, hapus draft
            document.addEventListener('reportSaved', function () {
                localStorage.removeItem('simple-report-draft');
            });
        });
    </script>
</div>