@extends('layouts.admin')

@section('content')
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
        <h2 class="text-cafe-title">☕ Product Management</h2>
        <div>
            <a href="#" id="btnAddProduct" class="btn btn-create btn-lg me-2">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </div>
    </div>



    {{-- Products Card --}}
    <div class="card shadow-sm rounded-4 cafe-card">
        <div class="card-body">

            {{-- Flash Messages --}}
            @if (Session::has('success'))
                <p class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </p>
            @endif
            @if (Session::has('delete'))
                <p class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ Session::get('delete') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </p>
            @endif

            {{-- Table --}}
            <div class="mb-3 d-flex justify-content-end">
                <input type="text" id="productSearch" class="form-control w-25" placeholder="Search product...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle text-center" style="color:#f5f5f5;">
                        <thead style="background-color:#6b4c3b;">
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Subtype</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                <th>Option</th>
                            </tr>
                        </thead>
                        <tbody>
                                    @php $counter = 1; @endphp
                                    @foreach ($products as $product)
                                        <tr style="border-bottom:1px solid #5a3d30;">
                                            <th scope="row">{{ $counter }}</th>
                                            <td>{{ $product->name }}</td>
                                            <td>
                                                <img src="{{ asset('assets/images/'.$product->image) }}"
                                                    alt="{{ $product->name }}"
                                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border:1px solid #6b4c3b;">
                                            </td>
                                            <td>${{ number_format($product->price, 2) }}</td>
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

                                                        Edit
                                                </button>

                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm rounded-pill btn-delete"
                                                        data-id="{{ $product->id }}"
                                                        data-name="{{ $product->name }}">
                                                        Delete
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-success btn-sm rounded-pill btn-create-variant"
                                                        data-id="{{ $product->id }}">
                                                        Add Option and Recipe
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('assets/css/allproduct.css') }}">

<script src="{{ asset('assets/js/all-product.js') }}"></script>
<script>
    window.variantCreateUrl = "{{ url('/admin/product/products') }}";
</script>


@endsection
