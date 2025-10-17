<div
    wire:key="simple-table-root"
    x-data="{
        formatOpen: false,
        alignOpen: false,
        // Persist the fitTable toggle across Livewire re-renders by storing
        // the value in localStorage. This prevents the table from resetting to
        // scrollable mode when typing in a cell or after saving.
        fitTable: JSON.parse(localStorage.getItem('fit_table_simple') || 'false'),
        scaleX: 1,
        toggleFit() {
            this.fitTable = !this.fitTable;
            // Persist the new state so Alpine reinitialization can restore it
            localStorage.setItem('fit_table_simple', JSON.stringify(this.fitTable));
            this.recalcScale();
        },
        recalcScale() {
            // When Fit Table is active, calculate a horizontal scale for the table.
            // We compute the ratio of container width to table scroll width and
            // apply it directly without clamping so that the entire table always
            // fits inside the container. The scale only affects the width (via
            // transform: scaleX) and does not reduce font size, preserving
            // readability while eliminating horizontal scrolling.
            this.$nextTick(() => {
                if (!this.fitTable) {
                    this.scaleX = 1;
                    return;
                }
                const container = this.$refs.container;
                const table     = this.$refs.table;
                if (container && table) {
                    const cw = container.clientWidth;
                    const tw = table.scrollWidth;
                    if (tw > 0) {
                        const ratio = cw / tw;
                        // Use the exact ratio when it is less than 1. This
                        // squeezes the table horizontally but keeps text size
                        // unchanged.
                        this.scaleX = ratio < 1 ? ratio : 1;
                    } else {
                        this.scaleX = 1;
                    }
                }
            });
        }
    }"
    x-init="recalcScale()"
    x-on:resize.window="recalcScale()"
>
        {{-- Pesan Sukses dan Error --}}
        @if (session('success'))
            <div class="bg-green-100 text-green-800 text-sm font-medium p-3 
rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 text-red-800 text-sm font-medium p-3 rounded-
lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Toolbar format teks --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            {{-- Format dropdown --}}
            <div class="relative inline-block text-left">
                <button type="button" @click="formatOpen = !formatOpen" 
class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm font-medium 
flex items-center gap-1">
                    <ion-icon name="create-outline" class="text-lg"></ion-icon>
                    <span>Format</span>
                    <ion-icon name="chevron-down-outline" class="text-xs"></ion-
icon>
                </button>
                <div x-show="formatOpen" @click.away="formatOpen = false" 
class="absolute z-20 mt-1 w-40 bg-white border border-gray-200 rounded-md 
shadow-lg py-1">
                    <a href="#" onclick="applyCommand('bold'); formatOpen=false;
 return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Tebal</a>
                    <a href="#" onclick="applyCommand('italic'); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Miring</a>
                    <a href="#" onclick="applyCommand('underline'); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Garis Bawah</a>
                    <a href="#" onclick="applyCommand('strikeThrough'); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Coret</a>
                    <div class="border-t border-gray-200 my-1"></div>
                    {{-- Font family selector inside dropdown --}}
                    <div class="px-3 py-2">
                        <label class="block text-xs mb-1">Font</label>
                        <select onchange="applyCommand('fontName', this.value); 
formatOpen=false;" class="w-full bg-gray-100 border border-gray-200 rounded-md 
text-sm py-1 px-2">
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New 
Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Helvetica">Helvetica</option>
                        </select>
                    </div>
                    <div class="px-3 py-2">
                        <label class="block text-xs mb-1">Ukuran</label>
                        <select onchange="applyCommand('fontSize', this.value); 
formatOpen=false;" class="w-full bg-gray-100 border border-gray-200 rounded-md 
text-sm py-1 px-2">
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
                <button type="button" @click="alignOpen = !alignOpen" 
class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm font-medium 
flex items-center gap-1">
                    <ion-icon name="text-outline" class="text-lg"></ion-icon>
                    <span>Align</span>
                    <ion-icon name="chevron-down-outline" class="text-xs"></ion-
icon>
                </button>
                <div x-show="alignOpen" @click.away="alignOpen = false" 
class="absolute z-20 mt-1 w-32 bg-white border border-gray-200 rounded-md 
shadow-lg py-1">
                    <a href="#" onclick="applyCommand('justifyLeft'); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Kiri</a>
                    <a href="#" onclick="applyCommand('justifyCenter'); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Tengah</a>
                    <a href="#" onclick="applyCommand('justifyRight'); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Kanan</a>
                    <a href="#" onclick="applyCommand('justifyFull'); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Justify</a>
                </div>
            </div>
        </div>

        {{-- Kelompok Detail Laporan (Judul, Tanggal, dan kolom tambahan) --}}
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-
gray-200 mb-4">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Detail 
Laporan</h2>
            {{-- Input Judul Laporan --}}
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-
gray-700">Judul Laporan</label>
                <input type="text" id="title" wire:model="title" 
