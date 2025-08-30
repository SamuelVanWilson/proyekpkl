<div>
    {{-- Pesan Sukses --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 text-sm font-medium p-3 rounded-lg mb-4">
            {{ session('success') }}
            <p class="text-gray-700 text-xs mt-1">Anda dapat melihat atau mengunduh PDF dari halaman histori.</p>
        </div>
    @endif

    {{-- Toolbar format teks --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        {{-- Format teks dasar --}}
        <button type="button" onclick="document.execCommand('bold', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm font-semibold" title="Tebal">B</button>
        <button type="button" onclick="document.execCommand('italic', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm italic" title="Miring">I</button>
        <button type="button" onclick="document.execCommand('underline', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm underline" title="Garis Bawah">U</button>
        <button type="button" onclick="document.execCommand('strikeThrough', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm line-through" title="Coret">S</button>
        {{-- Pemisah --}}
        <span class="mx-1 border-l h-4 border-gray-300"></span>
        {{-- Text alignment --}}
        <button type="button" onclick="document.execCommand('justifyLeft', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm" title="Rata Kiri">L</button>
        <button type="button" onclick="document.execCommand('justifyCenter', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm" title="Rata Tengah">C</button>
        <button type="button" onclick="document.execCommand('justifyRight', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm" title="Rata Kanan">R</button>
        <button type="button" onclick="document.execCommand('justifyFull', false, '')" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm" title="Rata Kanan/Kiri">J</button>
        {{-- Pemisah --}}
        <span class="mx-1 border-l h-4 border-gray-300"></span>
        {{-- Font family --}}
        <select onchange="document.execCommand('fontName', false, this.value)" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm">
            <option value="Arial">Arial</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Courier New">Courier New</option>
            <option value="Helvetica">Helvetica</option>
        </select>
        {{-- Font size --}}
        <select onchange="document.execCommand('fontSize', false, this.value)" class="bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-sm">
            <option value="1">8pt</option>
            <option value="2">10pt</option>
            <option value="3" selected>12pt</option>
            <option value="4">14pt</option>
            <option value="5">18pt</option>
            <option value="6">24pt</option>
            <option value="7">36pt</option>
        </select>
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
    <div class="overflow-x-auto">
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
    <div class="mt-4 flex flex-wrap gap-2">
        <button type="button" wire:click="addRow" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
            + Tambah Baris
        </button>
        <button type="button" wire:click="addColumn" @if (count($columns) >= 26) disabled @endif class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
            + Tambah Kolom
        </button>
        <button type="button" wire:click="save" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Simpan
        </button>
        {{-- Tombol export CSV dihapus sesuai permintaan --}}
    </div>
</div>