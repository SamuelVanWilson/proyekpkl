<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailyReportPolicy
{
    /**
     * Izinkan admin untuk melakukan aksi apa pun.
     * Ini adalah "super-access" yang akan dievaluasi pertama kali.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }
 
        return null; // Jika bukan admin, lanjutkan ke aturan lain.
    }

    /**
     * Menentukan apakah pengguna dapat melihat daftar laporan.
     * Semua pengguna yang login bisa melihat daftar laporannya sendiri.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat sebuah laporan spesifik.
     * Aturan: ID pengguna harus sama dengan user_id di laporan.
     */
    public function view(User $user, DailyReport $dailyReport): bool
    {
        return $user->id === $dailyReport->user_id;
    }

    /**
     * Menentukan apakah pengguna dapat membuat laporan baru.
     * Semua pengguna yang login bisa membuat laporan.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui sebuah laporan.
     * Aturan: ID pengguna harus sama dengan user_id di laporan.
     */
    public function update(User $user, DailyReport $dailyReport): bool
    {
        return $user->id === $dailyReport->user_id;
    }

    /**
     * Menentukan apakah pengguna dapat menghapus sebuah laporan.
     * Aturan: ID pengguna harus sama dengan user_id di laporan.
     */
    public function delete(User $user, DailyReport $dailyReport): bool
    {
        return $user->id === $dailyReport->user_id;
    }

    /**
     * Menentukan apakah pengguna dapat me-restore laporan yang sudah dihapus.
     * (Tidak dipakai di proyek ini, tapi best practice untuk diisi).
     */
    public function restore(User $user, DailyReport $dailyReport): bool
    {
        return false;
    }

    /**
     * Menentukan apakah pengguna dapat menghapus laporan secara permanen.
     * (Tidak dipakai di proyek ini).
     */
    public function forceDelete(User $user, DailyReport $dailyReport): bool
    {
        return false;
    }
}
