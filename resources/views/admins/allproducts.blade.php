@extends('layouts.admin')

@section('content')
@php
$types = App\Models\ProductType::all();
@endphp
<script>
    window.productTypes = @json($types);
</script>

<div class="container-fluid py-4">

<meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-cafe-title">☕ Product Management</h2>
        <a href="#" id="btnAddProduct" class="btn btn-create btn-lg">
    <i class="bi bi-plus-circle"></i> Add Product
    </a>
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
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center" style="color:#f5f5f5;">
    <thead style="background-color:#6b4c3b;">
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Image</th>
            <th>Price</th>
            <th>Type</th>
            <th>Edit</th>
            <th>Delete</th>
            <th>Ingredient</th>
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
            <td>
            <button type="button"
                class="btn btn-info btn-sm rounded-pill btn-edit"
                data-id="{{ $product->id }}"
                data-name="{{ $product->name }}"
                data-price="{{ $product->price }}"
                data-type-id="{{ $product->productType ? $product->productType->id : '' }}">
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
            <a href="{{ route('admin.product.assignPage', $product->id) }}"
            class="btn btn-primary btn-sm">
                Assign Recipe
            </a>


        </td>
        </tr>
        @php $counter++; @endphp
        @endforeach
    </tbody>
        </table>
            </div>

            {{-- Back to Dashboard Button --}}
            <a href="{{ route('admins.dashboard') }}" class="btn btn-back mt-4">
                <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
            </a>

        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('assets/css/allproduct.css') }}">
<script src="{{ asset('assets/js/all-product.js') }}"></script>

@endsection
