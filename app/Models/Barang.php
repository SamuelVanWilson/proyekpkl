<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'barangs';

    /**
     * The attributes that are mass assignable.
     * Kolom 'data' akan menyimpan semua detail rincian per karung.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'daily_report_id', // Foreign key ke laporan rekapitulasi
        'data',
    ];

    /**
     * The attributes that should be cast.
     * Ini akan secara otomatis mengubah kolom JSON 'data' menjadi array PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Mendefinisikan relasi: Setiap data rincian 'barang' dimiliki oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Mendefinisikan relasi: Setiap data rincian 'barang' adalah bagian dari satu DailyReport.
     */
    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}