@extends('layouts.admin')

@section('content')

{{-- CSS & Icons --}}
<link rel="stylesheet" href="{{ asset('assets/css/allproduct.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

@php
$types = App\Models\Product\ProductType::with('subTypes')->get();

// Prepare product types array
$productTypesArr = $types->map(fn($t) => ['id' => $t->id, 'name' => $t->name]);

// Prepare subtypes array grouped by type_id
$subTypesArr = [];
foreach($types as $t) {
    $subTypesArr[$t->id] = $t->subTypes->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray();
}
@endphp

<script>
    window.productTypes = @json($productTypesArr);
    window.subTypes = @json($subTypesArr);
    window.variantCreateUrl = "{{ url('/admin/product/products') }}";
</script>

<div class="container-fluid py-4">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-cafe-title">
            <i class="lucide-package-search icon-lg icon-center"></i>
            Product Management
        </h2>
        <div>
            <a href="#" id="btnAddProduct" class="btn btn-create btn-lg">
                <i class="lucide-plus-circle icon-md"></i>
                Add Product
            </a>
        </div>
    </div>

    {{-- Products Card --}}
    <div class="card shadow-lg rounded-4 cafe-card">
        <div class="card-body">

            {{-- Flash Messages --}}
            @if(Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="lucide-check-circle icon-sm icon-center"></i>
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(Session::has('delete'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="lucide-alert-circle icon-sm icon-center"></i>
                    {{ Session::get('delete') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Search --}}
            <div class="mb-4 d-flex justify-content-end">
                <input type="text" id="productSearch" class="form-control w-25" placeholder="🔍 Search products...">
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="lucide-tag icon-sm icon-center"></i> Product Name</th>
                            <th><i class="lucide-image icon-sm icon-center"></i> Image</th>
                            <th><i class="lucide-dollar-sign icon-sm icon-center"></i> Price</th>
                            <th><i class="lucide-layers icon-sm icon-center"></i> Type</th>
                            <th><i class="lucide-folder icon-sm icon-center"></i> Subtype</th>
                            <th><i class="lucide-edit icon-sm icon-center"></i> Edit</th>
                            <th><i class="lucide-trash-2 icon-sm icon-center"></i> Delete</th>
                            <th><i class="lucide-settings icon-sm icon-center"></i> Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach($products as $product)
                            <tr>
                                <th scope="row">{{ $counter }}</th>
                                <td>
                                    <i class="lucide-coffee icon-sm icon-center opacity-70"></i>
                                    {{ $product->name }}
                                </td>
                                <td>
                                    <img src="{{ asset('assets/images/'.$product->image) }}" alt="{{ $product->name }}" class="product-img">
                                </td>
                                <td><strong>${{ number_format($product->price, 2) }}</strong></td>
                                <td>{{ $product->productType?->name ?? 'N/A' }}</td>
                                <td>{{ $product->subType?->name ?? 'N/A' }}</td>
                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-sm btn-edit"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-price="{{ $product->price }}"
                                            data-type-id="{{ $product->productType?->id ?? '' }}"
                                            data-subtype-id="{{ $product->subType?->id ?? '' }}">
                                        <i class="lucide-edit icon-sm"></i> Edit
                                    </button>
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-danger btn-sm btn-delete"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}">
                                        <i class="lucide-trash-2 icon-sm"></i> Delete
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm btn-create-variant" data-id="{{ $product->id }}">
                                        <i class="lucide-settings icon-sm"></i> Add Options
                                    </button>
                                </td>
                            </tr>
                            @php $counter++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/all-product.js') }}"></script>

@endsection
