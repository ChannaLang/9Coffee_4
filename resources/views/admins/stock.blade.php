@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/raw-material.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<div class="container-fluid raw-material-container">

    {{-- Page Header with Stats --}}
    <div class="page-header-section">


        {{-- Quick Stats Cards --}}
        <div class="row stats-row mt-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card stat-total">
                    <div class="stat-icon-wrapper">
                        <span class="stat-emoji">📦</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Total Ingredients</div>
                        <div class="stat-value">{{ $rawMaterials->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card stat-low">
                    <div class="stat-icon-wrapper">
                        <span class="stat-emoji">⚠️</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Low Stock Items</div>
                        <div class="stat-value">{{ $rawMaterials->where('quantity', '<', 5)->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card stat-good">
                    <div class="stat-icon-wrapper">
                        <span class="stat-emoji">✅</span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">In Stock Items</div>
                        <div class="stat-value">{{ $rawMaterials->where('quantity', '>=', 5)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="search-section mb-4">
        <div class="search-wrapper-large">
            <i class="lucide-search search-icon-large"></i>
            <input type="text"
                   id="searchInput"
                   class="search-input-large"
                   placeholder="Search ingredients by name...">
        </div>
        <div>
            <button id="btnAddMaterial"
                    class="btn btn-add-material"
                    data-url="{{ route('raw-material.store') }}">
                <i class="lucide-plus-circle add-icon"></i>
                <span>Add New Ingredient</span>
            </button>
        </div>
    </div>

    {{-- Ingredients Grid --}}
    <div class="row g-4" id="ingredientsGrid">
        @forelse($rawMaterials as $material)
        <div class="col-lg-3 col-md-4 col-sm-6 ingredient-card-wrapper" data-name="{{ strtolower($material->name) }}">
            <div class="ingredient-card {{ $material->quantity < 5 ? 'card-low-stock' : '' }}">

                {{-- Card Header --}}
                <div class="ingredient-card-header">
                    <div class="ingredient-card-icon">
                        @if($material->image)
                            <img src="{{ asset('storage/' . $material->image) }}"
                                 alt="{{ $material->name }}"
                                 class="ingredient-image">
                        @else
                            <i class="lucide-package-2"></i>
                        @endif
                    </div>
                    <span class="ingredient-id">#{{ $material->id }}</span>
                </div>

                {{-- Card Body --}}
                <div class="ingredient-card-body">
                    <h5 class="ingredient-card-name" id="displayName{{ $material->id }}">
                        {{ $material->name }}
                    </h5>

                    <div class="ingredient-stats">
                        <div class="stat-item">
                            <div class="stat-item-label">
                                <i class="lucide-package"></i>
                                Quantity
                            </div>
                            <div class="stat-item-value" id="displayQty{{ $material->id }}">
                                <span class="qty-number {{ $material->quantity < 5 ? 'qty-low' : 'qty-good' }}">
                                    {{ number_format($material->quantity, 2) }}
                                </span>
                                <span class="unit-text" id="displayUnit{{ $material->id }}">{{ $material->unit }}</span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-item-label">
                                <i class="lucide-activity"></i>
                                Status
                            </div>
                            <div class="stat-item-value">
                                @if($material->quantity < 5)
                                    <span class="status-badge-small status-low">
                                        <i class="lucide-alert-circle"></i>
                                        Low Stock
                                    </span>
                                @else
                                    <span class="status-badge-small status-good">
                                        <i class="lucide-check-circle"></i>
                                        In Stock
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="ingredient-card-actions">
                    <button class="btn-card-action btn-card-add btnAddStock"
                            data-id="{{ $material->id }}"
                            data-name="{{ $material->name }}"
                            data-unit="{{ $material->unit }}"
                            title="Add Stock">
                        <i class="lucide-plus"></i>
                        <span class="btn-card-text">Add</span>
                    </button>

                    <button class="btn-card-action btn-card-reduce btnReduceStock"
                            data-id="{{ $material->id }}"
                            data-name="{{ $material->name }}"
                            data-unit="{{ $material->unit }}"
                            title="Reduce Stock">
                        <i class="lucide-minus"></i>
                        <span class="btn-card-text">Reduce</span>
                    </button>

                    <button class="btn-card-action btn-card-edit btnUpdateMaterial"
                            data-id="{{ $material->id }}"
                            data-name="{{ $material->name }}"
                            data-unit="{{ $material->unit }}"
                            title="Edit Material">
                        <i class="lucide-edit"></i>
                        <span class="btn-card-text">Edit</span>
                    </button>

                    <button class="btn-card-action btn-card-delete btnDeleteMaterial"
                            data-id="{{ $material->id }}"
                            data-name="{{ $material->name }}"
                            title="Delete Material">
                        <i class="lucide-trash-2"></i>
                        <span class="btn-card-text">Delete</span>
                    </button>
                </div>

            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="empty-state-card">
                <i class="lucide-package-x empty-icon-large"></i>
                <h3 class="empty-title-large">No Ingredients Found</h3>
                <p class="empty-text-large">Start by adding your first ingredient to track inventory</p>
                <button class="btn btn-add-material btnAddFromEmpty"
                        data-url="{{ route('raw-material.store') }}">
                    <i class="lucide-plus-circle"></i>
                    Add First Ingredient
                </button>
            </div>
        </div>
        @endforelse
    </div>


</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/raw-material.js') }}"></script>

@if(Session::has('success'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ Session::get('success') }}',
        background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
        color: '#f5e6d3',
        confirmButtonColor: '#d4a373',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
});
</script>
@endif

@endsection
