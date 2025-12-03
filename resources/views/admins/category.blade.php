@extends('layouts.admin')
@section('page-css')
<link rel="stylesheet" href="{{ asset('assets/css/category.css') }}">
@endsection

@section('content')

<div class="container py-4">

    <h2 class="mb-4">Category Management</h2>

    {{-- Top Buttons Row --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <button id="btnAddType" class="btn btn-primary">Add Product Type</button>
        <button id="btnAddSubtype" class="btn btn-warning">Add Subtype</button>

    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" id="typeTabs" role="tablist">
        @foreach($types as $index => $type)
            <li class="nav-item" role="presentation">
                <button class="nav-link @if($index==0) active @endif"
                        id="type-tab-{{ $type->id }}"
                        data-bs-toggle="tab"
                        data-bs-target="#type-{{ $type->id }}"
                        type="button" role="tab">
                    {{ $type->name }}
                </button>
            </li>
        @endforeach
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content mt-3" id="typeTabsContent">
        @foreach($types as $index => $type)
            <div class="tab-pane fade @if($index==0) show active @endif"
                 id="type-{{ $type->id }}" role="tabpanel">

                <div class="row">
                    @forelse($type->subTypes as $subtype)
                        <div class="col-md-4 mb-3">
                            <div class="card category-card"
                                data-product-count="{{ $subtype->products->count() }}">
                                <div class="card-header">{{ $subtype->name }}</div>
                                <div class="card-body">
                                    <ul class="list-group mb-2">
                                        @forelse($subtype->products as $product)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $product->name }}
                                                <span>${{ number_format($product->price, 2) }}</span>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">No products yet.</li>
                                        @endforelse
                                    </ul>

                                    {{-- Delete Subtype --}}
                                    <form action="{{ route('admin.categories.deleteSubtype', $subtype->id) }}"
                                        method="POST" class="mt-2 delete-subtype-form">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger w-100">Delete Subtype</button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    @empty
                        <p>No subtypes for this type yet.</p>
                    @endforelse
                </div>

            </div>
        @endforeach
    </div>
    <button id="btnRemoveType" class="btn btn-danger">Remove Product Type</button>

</div>

{{-- Add Product Type Modal --}}
<div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Product Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="add-type-form">
          <div class="mb-3">
            <label for="typeName" class="form-label">Type Name</label>
            <input type="text" class="form-control" id="typeName" name="name" placeholder="e.g., Drink" required autocomplete="off">
          </div>
          <button type="submit" class="btn btn-success w-100">Add Type</button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Remove Type Modal --}}
<div class="modal fade" id="removeTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Remove Product Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="remove-type-form" method="POST">
          @csrf
          @method('DELETE')
            <div class="mb-3">
                <fieldset class="mb-3">
                    <legend class="form-label">Select Product Types to Remove</legend>
                    <div id="typeCheckboxes" class="checkbox-group">
                        @foreach($types as $type)
                            <div class="form-check custom-checkbox">
                                <input class="form-check-input" type="checkbox" name="type_ids[]" value="{{ $type->id }}" id="typeCheck{{ $type->id }}">
                                <label class="form-check-label" for="typeCheck{{ $type->id }}">{{ $type->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

            </div>

          <button type="submit" class="btn btn-danger w-100">Remove Selected Types</button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Add Subtype Modal --}}
<div class="modal fade" id="addSubtypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Subtype</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="add-subtype-form">
          <div class="mb-3">
            <label for="productTypeSelect" class="form-label">Product Type</label>
            <select class="form-select" id="productTypeSelect" name="product_type_id" required autocomplete="off">
              @foreach($types as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="subtypeName" class="form-label">Subtype Name</label>
            <input type="text" class="form-control" id="subtypeName" name="name" placeholder="e.g., Ice" required autocomplete="off">
          </div>
          <button type="submit" class="btn btn-success w-100">Add Subtype</button>
        </form>
      </div>
    </div>
  </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- JS --}}
<script>
    window.productTypes = @json($types);
</script>
<script src="{{ asset('assets/js/create-type.js') }}"></script>
<script src="{{ asset('assets/js/create-subtype.js') }}"></script>

@endsection
