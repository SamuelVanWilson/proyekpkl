<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Subscription;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    'name','email','password',
    'alamat','tanggal_lahir','pekerjaan','nomor_telepon',
    'role','is_active',
    'subscription_plan','subscription_expires_at','offer_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'subscription_expires_at' => 'datetime',
        ];
    }

    /**
     * Mendefinisikan relasi: Satu User memiliki banyak Barang (data rincian).
     */
    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }

    /**
     * Mendefinisikan relasi: Satu User memiliki banyak Laporan Harian (rekapitulasi).
     */
    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    /**
     * Mendefinisikan relasi: Satu User memiliki banyak riwayat ekspor PDF.
     */
    public function pdfExports()
    {
        return $this->hasMany(PdfExport::class);
    }

    /**
     * Mendefinisikan relasi: Satu User memiliki banyak konfigurasi tabel.
     */
    public function tableConfigurations()
    {
        return $this->hasMany(TableConfiguration::class);
    }

    /**
     * Relasi: satu user memiliki banyak pesanan langganan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        // Pengguna dianggap aktif jika tanggal kadaluarsa langganan ada di masa depan
        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }
}
