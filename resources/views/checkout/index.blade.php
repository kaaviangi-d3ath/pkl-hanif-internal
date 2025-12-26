{{-- resources/views/checkout/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <div class="row g-4">

            {{-- Form Alamat --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        Informasi Pengiriman
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Penerima</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="{{ auth()->user()->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea name="address" id="address" rows="3" class="form-control" required></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Pesanan --}}
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        Ringkasan Pesanan
                    </div>
                    <div class="card-body">
                        <div class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach($cart->items as $item)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between fw-bold mb-3 border-top pt-2">
                            <span>Total</span>
                            <span>Rp {{ number_format($cart->items->sum('subtotal'), 0, ',', '.') }}</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Buat Pesanan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
