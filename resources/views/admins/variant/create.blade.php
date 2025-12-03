@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/variant.css') }}">

<h2>Create Variant for {{ $product->name }}</h2>

{{-- Flash Messages --}}
@if(session('success'))
    <p class="alert alert-success">{{ session('success') }}</p>
@endif
@if(session('delete'))
    <p class="alert alert-danger">{{ session('delete') }}</p>
@endif

{{-- Add Variant Button --}}
<button id="btnShowForm" class="btn btn-success mb-3">
    Add Variant
</button>

{{-- Hidden Form --}}
<div id="variantForm" style="display: none;">
    <form action="{{ route('admin.product.variants.store', $product->id) }}"
          method="POST"
          class="variant-form-card">
        @csrf

        <div class="mb-2">
            <input type="text" name="name" placeholder="Variant Name" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-2">
            <input type="number" name="price" placeholder="Price" class="form-control" step="0.01" required autocomplete="off">

        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>

            <button type="button" id="btnHideForm" class="btn btn-outline-secondary">
                Cancel
            </button>
        </div>
    </form>
</div>


{{-- Toggle Script --}}
<script>
document.getElementById('btnShowForm').addEventListener('click', function () {
   $('#variantForm').slideDown();
    this.style.display = 'none';
});

document.getElementById('btnHideForm').addEventListener('click', function () {
    $('#variantForm').slideUp();
    document.getElementById('btnShowForm').style.display = 'inline-block';
});
</script>


<h3 class="mt-4">Variants</h3>

<div class="row mt-2">
    @foreach($product->variants as $variant)
        <div class="col-md-4 mb-3">
            <div class="variant-card shadow-sm">

                <div class="variant-title">{{ $variant->name }}</div>
                <div class="variant-price">${{ number_format($variant->price, 2) }}</div>

                {{-- Show assigned ingredients --}}
                @if($variant->rawMaterials->count() > 0)
                    <div class="assigned-materials mt-2">
                        <strong>Ingredients:</strong>
                            <ul class="mb-0">
                            @foreach($variant->rawMaterials->take(5) as $material)
                                <li>{{ $material->name }}: {{ $material->pivot->quantity_required }} {{ $material->unit }}</li>
                            @endforeach
                            @if($variant->rawMaterials->count() > 5)
                                <li>+{{ $variant->rawMaterials->count() - 5 }} more...</li>
                            @endif
                            </ul>

                    </div>
                @else
                    <div class="assigned-materials mt-2 text-muted">
                        No ingredients assigned yet.
                    </div>
                @endif

                <div class="d-flex gap-2 mt-2">
                    <form action="{{ route('admin.product.variants.destroy', $variant->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100">🗑 Delete</button>
                    </form>

                    <a href="{{ route('admin.product.variants.assignMaterials', $variant->id) }}"
                    class="btn btn-primary btn-sm w-10">
                    📦 Recipe
                    </a>
                </div>

            </div>

        </div>
    @endforeach
</div>

@endsection
