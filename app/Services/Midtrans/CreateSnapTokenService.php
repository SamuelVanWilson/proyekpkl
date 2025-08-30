<?php

namespace App\Services\Midtrans;

use Midtrans\Snap;

/**
 * Layanan untuk menghasilkan Snap token pembayaran.
 *
 * Berdasarkan contoh dari artikel integrasi Midtrans【538212355415917†L344-L440】,
 * kelas ini menerima sebuah objek order (atau data sederhana) dan membangun
 * parameter transaksi yang diperlukan untuk menghasilkan token Snap.
 */
class CreateSnapTokenService extends Midtrans
{
    /**
     * Data pemesanan atau langganan yang akan dibayar.
     */
    protected $order;

    /**
     * Buat instance layanan baru.
     *
     * @param object $order Objek dengan informasi transaksi
     */
    public function __construct($order)
    {
        parent::__construct();
        $this->order = $order;
    }

    /**
     * Bangun parameter transaksi dan kembalikan token Snap.
     *
     * @return string Snap token
     */
    public function getSnapToken(): string
    {
        // Minimal informasi yang diperlukan oleh Midtrans: order_id dan gross_amount
        $params = [
            'transaction_details' => [
                'order_id' => $this->order->number,
                'gross_amount' => $this->order->total_price,
            ],
            'customer_details' => [
                'first_name' => $this->order->customer_name ?? 'Pengguna',
                'email' => $this->order->customer_email ?? 'email@example.com',
                'phone' => $this->order->customer_phone ?? '081000000000',
            ],
        ];

        // Jika order menyediakan rincian item, gunakan sebagai item_details
        if (!empty($this->order->items)) {
            $params['item_details'] = $this->order->items;
        }

        // Panggil API Snap untuk mendapatkan token
        return Snap::getSnapToken($params);
    }
}