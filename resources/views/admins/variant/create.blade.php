@extends('layouts.admin')

@section('content')

<h2>Create Variant for {{ $product->name }}</h2>

{{-- Flash Messages --}}
@if(session('success'))
    <p class="alert alert-success">{{ session('success') }}</p>
@endif
@if(session('delete'))
    <p class="alert alert-danger">{{ session('delete') }}</p>
@endif

{{-- Create Variant Form --}}
<form action="{{ route('admin.product.variants.store', $product->id) }}" method="POST">
    @csrf
    <div class="mb-2">
        <input type="text" name="name" placeholder="Variant Name" class="form-control" required>
    </div>
    <div class="mb-2">
        <input type="number" name="price" placeholder="Price" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Create Variant</button>
</form>

<hr>

<h3>Existing Variants</h3>
<ul>
    @foreach($product->variants as $variant)
        <li>
            {{ $variant->name }} - ${{ $variant->price }}

            {{-- Delete Variant --}}
            <form action="{{ route('admin.product.variants.destroy', $variant->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>

            {{-- Assign Recipe --}}
            <a href="{{ route('admin.product.variants.assignMaterials', $variant->id) }}" class="btn btn-primary btn-sm">Assign Recipe</a>
        </li>
    @endforeach
</ul>

@endsection
