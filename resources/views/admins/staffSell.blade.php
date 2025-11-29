@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/staff-sell.css') }}">

<div class="container-fluid mt-4 px-4">
    <div class="row">

        {{-- ========== Left: Products ========== --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 staff-sell-section" style="background-color: #3e2f2f; color: #f5f5f5;">

                        {{-- Header --}}
                        <div class="d-flex justify-content-between mb-2">
                                <a href="{{ route('admins.dashboard') }}" class="btn btn-outline-light fw-bold">
                                        <i class="bi bi-arrow-left-circle"></i> Back
                                    </a>
                                    <a href="{{ route('admin.raw-material.stock')}}" class="btn btn-outline-light fw-bold">
                                <i class="bi bi-exclamation-triangle"></i> Ingredients Management
                            </a>

                        </div>

                        <div class="card-header text-center" style="background-color: #db770cff; color: #fff;">
                            <h4 class="mb-0">Staff Sell POS</h4>
                        </div>
                        {{-- Type Filters --}}
                        <div class="mb-2">
                            <strong class="text-warning me-2">Filter Type:</strong>
                            <button class="btn btn-outline-warning filter-type-btn active" data-type="all">All</button>
                            @foreach(App\Models\Product\ProductType::all() as $type)
                                <button class="btn btn-outline-warning filter-type-btn" data-type="{{ strtolower($type->name) }}">
                                    {{ $type->name }}
                                </button>
                            @endforeach
                        </div>
                        {{-- Sub-Type Filters --}}
                        <div class="mb-2">
                            <strong class="text-warning me-2">Filter Menu:</strong>
                            <button class="btn btn-outline-warning filter-sub-btn active" data-subtype="all">All</button>
                            @foreach(App\Models\Product\SubType::all() as $sub)
                                <button class="btn btn-outline-warning filter-sub-btn" data-subtype="{{ strtolower($sub->name) }}">
                                    {{ $sub->name }}
                                </button>
                            @endforeach
                        </div>


                        {{-- Products Grid --}}
                        <div class="row mt-3" id="products-container">
                            @foreach($products as $product)
                                <div class="col-md-3 text-center mb-3 product-wrapper"
                                    data-type="{{ strtolower($product->type->name ?? 'food') }}"
                                    data-subtype="{{ strtolower($product->subType->name ?? 'other') }}"
                                    data-name="{{ strtolower($product->name) }}">

                                    <div class="product-card p-3 rounded"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-type="{{ strtolower($product->type->name ?? 'food') }}"
                                        style="background:#4b3a2f; border:1px solid #6b4c3b;">
                                        <img src="{{ asset('assets/images/'.$product->image) }}" class="img-fluid rounded mb-2" style="height:120px; object-fit:cover;">
                                        <div class="fw-bold">{{ $product->name }}</div>
                                        <div class="fw-bold product-price">${{ $product->price }}</div>

                                        {{-- Variant group --}}
                                        <div class="variant-group mt-2">
                                            @foreach($product->variants as $variant)
                                                <button class="btn btn-outline-warning btn-sm variant-btn mb-1"
                                                        data-variant-id="{{ $variant['id'] }}"
                                                        data-variant-name="{{ $variant['name'] }}"
                                                        data-variant-price="{{ $variant['price'] }}"
                                                        data-available="{{ $variant['stock'] }}">
                                                    {{ $variant['name'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                        {{-- Sugar selection (single, inside main card) --}}
                                            <div class="mt-2 sugar-wrapper">
                                                <select class="sugar-select btn btn-outline-light btn-sm w-100">

                                                    <option value="0">No Sugar</option>
                                                    <option value="25">Less Sweet</option>
                                                    <option value="50" selected>Normal Sweet</option>
                                                    <option value="75">Sweet</option>
                                                    <option value="100">Extra Sweet</option>
                                                </select>
                                            </div>
                                        {{-- Add button --}}
                                        <div class="mt-3 d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-success btn-add-to-cart">
                                                <i class="bi bi-plus-circle"></i> Add
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
            <div class="card shadow-sm border-0 rounded-4 p-3" style="background:#3e2f2f; color:#f5f5f5;">
                <h4 class="text-center">🛒 Cart</h4>

                {{-- Wallet --}}
                <div class="card mb-3 p-3 rounded" style="background:#5a3d30; color:#fff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-0">
                            Wallet Balance:
                            <span id="wallet-balance" data-balance="{{ $earning }}">
                                ${{ $earning }}
                            </span>
                        </p>
                        <i class="bi bi-wallet2 fs-2 text-warning"></i>
                    </div>
                </div>

                {{-- Cart Table --}}
                <div class="table-responsive" style="max-height:60vh; overflow-y:auto; border:1px solid #6b4c3b;">
                    <table class="table table-hover align-middle text-white mb-0" id="cart-table">
                        <thead style="background-color: #5a3d30;" class="text-center sticky-top">
                            <tr>
                                <th>Product</th>
                                <th>Option</th>
                                <th>Sugar</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>

                {{-- Payment --}}
                <div class="mt-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="cash" selected>Cash</option>
                    </select>
                </div>

                {{-- Checkout --}}
                <form id="checkout-form" action="{{ route('staff.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cart_data" id="cart_data">
                    <button type="button" id="checkout" class="btn btn-warning w-100 py-2 fw-bold">
                        <i class="bi bi-cash-coin"></i> Checkout & Print
                    </button>
                    <!-- Receipt Preview Modal -->
                    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" id="receipt-content"
                            style="font-family: 'Khmer OS', sans-serif; background-color:#fff; color:#000;">
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    const checkoutUrl = "{{ route('staff.checkout') }}";
</script>

{{-- JS + SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/staff-sell.js') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
@endsection
