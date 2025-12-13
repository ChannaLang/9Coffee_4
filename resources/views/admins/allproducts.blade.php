@extends('layouts.admin')

@section('content')

{{-- Add CSS and Icons --}}
<link rel="stylesheet" href="{{ asset('assets/css/allproduct.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

@php
$types = App\Models\Product\ProductType::with('subTypes')->get();

// Prepare product types array
$productTypesArr = $types->map(function($t) {
    return [
        'id' => $t->id,
        'name' => $t->name
    ];
});

// Prepare subtypes array grouped by type_id
$subTypesArr = [];
foreach($types as $t) {
    $subTypesArr[$t->id] = $t->subTypes->map(function($s) {
        return [
            'id' => $s->id,
            'name' => $s->name
        ];
    })->toArray();
}
@endphp

<script>
    window.productTypes = @json($productTypesArr);
    window.subTypes = @json($subTypesArr);
</script>

<div class="container-fluid py-4">

<meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-cafe-title">
            <i class="lucide-package-search" style="width: 32px; height: 32px; vertical-align: middle;"></i>
            Product Management
        </h2>
        <div>
            <a href="#" id="btnAddProduct" class="btn btn-create btn-lg">
                <i class="lucide-plus-circle" style="width: 20px; height: 20px;"></i>
                Add Product
            </a>
        </div>
    </div>

    {{-- Products Card --}}
    <div class="card shadow-lg rounded-4 cafe-card">
        <div class="card-body">

            {{-- Flash Messages --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="lucide-check-circle" style="width: 18px; height: 18px; vertical-align: middle;"></i>
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (Session::has('delete'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="lucide-alert-circle" style="width: 18px; height: 18px; vertical-align: middle;"></i>
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
                            <th>
                                <i class="lucide-tag" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Product Name
                            </th>
                            <th>
                                <i class="lucide-image" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Image
                            </th>
                            <th>
                                <i class="lucide-dollar-sign" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Price
                            </th>
                            <th>
                                <i class="lucide-layers" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Type
                            </th>
                            <th>
                                <i class="lucide-folder" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Subtype
                            </th>
                            <th>
                                <i class="lucide-edit" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Edit
                            </th>
                            <th>
                                <i class="lucide-trash-2" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Delete
                            </th>
                            <th>
                                <i class="lucide-settings" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Options
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach ($products as $product)
                            <tr>
                                <th scope="row">{{ $counter }}</th>
                                <td>
                                    <i class="lucide-coffee" style="width: 16px; height: 16px; vertical-align: middle; opacity: 0.7;"></i>
                                    {{ $product->name }}
                                </td>
                                <td>
                                    <img src="{{ asset('assets/images/'.$product->image) }}"
                                        alt="{{ $product->name }}"
                                        class="product-img">
                                </td>
                                <td><strong>${{ number_format($product->price, 2) }}</strong></td>
                                <td>{{ $product->productType ? $product->productType->name : 'N/A' }}</td>
                                <td>{{ $product->subType ? $product->subType->name : 'N/A' }}</td>

                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-sm rounded-pill btn-edit"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-price="{{ $product->price }}"
                                            data-type-id="{{ $product->productType ? $product->productType->id : '' }}"
                                            data-subtype-id="{{ $product->subType ? $product->subType->id : '' }}">
                                            <i class="lucide-edit" style="width: 14px; height: 14px;"></i>
                                            Edit
                                    </button>
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-danger btn-sm rounded-pill btn-delete"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}">
                                            <i class="lucide-trash-2" style="width: 14px; height: 14px;"></i>
                                            Delete
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm rounded-pill btn-create-variant"
                                            data-id="{{ $product->id }}">
                                            <i class="lucide-settings" style="width: 14px; height: 14px;"></i>
                                            Add Options
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
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/all-product.js') }}"></script>
<script>
    window.variantCreateUrl = "{{ url('/admin/product/products') }}";
</script>

@endsection
