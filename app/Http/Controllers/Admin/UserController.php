<?php
// File: app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nama_pabrik' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $validated['kode_unik'] = 'KLIEN-' . strtoupper(Str::random(8));
        $validated['password'] = Hash::make(Str::random(16)); // Buat password acak
        $validated['role'] = 'user';

        User::create($validated);
        return redirect()->route('admin.users.index')->with('success', 'Klien baru berhasil ditambahkan.');
    }
    
    public function edit(User $user)
    {
        // Pastikan kita tidak mengedit admin lain atau diri sendiri lewat sini
        if ($user->role === 'admin') {
            abort(403);
        }
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nama_pabrik' => 'nullable|string|max:255',
            'kode_unik' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'is_active' => 'required|boolean',
        ]);
        
        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'Data klien berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            abort(403);
        }
        
        // Data laporan, dll akan terhapus otomatis karena onDelete('cascade')
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Klien berhasil dihapus.');
    }
    
    public function showActivity(User $user)
    {
        if ($user->role === 'admin') {
            abort(403);
        }
        
        $user->load(['dailyReports' => function ($query) {
            $query->orderBy('tanggal', 'desc')->take(10);
        }, 'pdfExports' => function ($query) {
            $query->orderBy('created_at', 'desc')->take(10);
        }]);

        return view('admin.users.activity', compact('user'));
    }
}
