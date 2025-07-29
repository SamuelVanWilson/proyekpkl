<?php
// File: app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableConfiguration; // Pastikan ini di-import
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        // PERBAIKAN: Menambahkan pagination
        $users = User::where('role', 'user')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        // PERBAIKAN: Menambahkan validasi untuk lokasi & pola kode unik yang baru
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nama_pabrik' => 'required|string|max:255',
            'lokasi_pabrik' => 'nullable|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'is_active' => 'required|boolean',
            'kode_unik_pola' => 'required|string|in:nama-usaha,usaha.tahun,usaha.nama,lokasi-nama',
        ]);

        // --- LOGIKA KODE UNIK KUSTOM BARU ---
        $originalKodeUnik = $this->generateKodeUnik($validated['kode_unik_pola'], $validated);

        $dataToCreate = $validated;
        $dataToCreate['kode_unik'] = Hash::make($originalKodeUnik); // Hash kode unik
        $dataToCreate['role'] = 'user';
        // PERBAIKAN: Menghilangkan 'password', BUG SQL FIXED!

        $user = User::create($dataToCreate);

        // Redirect ke halaman edit agar admin bisa langsung menyalin kode unik
        return redirect()->route('admin.users.edit', $user)
                         ->with('success', 'Klien baru berhasil dibuat!')
                         ->with('new_kode_unik', $originalKodeUnik);
    }

    public function edit(User $user)
    {
        if ($user->role === 'admin') abort(403);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'admin') abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nama_pabrik' => 'nullable|string|max:255',
            'lokasi_pabrik' => 'nullable|string|max:255',
            'nomor_telepon' => 'required|string|max:20',
            'is_active' => 'required|boolean',
            'kode_unik_baru' => 'nullable|string|min:4', // Input untuk kode unik baru
        ]);

        $flashMessage = 'Data klien berhasil diperbarui.';

        // PERBAIKAN: Jika admin mengisi kode unik baru, hash dan simpan
        if (!empty($validated['kode_unik_baru'])) {
            $user->kode_unik = Hash::make($validated['kode_unik_baru']);
            $user->save();
            // Siapkan pesan flash untuk ditampilkan
            $flashMessage .= ' Kode unik baru telah diatur ke: ' . $validated['kode_unik_baru'];
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', $flashMessage);
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') abort(403);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Klien berhasil dihapus.');
    }

    // METHOD BARU UNTUK MEMPERBAIKI ERROR `View not found`
    public function showActivity(User $user)
    {
        if ($user->role === 'admin') abort(403);

        $user->load(['dailyReports' => function ($query) {
            $query->latest('tanggal')->take(10);
        }, 'pdfExports' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.users.activity', compact('user'));
    }

    // METHOD BARU UNTUK MEMPERBAIKI ERROR `Method not found`
    public function showFormBuilder(User $user)
    {
        $config = TableConfiguration::firstOrNew(['user_id' => $user->id, 'table_name' => 'daily_reports']);
        return view('admin.users.form-builder', compact('user', 'config'));
    }

    // METHOD BARU UNTUK MEMPERBAIKI ERROR `Method not found`
    public function saveFormBuilder(Request $request, User $user)
    {
        $validated = $request->validate([
            'columns' => 'required|array',
            'columns.*.name' => 'required|string',
            'columns.*.label' => 'required|string',
            'columns.*.type' => 'required|string|in:text,number,date',
        ]);

        TableConfiguration::updateOrCreate(
            ['user_id' => $user->id, 'table_name' => 'daily_reports'],
            ['columns' => $validated['columns']]
        );

        return redirect()->route('admin.users.index')->with('success', 'Konfigurasi form untuk ' . $user->name . ' berhasil disimpan.');
    }

    /**
     * Helper function untuk generate kode unik sesuai pola.
     */
    private function generateKodeUnik(string $pola, array $data): string
    {
        $namaPengguna = Str::upper(Str::slug(implode(' ', array_slice(explode(' ', $data['name']), 0, 2))));
        $namaUsaha = Str::upper(Str::slug(implode(' ', array_slice(explode(' ', $data['nama_pabrik'] ?? 'BISNIS'), 0, 3))));
        $lokasi = Str::upper(Str::slug($data['lokasi_pabrik'] ?? 'LOKAL'));
        $tahun = Carbon::now()->format('Y');

        switch ($pola) {
            case 'nama-usaha':
                return "{$namaPengguna}-{$namaUsaha}";
            case 'usaha.tahun':
                return "{$namaUsaha}.{$tahun}";
            case 'usaha.nama':
                return "{$namaUsaha}.{$namaPengguna}";
            case 'lokasi-nama':
                return "{$lokasi}-{$namaPengguna}";
            default:
                return "{$namaPengguna}-" . Str::upper(Str::random(4));
        }
    }
}
