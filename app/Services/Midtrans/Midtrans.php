<?php

namespace App\Services\Midtrans;

use Midtrans\Config;

/**
 * Kelas dasar untuk mengonfigurasi koneksi Midtrans.
 *
 * Kelas ini membaca nilai dari konfigurasi `config/midtrans.php` dan
 * menginisialisasi pengaturan statis yang diperlukan oleh library
 * midtrans-php【538212355415917†L305-L332】【538212355415917†L355-L385】.
 */
class Midtrans
{
    /**
     * Server key dari Midtrans.
     *
     * @var string
     */
    protected $serverKey;

    /**
     * Apakah aplikasi menggunakan environment production?
     *
     * @var bool
     */
    protected $isProduction;

    /**
     * Apakah sanitasi parameter diaktifkan?
     *
     * @var bool
     */
    protected $isSanitized;

    /**
     * Apakah 3DSecure diaktifkan untuk pembayaran kartu kredit?
     *
     * @var bool
     */
    protected $is3ds;

    /**
     * Inisialisasi konfigurasi Midtrans dengan membaca parameter dari file config.
     */
    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->isProduction = config('midtrans.is_production');
        $this->isSanitized = config('midtrans.is_sanitized');
        $this->is3ds = config('midtrans.is_3ds');

        $this->configureMidtrans();
    }

    /**
     * Setel konfigurasi global untuk library Midtrans.
     */
    protected function configureMidtrans(): void
    {
        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = $this->isSanitized;
        Config::$is3ds = $this->is3ds;
    }
}