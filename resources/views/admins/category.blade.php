@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/category.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<div class="category-management-container">

    {{-- Header Section --}}
    <div class="page-header">
        <div class="header-content">
            <div class="header-title-section">
                <span class="emoji header-emoji">📁</span>
                <div>
                    <h1 class="page-title">Category Management</h1>
                    <p class="page-subtitle">Organize your products by types and subtypes</p>
                </div>
            </div>
            <div class="header-actions">
                <button id="btnAddType" class="btn-action btn-primary">
                    <span class="emoji">➕</span>
                    Add Type
                </button>
                <button id="btnAddSubtype" class="btn-action btn-warning">
                    <span class="emoji">📂</span>
                    Add Subtype
                </button>
                <button id="btnRemoveType" class="btn-action btn-danger">
                    <span class="emoji">🗑️</span>
                    Remove Type
                </button>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="tabs-container">
        <ul class="nav nav-tabs category-tabs" id="typeTabs" role="tablist">
            @foreach($types as $index => $type)
                <li class="nav-item" role="presentation">
                    <button class="nav-link category-tab @if($index==0) active @endif"
                            id="type-tab-{{ $type->id }}"
                            data-bs-toggle="tab"
                            data-bs-target="#type-{{ $type->id }}"
                            type="button" role="tab">
                        <span class="tab-icon">🏷️</span>
                        <span class="tab-text">{{ $type->name }}</span>
                        <span class="tab-count">{{ $type->subTypes->count() }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Tab Content --}}
    <div class="tab-content category-content" id="typeTabsContent">
        @foreach($types as $index => $type)
            <div class="tab-pane fade @if($index==0) show active @endif"
                 id="type-{{ $type->id }}" role="tabpanel">

                <div class="subtypes-grid">
                    @forelse($type->subTypes as $subtype)
                        <div class="subtype-card">
                            <div class="subtype-header">
                                <div class="subtype-title">
                                    <span class="emoji">🏷️</span>
                                    <h3>{{ $subtype->name }}</h3>
                                </div>
                                <div class="product-count-badge">
                                    <span class="emoji">📦</span>
                                    {{ $subtype->products->count() }}
                                </div>
                            </div>

                            <div class="subtype-body">
                                <div class="products-list">
                                    @forelse($subtype->products as $product)
                                        <div class="product-item">
                                            <div class="product-info">
                                                <span class="product-icon">☕</span>
                                                <span class="product-name">{{ $product->name }}</span>
                                            </div>
                                            <span class="product-price">${{ number_format($product->price, 2) }}</span>
                                        </div>
                                    @empty
                                        <div class="empty-products">
                                            <span class="emoji empty-emoji">📭</span>
                                            <p>No products yet</p>
                                            <span class="empty-hint">Add products to this category</span>
                                        </div>
                                    @endforelse
                                </div>

                                <form action="{{ route('admin.categories.deleteSubtype', $subtype->id) }}"
                                    method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-subtype">
                                        <span class="emoji">🗑️</span>
                                        Delete Category
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <span class="emoji large-emoji">📂</span>
                            <h3>No Subtypes Found</h3>
                            <p>Create your first subtype to organize products under {{ $type->name }}</p>
                            <button class="btn-action btn-primary" onclick="document.getElementById('btnAddSubtype').click()">
                                <span class="emoji">➕</span>
                                Add First Subtype
                            </button>
                        </div>
                    @endforelse
                </div>

            </div>
        @endforeach
    </div>

</div>

{{-- Add Product Type Modal --}}
<div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content category-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="emoji">➕</span>
                    Add New Product Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-type-form">
                    <div class="form-group">
                        <label for="typeName" class="form-label">
                            <span class="emoji">🏷️</span>
                            Type Name
                        </label>
                        <input type="text" class="form-control" id="typeName" name="name"
                               placeholder="e.g., Drink, Food, Dessert" required autocomplete="off">
                        <small class="form-hint">Choose a descriptive name for this category type</small>
                    </div>
                    <button type="submit" class="btn-submit">
                        <span class="emoji">✅</span>
                        Create Type
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Remove Type Modal --}}
<div class="modal fade" id="removeTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content category-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="emoji">🗑️</span>
                    Remove Product Types
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="remove-type-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="form-group">
                        <label class="form-label">
                            <span class="emoji">☑️</span>
                            Select Types to Remove
                        </label>
                        <div class="checkbox-list">
                            @foreach($types as $type)
                                <label class="checkbox-item">
                                    <input type="checkbox" name="type_ids[]" value="{{ $type->id }}">
                                    <span class="checkbox-label">
                                        <span class="emoji">🏷️</span>
                                        {{ $type->name }}
                                        <span class="type-subcount">({{ $type->subTypes->count() }} subtypes)</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <small class="form-warning">⚠️ Warning: This will delete all associated subtypes and products</small>
                    </div>
                    <button type="submit" class="btn-submit btn-danger-submit">
                        <span class="emoji">🗑️</span>
                        Remove Selected Types
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add Subtype Modal --}}
<div class="modal fade" id="addSubtypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content category-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="emoji">📂</span>
                    Add New Subtype
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-subtype-form">
                    <div class="form-group">
                        <label for="productTypeSelect" class="form-label">
                            <span class="emoji">📁</span>
                            Product Type
                        </label>
                        <select class="form-control form-select" id="productTypeSelect" name="product_type_id" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subtypeName" class="form-label">
                            <span class="emoji">🏷️</span>
                            Subtype Name
                        </label>
                        <input type="text" class="form-control" id="subtypeName" name="name"
                               placeholder="e.g., Ice, Hot, Blended" required autocomplete="off">
                        <small class="form-hint">Create a subcategory within the selected type</small>
                    </div>
                    <button type="submit" class="btn-submit">
                        <span class="emoji">✅</span>
                        Create Subtype
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Scripts --}}
<script>
    window.productTypes = @json($types);
</script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
<script src="{{ asset('assets/js/create-type.js') }}"></script>
<script src="{{ asset('assets/js/create-subtype.js') }}"></script>

@endsection
