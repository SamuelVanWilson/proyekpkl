<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_reports';

    /**
     * The attributes that are mass assignable.
     * Ini adalah semua kolom dari 'Formulir Rekapitulasi'.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tanggal',
        // Kolom bawaan untuk laporan rekapitulasi (advanced). Tetap dipertahankan
        'lokasi',
        'pemilik_sawah',
        'jumlah_karung',
        'total_bruto',
        'karung_kosong',
        'total_netto',
        'harga_per_kilo',
        'harga_bruto',
        'uang_muka',
        'total_uang',
        'custom_fields', // Kolom untuk data kustom seperti No. HP
        // Kolom baru untuk laporan dinamis (laporan biasa)
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'custom_fields' => 'array',
        'data' => 'array',
    ];

    /**
     * Mendefinisikan relasi: Setiap laporan rekapitulasi dimiliki oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendefinisikan relasi: Satu laporan rekapitulasi memiliki banyak data rincian (barang).
     */
    public function rincianBarang()
    {
        return $this->hasMany(Barang::class);
    }
}
