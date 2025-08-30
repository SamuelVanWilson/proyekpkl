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

    public function edit(User $user)
    {
        if ($user->role === 'admin') abort(403);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
    $validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => ['required','email','max:255', Rule::unique('users')->ignore($user->id)],
    'password' => 'nullable|string|min:8|confirmed',


    'alamat' => 'nullable|string|max:255',
    'tanggal_lahir' => 'nullable|string|max:20',
    'pekerjaan' => 'nullable|string|max:255',
    'nomor_telepon' => 'required|string|max:20',


    'role' => ['required', Rule::in(['admin','user'])],
    'is_active' => 'required|boolean',


    'subscription_plan' => 'nullable|in:mingguan,bulanan,3_bulan',
    'subscription_expires_at' => 'nullable|date',
    'offer_expires_at' => 'nullable|date',
    ]);


    $user->name = $validated['name'];
    $user->email = $validated['email'];


    if (!empty($validated['password'])) {
    $user->password = Hash::make($validated['password']);
    }


    $user->alamat = $validated['alamat'] ?? null;
    $user->tanggal_lahir = $validated['tanggal_lahir'] ?? null;
    $user->pekerjaan = $validated['pekerjaan'] ?? null;
    $user->nomor_telepon = $validated['nomor_telepon'];


    $user->role = $validated['role'];
    $user->is_active = (bool) $validated['is_active'];


    $user->subscription_plan = $validated['subscription_plan'] ?? null;


    $user->subscription_expires_at = !empty($validated['subscription_expires_at'])
    ? Carbon::parse($validated['subscription_expires_at'])
    : null;


    $user->offer_expires_at = !empty($validated['offer_expires_at'])
    ? Carbon::parse($validated['offer_expires_at'])
    : null;


    $user->save();


    return redirect()->route('admin.users.index')
    ->with('success', 'Data pengguna berhasil diperbarui.');
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

    public function saveFormBuilder(Request $request, User $user) // Tambahkan User $user = null untuk Client
    {
        // Validasi sekarang memeriksa 'label' bukan 'name' untuk input pengguna
        $validated = $request->validate([
            'rincian' => 'sometimes|array',
            'rincian.*.label' => 'required_with:rincian|string', // Cek 'label'
            'rincian.*.type' => 'required_with:rincian|string',

            'rekap' => 'sometimes|array',
            'rekap.*.label' => 'required_with:rekap|string', // Cek 'label'
            'rekap.*.type' => 'required_with:rekap|string',
            'rekap.*.formula' => 'nullable|string',
            'rekap.*.default_value' => 'nullable|string',
            'rekap.*.readonly' => 'sometimes|boolean',
        ]);

        $processedColumns = [];

        // Proses Tabel Rincian
        if (!empty($validated['rincian'])) {
            foreach ($validated['rincian'] as $col) {
                $processedColumns['rincian'][] = [
                    // 'name' dibuat secara otomatis dari 'label'
                    'name' => \Illuminate\Support\Str::slug($col['label'], '_'),
                    'label' => $col['label'], // Simpan 'label' asli
                    'type' => $col['type'],
                ];
            }
        } else {
            $processedColumns['rincian'] = [];
        }

        // Proses Formulir Rekapitulasi
        if (!empty($validated['rekap'])) {
            foreach ($validated['rekap'] as $col) {
                $processedColumns['rekap'][] = [
                    'name' => \Illuminate\Support\Str::slug($col['label'], '_'),
                    'label' => $col['label'],
                    'type' => $col['type'],
                    'formula' => $col['formula'] ?? null,
                    'default_value' => $col['default_value'] ?? null,
                    'readonly' => !empty($col['readonly']),
                ];
            }
        } else {
            $processedColumns['rekap'] = [];
        }

        // Logika untuk menentukan user_id
        $userId = $user ? $user->id : Auth::id();

        TableConfiguration::updateOrCreate(
            ['user_id' => $userId, 'table_name' => 'daily_reports'],
            ['columns' => $processedColumns]
        );

        // Redirect yang sesuai untuk setiap peran
        if ($user) {
            return redirect()->route('admin.users.index')->with('success', 'Konfigurasi form untuk ' . $user->name . ' berhasil disimpan.');
        } else {
            return redirect()->route('client.laporan.harian')->with('success', 'Struktur laporan Anda berhasil diperbarui!');
        }
    }


    // Terapkan pada method showFormBuilder() di KEDUA controller
    public function showFormBuilder(User $user)
    {
        $userId = $user ? $user->id : Auth::id();

        $config = TableConfiguration::firstOrNew(
            ['user_id' => $userId, 'table_name' => 'daily_reports'],
            ['columns' => ['rincian' => [], 'rekap' => []]]
        );

        $columns = $config->columns;

        // --- LOGIKA BARU: Pastikan 'label' selalu ada ---
        // Loop untuk Rincian
        if (!empty($columns['rincian'])) {
            foreach ($columns['rincian'] as $key => $col) {
                if (empty($col['label'])) {
                    // Jika tidak ada label (data lama), buat dari nama
                    // Ubah underscore menjadi spasi dan kapitalisasi setiap kata
                    $columns['rincian'][$key]['label'] = ucwords(str_replace('_', ' ', $col['name']));
                }
            }
        }

        // Loop untuk Rekapitulasi
        if (!empty($columns['rekap'])) {
            foreach ($columns['rekap'] as $key => $col) {
                if (empty($col['label'])) {
                    $columns['rekap'][$key]['label'] = ucwords(str_replace('_', ' ', $col['name']));
                }
            }
        }

        if ($user) {
            return view('admin.users.form-builder', compact('user', 'config', 'columns'));
        } else {
            return view('client.laporan.form-builder', compact('columns'));
        }
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
