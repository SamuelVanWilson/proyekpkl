@extends('layouts.app')
@section('content')
<div class="max-w-lg mx-auto">
    @include('auth.register.partials.steps', ['active' => 4])

    <h1 class="text-2xl font-semibold mb-4">Persetujuan Penggunaan Data</h1>

    {{-- Kebijakan lengkap + scrollable --}}
    <section
      class="bg-white rounded-xl border p-5 md:p-6 space-y-6 text-sm leading-6 max-h-[420px] overflow-y-auto pr-2 policy-scroll"
      tabindex="0"
      aria-label="Ketentuan, Kebijakan Privasi, & Keamanan Data {{ config('app.name', 'Aplikasi') }}">
      <!-- Ringkasan Singkat -->
      <div class="space-y-2">
        <h3 class="text-base font-semibold">Ringkasan Kebijakan</h3>
        <ul class="list-disc pl-5 space-y-1">
          <li>Kami mengumpulkan data yang Anda berikan (nama, tanggal lahir, alamat/domisi­li, pekerjaan, nomor telepon, email) serta data teknis (log, perangkat, cookies) untuk mengoperasikan dan meningkatkan layanan.</li>
          <li>Kami tidak menjual data Anda. Berbagi data hanya kepada penyedia layanan pendukung yang terikat kewajiban kerahasiaan.</li>
          <li>Anda berhak mengakses, memperbarui, menghapus, atau menarik persetujuan pemrosesan data Anda.</li>
          <li>Keamanan: enkripsi saat transit (HTTPS/TLS), hashing kata sandi, pembatasan akses, audit log, dan pencadangan berkala.</li>
          <li>Kontak: <a href="mailto:{{ config('mail.from.address') }}" class="underline">{{ config('mail.from.address') }}</a>. Terakhir diperbarui: <time datetime="{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}">{{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}</time>.</li>
        </ul>
      </div>

      <!-- Ketentuan Layanan -->
      <details class="group">
        <summary class="cursor-pointer font-semibold flex items-center justify-between">
          Ketentuan Layanan (Terms of Service)
          <span class="text-xs text-gray-500 group-open:hidden">klik untuk baca</span>
          <span class="text-xs text-gray-500 hidden group-open:inline">klik untuk tutup</span>
        </summary>
        <div class="mt-3 space-y-3">
          <p>Dokumen ini mengatur penggunaan aplikasi/website <b>{{ config('app.name', 'Aplikasi') }}</b> yang dioperasikan oleh <b>[NAMA PERUSAHAAN/DEVELOPER]</b> (“kami”). Dengan membuat akun atau menggunakan layanan, Anda menyetujui ketentuan ini.</p>
          <ol class="list-decimal pl-5 space-y-2">
            <li><b>Akses & Akun.</b> Anda bertanggung jawab atas kerahasiaan kredensial; aktivitas pada akun dianggap dilakukan oleh pemegang akun.</li>
            <li><b>Penggunaan yang Dilarang.</b> Dilarang aktivitas melanggar hukum, mengganggu sistem, scraping berlebihan, atau menyalahgunakan fitur.</li>
            <li><b>Kekayaan Intelektual.</b> Materi pada layanan dilindungi hukum. Penggunaan di luar izin tertulis dilarang.</li>
            <li><b>Perubahan Layanan.</b> Kami dapat mengubah/menutup fitur dengan pemberitahuan wajar.</li>
            <li><b>Penghentian.</b> Akun dapat ditangguhkan/ditutup bila melanggar ketentuan atau demi keamanan.</li>
            <li><b>Penafian.</b> Layanan disediakan “apa adanya”; kami berupaya wajar menjaga akurasi & ketersediaan.</li>
            <li><b>Pembatasan Tanggung Jawab.</b> Sepanjang diizinkan hukum, tanggung jawab kami dibatasi pada jumlah yang Anda bayarkan (jika ada) untuk 3 bulan terakhir.</li>
            <li><b>Hukum yang Berlaku.</b> Tunduk pada hukum Indonesia. Sengketa: musyawarah.</li>
            <li><b>Kontak.</b> <a href="mailto:{{ config('mail.from.address') }}" class="underline">{{ config('mail.from.address') }}</a>.</li>
          </ol>
        </div>
      </details>

      <!-- Kebijakan Privasi -->
      <details class="group">
        <summary class="cursor-pointer font-semibold flex items-center justify-between">
          Kebijakan Privasi (Privacy Policy)
          <span class="text-xs text-gray-500 group-open:hidden">klik untuk baca</span>
          <span class="text-xs text-gray-500 hidden group-open:inline">klik untuk tutup</span>
        </summary>
        <div class="mt-3 space-y-3">
          <p>Kami memproses data pribadi sesuai peraturan yang berlaku (termasuk UU Perlindungan Data Pribadi). Bagian ini menjelaskan apa yang kami kumpulkan, untuk apa, dan hak-hak Anda.</p>

          <h4 class="font-semibold">1. Data yang Kami Kumpulkan</h4>
          <ul class="list-disc pl-5 space-y-1">
            <li><b>Data yang Anda berikan:</b> nama, tanggal lahir, alamat/domisi­li, pekerjaan, nomor telepon (format internasional disarankan), email.</li>
            <li><b>Data penggunaan:</b> log aktivitas, alamat IP, jenis perangkat/peramban, cookie/teknologi serupa, preferensi, waktu akses.</li>
            <li><b>Data dari pihak ketiga:</b> bila menggunakan layanan pembayaran, email, analitik, atau penyimpanan, kami menerima data seperlunya untuk menjalankan fitur.</li>
          </ul>

          <h4 class="font-semibold">2. Tujuan Pemrosesan</h4>
          <ul class="list-disc pl-5 space-y-1">
            <li>Menyediakan, memelihara, dan meningkatkan layanan serta pengalaman pengguna.</li>
            <li>Keamanan: pencegahan penyalahgunaan, pemecahan masalah teknis.</li>
            <li>Komunikasi: verifikasi akun, notifikasi transaksional, dukungan pelanggan.</li>
            <li>Kepatuhan hukum dan permintaan otoritas yang sah.</li>
          </ul>

          <h4 class="font-semibold">3. Dasar Hukum Pemrosesan</h4>
          <p>Persetujuan Anda, pelaksanaan kontrak (penyediaan layanan), kepentingan yang sah, dan/atau pemenuhan kewajiban hukum.</p>

          <h4 class="font-semibold">4. Berbagi Data</h4>
          <p>Kami <b>tidak memperjualbelikan</b> data Anda. Data dapat dibagikan kepada:</p>
          <ul class="list-disc pl-5 space-y-1">
            <li><b>Penyedia layanan (processors):</b> hosting, penyimpanan, email, analitik, pembayaran—dengan perjanjian kerahasiaan & keamanan.</li>
          </ul>

          <h4 class="font-semibold">5. Retensi Data</h4>
          <p>Data disimpan selama akun aktif dan/atau diperlukan untuk tujuan di atas, lalu dihapus atau dianonimkan sesuai kebijakan retensi internal.</p>

          <h4 class="font-semibold">6. Hak Anda</h4>
          <ul class="list-disc pl-5 space-y-1">
            <li>Akses & salinan data.</li>
            <li>Perbaikan/perbaruan data tidak akurat.</li>
            <li>Penghapusan data (sesuai syarat hukum).</li>
            <li>Pembatasan pemrosesan & penarikan persetujuan.</li>
            <li>Portabilitas data (jika berlaku).</li>
          </ul>
          <p>Gunakan hak-hak tersebut via <a href="mailto:{{ config('mail.from.address') }}" class="underline">{{ config('mail.from.address') }}</a>. Kami merespons dalam jangka waktu wajar.</p>

          <h4 class="font-semibold">7. Anak di Bawah Umur</h4>
          <p>Layanan tidak ditujukan untuk anak di bawah 18 tahun. Jika Anda orang tua/wali dan mengetahui anak Anda memberikan data, hubungi kami untuk penghapusan.</p>

          <h4 class="font-semibold">8. Perubahan Kebijakan</h4>
          <p>Kami dapat memperbarui kebijakan ini; perubahan material akan diberitahukan melalui aplikasi/email.</p>
        </div>
      </details>

      <!-- Keamanan Data -->
      <details class="group">
        <summary class="cursor-pointer font-semibold flex items-center justify-between">
          Keamanan Data
          <span class="text-xs text-gray-500 group-open:hidden">klik untuk baca</span>
          <span class="text-xs text-gray-500 hidden group-open:inline">klik untuk tutup</span>
        </summary>
        <div class="mt-3 space-y-3">
          <p>Kami menerapkan langkah administratif, teknis, dan fisik untuk melindungi data pribadi.</p>
          <ul class="list-disc pl-5 space-y-1">
            <li><b>Enkripsi:</b> semua lalu lintas via HTTPS/TLS.</li>
            <li><b>Kontrol Akses:</b> least-privilege, autentikasi internal berlapis, rotasi kredensial.</li>
            <li><b>Audit & Logging:</b> pencatatan akses/aktivitas penting, pemantauan anomali.</li>
            <li><b>Pencadangan & Pemulihan:</b> backup terjadwal, uji pemulihan berkala.</li>
            <li><b>Manajemen Insiden:</b> SOP respons insiden & pemberitahuan tepat waktu kepada pengguna/otoritas sesuai ketentuan.</li>
          </ul>
          <p>Tidak ada sistem yang 100% aman, namun kami terus meningkatkan kontrol keamanan untuk meminimalkan risiko.</p>
        </div>
      </details>

    </section>

    {{-- Form persetujuan (tetap) --}}
    <form action="{{ route('register.consent.post') }}" method="POST" class="mt-4 space-y-4">
        @csrf
        <label class="flex items-start gap-3">
            <input type="checkbox" name="agree_terms" value="1" class="mt-1" {{ old('agree_terms') ? 'checked' : '' }} required>
            <span>Saya menyetujui Ketentuan Layanan.</span>
        </label>
        @error('agree_terms')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

        <label class="flex items-start gap-3">
            <input type="checkbox" name="agree_privacy" value="1" class="mt-1" {{ old('agree_privacy') ? 'checked' : '' }} required>
            <span>Saya memahami & menyetujui Kebijakan Privasi dan Keamanan Data.</span>
        </label>
        @error('agree_privacy')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="flex items-center justify-between">
            <a href="{{ route('register.step3.show') }}" class="px-4 py-2 rounded-md border">Kembali</a>
            <button class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">Selesai &amp; Daftar</button>
        </div>
    </form>
</div>

{{-- Mini styling scrollbar (opsional, bisa dipindah ke file CSS) --}}
<style>
  .policy-scroll::-webkit-scrollbar { width: 8px; }
  .policy-scroll::-webkit-scrollbar-track { background: transparent; }
  .policy-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 9999px; }
  .policy-scroll:hover::-webkit-scrollbar-thumb { background: #d1d5db; }
</style>
@endsection
