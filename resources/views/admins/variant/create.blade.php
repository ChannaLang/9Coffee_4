@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/variant.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<h2>
    <i class="lucide-layers-3" style="width: 32px; height: 32px; vertical-align: middle;"></i>
    Create Variant for {{ $product->name }}
</h2>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success">
        <i class="lucide-check-circle" style="width: 18px; height: 18px; vertical-align: middle;"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('delete'))
    <div class="alert alert-danger">
        <i class="lucide-alert-circle" style="width: 18px; height: 18px; vertical-align: middle;"></i>
        {{ session('delete') }}
    </div>
@endif

{{-- Add Variant Button --}}
<button id="btnShowForm" class="btn btn-success mb-3">
    <i class="lucide-plus-circle" style="width: 20px; height: 20px;"></i>
    Add Variant
</button>

{{-- Hidden Form --}}
<div id="variantForm" style="display: none;">
    <form action="{{ route('admin.product.variants.store', $product->id) }}"
          method="POST"
          class="variant-form-card">
        @csrf

        <div class="mb-2">
            <input type="text" name="name" placeholder="Variant Name (e.g., Small, Medium, Large)" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-2">
            <input type="number" name="price" placeholder="Price ($)" class="form-control" step="0.01" required autocomplete="off">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="lucide-save" style="width: 18px; height: 18px;"></i>
                Save Variant
            </button>

            <button type="button" id="btnHideForm" class="btn btn-outline-secondary">
                <i class="lucide-x" style="width: 18px; height: 18px;"></i>
                Cancel
            </button>
        </div>
    </form>
</div>

{{-- Toggle Script --}}
<script>
document.getElementById('btnShowForm').addEventListener('click', function () {
    const form = document.getElementById('variantForm');
    form.style.display = 'block';
    // Trigger reflow for animation
    form.offsetHeight;
    form.classList.add('show');
    this.style.display = 'none';
});

document.getElementById('btnHideForm').addEventListener('click', function () {
    const form = document.getElementById('variantForm');
    const showBtn = document.getElementById('btnShowForm');

    form.classList.remove('show');
    setTimeout(() => {
        form.style.display = 'none';
        showBtn.style.display = 'inline-flex';
    }, 300);
});
</script>

<h3>
    <i class="lucide-package" style="width: 24px; height: 24px; vertical-align: middle;"></i>
    Available Variants
</h3>

<div class="row mt-2">
    @forelse($product->variants as $variant)
        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
            <div class="variant-card">

                <div class="variant-title">
                    <i class="lucide-tag" style="width: 20px; height: 20px;"></i>
                    {{ $variant->name }}
                </div>
                <div class="variant-price">${{ number_format($variant->price, 2) }}</div>

                {{-- Show assigned ingredients --}}
                @if($variant->rawMaterials->count() > 0)
                    <div class="assigned-materials">
                        <strong>
                            <i class="lucide-utensils" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                            Recipe Ingredients:
                        </strong>
                        <ul class="mb-0">
                            @foreach($variant->rawMaterials->take(5) as $material)
                                <li>{{ $material->name }}: {{ $material->pivot->quantity_required }} {{ $material->unit }}</li>
                            @endforeach
                            @if($variant->rawMaterials->count() > 5)
                                <li style="color: #d4a373; font-weight: 600;">+{{ $variant->rawMaterials->count() - 5 }} more ingredients...</li>
                            @endif
                        </ul>
                    </div>
                @else
                    <div class="assigned-materials text-muted">
                        <i class="lucide-alert-triangle" style="width: 18px; height: 18px; vertical-align: middle; display: inline-block; margin-bottom: 4px;"></i>
                        No ingredients assigned yet.
                    </div>
                @endif

                <div class="d-flex gap-2 mt-2">
                    <form action="{{ route('admin.product.variants.destroy', $variant->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100">
                            <i class="lucide-trash-2" style="width: 16px; height: 16px;"></i>
                            Delete
                        </button>
                    </form>

                    <a href="{{ route('admin.product.variants.assignMaterials', $variant->id) }}"
                       class="btn btn-primary btn-sm w-10">
                        <i class="lucide-chef-hat" style="width: 16px; height: 16px;"></i>
                        Recipe
                    </a>
                </div>

            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="variant-card text-center" style="padding: 48px;">
                <i class="lucide-package-x" style="width: 64px; height: 64px; color: rgba(245, 230, 211, 0.3); display: block; margin: 0 auto 16px;"></i>
                <p style="color: rgba(245, 230, 211, 0.5); font-size: 1.1rem; margin: 0;">
                    No variants created yet. Click "Add Variant" to get started!
                </p>
            </div>
        </div>
    @endforelse
</div>

{{-- Initialize Lucide Icons --}}
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

@endsection
