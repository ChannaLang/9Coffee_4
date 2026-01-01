@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/staff-sell.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<div class="container-fluid staff-sell-container">
    <div class="row">

        {{-- ========== Left: Products ========== --}}
        <div class="col-md-8 mb-4">
            <div class="card shadow-lg border-0 staff-sell-section">

                {{-- Header --}}
                <div class="card-header text-center d-flex align-items-center justify-content-center gap-2">
                    <i class="lucide-coffee header-icon"></i>
                    <h4 class="mb-0">Point of Sale</h4>
                </div>

                {{-- Type Filters --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <strong class="filter-label">
                            <i class="lucide-filter filter-icon"></i> Type:
                        </strong>
                        <button class="btn filter-type-btn active" data-type="all">
                            <i class="lucide-grid-3x3 btn-icon"></i> All
                        </button>
                        @foreach(App\Models\Product\ProductType::all() as $type)
                            <button class="btn filter-type-btn" data-type="{{ strtolower($type->name) }}">
                                {{ $type->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Sub-Type Filters --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <strong class="filter-label">
                            <i class="lucide-layers filter-icon"></i> Menu:
                        </strong>
                        <button class="btn filter-sub-btn active" data-subtype="all">
                            <i class="lucide-circle-dot btn-icon"></i> All
                        </button>
                        @foreach(App\Models\Product\SubType::all() as $sub)
                            <button class="btn filter-sub-btn" data-subtype="{{ strtolower($sub->name) }}">
                                {{ $sub->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Products Grid --}}
                <div class="row g-3" id="products-container">
                    @foreach($products as $product)
                        <div class="col-lg-3 col-md-4 col-sm-6 product-wrapper"
                            data-type="{{ strtolower($product->type->name ?? 'food') }}"
                            data-subtype="{{ strtolower($product->subType->name ?? 'other') }}"
                            data-name="{{ strtolower($product->name) }}">

                            <div class="product-card"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-type="{{ strtolower($product->type->name ?? 'food') }}">

                                {{-- Product Image --}}
                                <div class="product-image-wrapper">
                                    <img src="{{ asset('assets/images/'.$product->image) }}"
                                         class="img-fluid product-image"
                                         alt="{{ $product->name }}">
                                </div>

                                {{-- Product Info --}}
                                <div class="text-center product-info">
                                    <div class="fw-bold mb-1 product-name">{{ $product->name }}</div>
                                    <div class="product-price">${{ $product->price }}</div>
                                </div>

                                {{-- Variant Buttons --}}
                                <div class="variant-group">
                                    @foreach($product->variants as $variant)
                                        <button class="btn variant-btn"
                                                data-variant-id="{{ $variant['id'] }}"
                                                data-variant-name="{{ $variant['name'] }}"
                                                data-variant-price="{{ $variant['price'] }}"
                                                data-available="{{ $variant['stock'] }}"
                                                title="{{ $variant['name'] }} - ${{ $variant['price'] }}">
                                            {{ $variant['name'] }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Sugar Selection (for drinks only) --}}
                                <div class="sugar-wrapper d-none">
                                    <select id="sugar_level_{{ $product->id }}"
                                            name="sugar_level[{{ $product->id }}]"
                                            class="sugar-select">
                                        <option value="0">🚫 No Sugar</option>
                                        <option value="25">🍯 Less Sweet</option>
                                        <option value="50" selected>☕ Normal Sweet</option>
                                        <option value="75">🧁 Sweet</option>
                                        <option value="100">🍰 Extra Sweet</option>
                                    </select>
                                </div>

                                {{-- Add to Cart Button --}}
                                <div class="mt-3">
                                    <button type="button" class="btn btn-add-to-cart">
                                        <i class="lucide-shopping-cart cart-icon"></i>
                                        Add to Cart
                                    </button>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- ========== Right: Cart & Checkout ========== --}}
        <div class="col-md-4">
            <div class="card shadow-lg border-0 cart-section">
                <h4 class="text-center mb-3 cart-title">
                    <i class="lucide-shopping-bag cart-bag-icon"></i>
                    Shopping Cart
                </h4>

                {{-- Wallet Balance --}}
                <div class="card mb-3 wallet-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="wallet-label">Wallet Balance</div>
                            <div id="wallet-balance" class="wallet-amount" data-balance="{{ $earning }}">
                                ${{ number_format($earning, 2) }}
                            </div>
                        </div>
                        <i class="bi bi-wallet2 wallet-icon"></i>
                    </div>
                </div>

                {{-- Cart Table --}}
                <div class="table-responsive cart-table-wrapper">
                    <table class="table align-middle mb-0" id="cart-table">
                        <thead class="text-center">
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Sugar</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>

                {{-- Payment Method --}}
                <div class="mt-3 payment-method-section">
                    <label for="payment_method" class="form-label payment-label">
                        <i class="lucide-credit-card payment-icon"></i>
                        Payment Method
                    </label>
                    <select name="payment_method" id="payment_method" class="form-select payment-select" required>
                        <option value="cash" selected>💵 Cash</option>
                        <option value="card">💳 Credit/Debit Card</option>
                        <option value="mobile">📱 Mobile Payment</option>
                    </select>
                </div>

                {{-- Checkout Button --}}
                <form id="checkout-form" action="{{ route('staff.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cart_data" id="cart_data">
                    <button type="button" id="checkout" class="btn w-100 checkout-btn">
                        <i class="lucide-receipt checkout-icon"></i>
                        Checkout & Print
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Receipt Preview Modal --}}
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content receipt-modal-content" id="receipt-content"></div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    const checkoutUrl = "{{ route('staff.checkout') }}";
</script>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
<script src="{{ asset('assets/js/staff-sell.js') }}"></script>
@endsection
