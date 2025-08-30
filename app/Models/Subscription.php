<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk menyimpan pesanan langganan (subscription) pengguna.
 *
 * Masing-masing record mewakili transaksi yang akan dibayar melalui Midtrans.
 * Kolom `number` berfungsi sebagai kode pesanan unik, sedangkan
 * `total_price` menyimpan total harga yang harus dibayar.
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'number',
        'plan',
        'total_price',
        'payment_status',
        'snap_token',
        'subscription_expires_at',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
    ];

    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}