placeholder="Masukkan judul laporan" class="mt-1 block w-full rounded-md border-
gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                @error('title')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Tanggal --}}
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-
gray-700">Tanggal Laporan</label>
                <input type="date" id="date" wire:model="date" class="mt-1 block
  w-full rounded-md border-gray-300 shadow-sm">
                @error('date')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tambahan field detail laporan dari schema konfigurasi. 
                 Hanya ditampilkan di sini (bukan konfigurasi). 
                 Input akan disesuaikan dengan tipe (text/number/date). --}}
            @foreach ($detailSchema as $field)
                @if($field['key'] !== 'title' && $field['key'] !== 
'tanggal_raw')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-
gray-700">{{ $field['label'] }}</label>
                        @if($field['type'] === 'number')
                            <input type="number" class="mt-1 block w-full 
rounded-md border-gray-300 shadow-sm" wire:model.defer="detailValues.{{ 
 $field['key'] }}">
                        @elseif($field['type'] === 'date')
                            <input type="date" class="mt-1 block w-full rounded-
md border-gray-300 shadow-sm" wire:model.defer="detailValues.{{ $field['key'] 
}}">
                        @else
                            <input type="text" class="mt-1 block w-full rounded-
md border-gray-300 shadow-sm" wire:model.defer="detailValues.{{ $field['key'] 
}}">
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Toolbar Aksi (Baris/Kolom, Fit Table, Fullscreen) --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <!-- Tambah Baris -->
            <button type="button" wire:click="addRow" class="flex items-center justify-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">
                <ion-icon name="add-outline" class="text-lg mr-1"></ion-icon>
                <span>+ Baris</span>
            </button>
            <!-- Tambah Kolom -->
            <button type="button" wire:click="addColumn" class="flex items-center justify-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium @if(count($columns) >= 26) opacity-50 cursor-not-allowed @endif" @if(count($columns) >= 26) disabled @endif>
                <ion-icon name="add-outline" class="text-lg mr-1"></ion-icon>
                <span>+ Kolom</span>
            </button>
            <!-- Hapus Baris Terpilih -->
            <button type="button"
                wire:click="deleteSelectedRow"
                @class([
                    'flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-medium border',
                    'border-red-500 text-red-700 hover:bg-red-50' => !is_null($selectedRowIndex),
                    'border-red-300 text-red-300 cursor-not-allowed' => is_null($selectedRowIndex),
                ])
                @if(is_null($selectedRowIndex)) disabled @endif>
                Hapus Baris
            </button>
            <!-- Hapus Kolom Terpilih -->
            <button type="button"
                wire:click="deleteSelectedColumn"
                @class([
                    'flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-medium border',
                    'border-red-500 text-red-700 hover:bg-red-50' => !is_null($selectedColumnIndex),
                    'border-red-300 text-red-300 cursor-not-allowed' => is_null($selectedColumnIndex),
                ])
                @if(is_null($selectedColumnIndex)) disabled @endif>
                Hapus Kolom
            </button>
            <!-- Undo Hapus -->
            <button type="button"
                wire:click="undoDelete"
                @class([
                    'flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-medium border',
                    'border-yellow-500 text-yellow-700 hover:bg-yellow-50' => $undoAvailable,
                    'border-yellow-300 text-yellow-300 cursor-not-allowed' => !$undoAvailable,
                ])
                @if(!$undoAvailable) disabled @endif>
                Undo
            </button>
            <!-- Fit Table Toggle -->
            <button type="button" @click="toggleFit()" class="flex items-center justify-center px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium">
                <template x-if="fitTable">
                    <ion-icon name="contract-outline" class="text-lg mr-1"></ion-icon>
                </template>
                <template x-if="!fitTable">
                    <ion-icon name="expand-outline" class="text-lg mr-1"></ion-icon>
                </template>
                <span x-text="fitTable ? 'Fit OFF' : 'Fit ON'"></span>
            </button>
            <!-- Fullscreen Toggle -->
            <button type="button" @click="toggleFullscreen()" class="flex items-center justify-center px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium">
                <ion-icon name="scan-outline" class="text-lg mr-1"></ion-icon>
                <span>Fullscreen</span>
            </button>
        </div>

        {{-- Tabel Data Dinamis --}}
        <div class="overflow-y-auto max-h-96" x-ref="container"
             :class="fitTable ? 'overflow-x-hidden' : 'overflow-x-auto'">
            <table
                class="min-w-full bg-white border border-gray-200 rounded-lg"
                x-ref="table"
                :style="fitTable ? 'transform: scaleX(' + scaleX + '); transform-origin: left;' : ''"
            >
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="py-2 px-3 border-b bg-gray-100 text-center 
cursor-pointer select-none"
                             wire:click="selectRow(null)"
                            @class(['bg-green-100 text-green-800' => 
$selectedRowIndex === null])
                        >
                            #
                        </th>
                        @foreach ($columns as $colIndex => $col)
                            <th
                                class="py-2 px-3 border-b text-center cursor-
