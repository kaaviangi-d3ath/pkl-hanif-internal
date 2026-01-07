<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Membuat Order baru dari Keranjang belanja.
     *
     * ALUR:
     * 1. Validasi stok & hitung total
     * 2. Buat order (header)
     * 3. Pindahkan cart_items → order_items
     * 4. Kurangi stok produk
     * 5. Generate snap_token Midtrans
     * 6. Hapus cart items
     */
    public function createOrder(User $user, array $shippingData): Order
    {
        // Ambil cart + relasi product (WAJIB)
        $cart = $user->cart()->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw new \Exception('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($user, $cart, $shippingData) {

            /* =====================================================
             * A. VALIDASI STOK & HITUNG TOTAL
             * ===================================================== */
            $totalAmount = 0;

            foreach ($cart->items as $item) {
                if (! $item->product) {
                    throw new \Exception('Produk tidak ditemukan.');
                }

                if ($item->quantity > $item->product->stock) {
                    throw new \Exception(
                        "Stok produk {$item->product->name} tidak mencukupi."
                    );
                }

                // 🔑 HARGA FINAL (WAJIB FALLBACK)
                $price = $item->product->discount_price
                    ?? $item->product->price;

                if ($price === null) {
                    throw new \Exception(
                        "Harga produk {$item->product->name} tidak valid."
                    );
                }

                $totalAmount += $price * $item->quantity;
            }

            /* =====================================================
             * B. BUAT HEADER ORDER
             * ===================================================== */
            $order = Order::create([
                'user_id'          => $user->id,
                'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
                'status'           => 'pending',
                'payment_status'   => 'unpaid',
                'shipping_name'    => $shippingData['name'],
                'shipping_phone'   => $shippingData['phone'],
                'shipping_address' => $shippingData['address'],
                'total_amount'     => $totalAmount,
            ]);

            /* =====================================================
             * C. PINDAHKAN CART ITEMS → ORDER ITEMS
             * ===================================================== */
            foreach ($cart->items as $item) {

                $price = $item->product->discount_price
                    ?? $item->product->price;

                $order->items()->create([
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'price'        => $price,                     // ✅ TIDAK NULL
                    'quantity'     => $item->quantity,
                    'subtotal'     => $price * $item->quantity,   // ✅ BENAR
                ]);

                // Kurangi stok
                $item->product->decrement('stock', $item->quantity);
            }

            /* =====================================================
             * D. GENERATE SNAP TOKEN MIDTRANS
             * ===================================================== */
            try {
                $order->load('user');

                $midtransService = new \App\Services\MidtransService();
                $snapToken = $midtransService->createSnapToken($order);

                $order->update([
                    'snap_token' => $snapToken,
                ]);
            } catch (\Exception $e) {
                // Jika gagal, snap_token tetap null
                // Tombol bayar bisa disembunyikan
            }

            /* =====================================================
             * E. BERSIHKAN KERANJANG
             * ===================================================== */
            $cart->items()->delete();

            return $order;
        });
    }
}
