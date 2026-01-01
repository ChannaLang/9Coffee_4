@extends('layouts.admin')

@section('content')

{{-- CSS & Icons --}}
<link rel="stylesheet" href="{{ asset('assets/css/allproduct.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/category.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<meta name="csrf-token" content="{{ csrf_token() }}">

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


    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="products-tab" data-bs-toggle="tab"
                    data-bs-target="#products-section" type="button" role="tab">
                <i class="lucide-package icon-sm"></i> All Products
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="categories-tab" data-bs-toggle="tab"
                    data-bs-target="#categories-section" type="button" role="tab">
                <i class="lucide-folder icon-sm"></i> Categories
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="mainTabsContent">

        {{-- Products Section --}}
        <div class="tab-pane fade show active" id="products-section" role="tabpanel">

            {{-- Products Header Actions --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="mb-3">
                    <input type="text" id="productSearch" class="form-control" placeholder="🔍 Search products..." >
                </div>
                <a href="#" id="btnAddProduct" class="btn btn-create btn-lg">
                    <i class="lucide-plus-circle icon-md"></i>
                    Add Product
                </a>
            </div>

            {{-- Flash Messages --}}
            @if(Session::has('success') && Session::get('success') !== 'Subtype deleted successfully.')
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

            {{-- Hidden Table for JavaScript Compatibility --}}
            <table style="display: none;">
                <tbody id="product-table-body">
                    @php $counter = 1; @endphp
                    @foreach($products as $product)
                        <tr data-product-id="{{ $product->id }}">
                            <td>{{ $counter }}</td>
                            <td>{{ $product->name }}</td>
                            <td><img src="{{ asset('assets/images/'.$product->image) }}" alt="{{ $product->name }}"></td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->productType?->name ?? 'N/A' }}</td>
                            <td>{{ $product->subType?->name ?? 'N/A' }}</td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm btn-edit"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}"
                                    data-type-id="{{ $product->productType?->id ?? '' }}"
                                    data-subtype-id="{{ $product->subType?->id ?? '' }}">
                                    Edit
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}">
                                    Delete
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm btn-create-variant" data-id="{{ $product->id }}">
                                    Add Options
                                </button>
                            </td>
                        </tr>
                        @php $counter++; @endphp
                    @endforeach
                </tbody>
            </table>

            {{-- Products Grid --}}
            <div class="products-folder-grid" id="products-folder-grid">
                @foreach($products as $product)
                    <div class="product-folder-card" data-product-id="{{ $product->id }}" data-product-name="{{ strtolower($product->name) }}">
                        <div class="product-folder-image">
                            <img src="{{ asset('assets/images/'.$product->image) }}" alt="{{ $product->name }}" class="product-folder-img">
                        </div>
                        <div class="product-folder-content">
                            <div class="product-folder-header">
                                <h5 class="product-folder-name">
                                    <i class="lucide-coffee"></i>
                                    <span class="product-name-text">{{ $product->name }}</span>
                                </h5>
                                <span class="product-folder-price product-price-text">${{ number_format($product->price, 2) }}</span>
                            </div>
                            <div class="product-folder-meta">
                                <div class="meta-item">
                                    <i class="lucide-layers"></i>
                                    <span class="product-type-text">{{ $product->productType?->name ?? 'N/A' }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="lucide-folder"></i>
                                    <span class="product-subtype-text">{{ $product->subType?->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="product-folder-actions">
                                <button type="button"
                                        class="btn-product-action btn-edit-product btn-edit"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->price }}"
                                        data-type-id="{{ $product->productType?->id ?? '' }}"
                                        data-subtype-id="{{ $product->subType?->id ?? '' }}">
                                    <i class="lucide-edit"></i> Edit
                                </button>
                                <button type="button"
                                        class="btn-product-action btn-options-product btn-create-variant"
                                        data-id="{{ $product->id }}">
                                    <i class="lucide-settings"></i> Options
                                </button>
                                <button type="button"
                                        class="btn-product-action btn-delete-product btn-delete"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->price }}">
                                    <i class="lucide-trash-2"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Categories Section --}}
        <div class="tab-pane fade" id="categories-section" role="tabpanel">

            <div class="category-management-container">

                {{-- Category Header --}}
                <div class="page-header mb-4">
                    <div class="header-content">
                        <div class="header-title-section">
                            <span class="emoji header-emoji">📁</span>
                            <div>
                                <h3 class="page-title">Category Management</h3>
                                <p class="page-subtitle">Click on folders to view products</p>
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

                {{-- Category Tabs Navigation --}}
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

                {{-- Category Tab Content --}}
                <div class="tab-content category-content" id="typeTabsContent">
                    @foreach($types as $index => $type)
                        <div class="tab-pane fade @if($index==0) show active @endif"
                             id="type-{{ $type->id }}" role="tabpanel">

                            <div class="folders-grid">
                                @forelse($type->subTypes as $subtype)
                                    <div class="folder-card" data-subtype-id="{{ $subtype->id }}">
                                        <div class="folder-front" id="folder-front-{{ $subtype->id }}">
                                            <div class="folder-icon">
                                                <i class="lucide-folder"></i>
                                            </div>
                                            <div class="folder-info">
                                                <h4 class="folder-name">{{ $subtype->name }}</h4>
                                                <div class="folder-count">
                                                    <i class="lucide-box icon-sm"></i>
                                                    <span>{{ $subtype->products->count() }} products</span>
                                                </div>
                                            </div>
                                            <div class="folder-actions">
                                                <button type="button" class="btn-view-folder" data-subtype-id="{{ $subtype->id }}">
                                                    <i class="lucide-eye"></i> View
                                                </button>
                                                <form action="{{ route('admin.categories.deleteSubtype', $subtype->id) }}"
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete-folder" onclick="return confirm('Delete this category and all its products?')">
                                                        <i class="lucide-trash-2">Remove</i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Full Screen Folder View --}}
                                    <div class="folder-overlay" id="folder-products-{{ $subtype->id }}" style="display: none;">
                                        <div class="folder-overlay-content">
                                            <div class="folder-products-header">
                                                <h5>
                                                    <i class="lucide-package"></i>
                                                    Products in {{ $subtype->name }}
                                                </h5>
                                                <button type="button" class="btn-close-folder" data-subtype-id="{{ $subtype->id }}">
                                                    <i class="lucide-x"></i> Close
                                                </button>
                                            </div>

                                            <div class="products-gallery">
                                                @forelse($subtype->products as $product)
                                                    <div class="product-card-gallery">
                                                        <div class="product-image-wrapper">
                                                            <img src="{{ asset('assets/images/'.$product->image) }}"
                                                                 alt="{{ $product->name }}"
                                                                 class="product-gallery-img">
                                                        </div>
                                                        <div class="product-card-info">
                                                            <h6 class="product-card-name">{{ $product->name }}</h6>
                                                            <p class="product-card-price">${{ number_format($product->price, 2) }}</p>
                                                            <div class="product-card-meta">
                                                                <span class="badge bg-primary">{{ $product->productType?->name ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="empty-folder">
                                                        <i class="lucide-inbox" ></i>
                                                        <p>No products in this category yet</p>
                                                    </div>
                                                @endforelse
                                            </div>
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
        </div>

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
                        <div class="form-label mb-2">
                            <span class="emoji">☑️</span>
                            Select Types to Remove
                        </div>
                        <div class="checkbox-list">
                            @foreach($types as $type)
                                <label class="checkbox-item" for="type_checkbox_{{ $type->id }}">
                                    <input type="checkbox" id="type_checkbox_{{ $type->id }}" name="type_ids[]" value="{{ $type->id }}">
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

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/all-product.js') }}"></script>
<script src="{{ asset('assets/js/create-type.js') }}"></script>
<script src="{{ asset('assets/js/create-subtype.js') }}"></script>

@endsection