pointer select-none"
                                wire:click="selectColumn({{ $colIndex }})"
                                @class(['bg-green-100 text-green-800' => 
$selectedColumnIndex === $colIndex])
                            >{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $rowIndex => $row)
                        <tr @class(['bg-green-50' => $selectedRowIndex === 
$rowIndex])>
                            <td
                                class="py-2 px-3 border-b bg-gray-50 text-center
  cursor-pointer select-none"
                                wire:click="selectRow({{ $rowIndex }})"
                                @class(['bg-green-100 text-green-800' => 
$selectedRowIndex === $rowIndex])
                            >{{ $rowIndex + 1 }}</td>
                            @foreach ($columns as $colIndex => $col)
                            <td class="py-1 px-1 border-b border-r {{ $selectedColumnIndex === $colIndex ? 'bg-green-50' : '' }}">
                                <div
                                    contenteditable="true"
                                    class="outline-none p-1 text-sm"
                                    :class="fitTable ? 'min-w-[60px]' : 'min-w-[100px]'"
                                    wire:blur="updateCell({{ $rowIndex }}, '{{ $col }}', $event.target.innerHTML)"
                                    wire:key="cell-{{ $rowIndex }}-{{ $col }}"
                                    >{!! $rows[$rowIndex][$col] ?? '' !!}</div>
                            </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Kontrol simpan dan preview (ditempatkan di bawah tabel) --}}
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button type="button" wire:click="save" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full flex items-center justify-center" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Simpan</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </button>
            <button type="button" wire:click="preview" class="px-4 py-2 rounded-lg text-sm font-medium w-full {{ $reportId ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-green-300 text-gray-500 cursor-not-allowed' }}" @if(!$reportId) disabled @endif>
                Preview
            </button>
        </div>

        {{-- Link ke konfigurasi tabel untuk laporan ini --}}
        @if($reportId)
            <div class="mt-4">
                <a href="{{ route('client.laporan.simple.config.edit', 
$reportId) }}"
                   class="inline-block px-4 py-2 bg-gray-100 hover:bg-gray-200 
border rounded-md text-sm font-medium text-gray-800">
                    Konfigurasi Tabel
                </a>
            </div>
        @endif

        {{-- Bagian konfigurasi detail laporan dihapus dari halaman ini. 
Konfigurasi dilakukan di halaman terpisah. --}}

    @once
        <script>
            /**
             * Kembalikan perilaku pemformatan teks ke penggunaan seleksi 
normal.
             * Pengguna harus menyeleksi teks yang ingin diformat. Fungsi ini
             * menjalankan execCommand dan memicu event input agar Livewire
             * menangkap perubahan konten. Kami menambahkan logika untuk
             * memilih seluruh isi sel ketika tidak ada teks yang diseleksi,
             * sehingga perintah seperti align dapat diterapkan ke seluruh
             * konten tanpa perlu seleksi manual.
             */
            window.activeEditableElement = null;
            document.addEventListener('focusin', function (e) {
                if (e.target && e.target.isContentEditable) {
                    window.activeEditableElement = e.target;
                }
            });
            function applyCommand(cmd, value = null) {
                const el = window.activeEditableElement;
                if (!el) return;
                el.focus();
                // Jika tidak ada teks yang diseleksi, pilih seluruh isi sel
                const selection = window.getSelection();
                if (selection && (selection.isCollapsed || selection.type === 'Caret')) {
                    const range = document.createRange();
                    range.selectNodeContents(el);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
                try {
                    document.execCommand(cmd, false, value);
                } catch (e) {
                    // abaikan jika browser tidak mendukung perintah
                }
                // Notifikasi ke Livewire bahwa isi sel berubah
                try {
                    const event = new Event('input', { bubbles: true });
                    el.dispatchEvent(event);
                } catch (e) {
                    const evt = document.createEvent('Event');
                    evt.initEvent('input', true, true);
                    el.dispatchEvent(evt);
                }
            }
        </script>
    @endonce

        {{-- Fitur draft via localStorage dihapus untuk meningkatkan stabilitas --}}
    </div>