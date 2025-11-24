@extends('layouts.admin')
@section('page-css')
<link rel="stylesheet" href="{{ asset('assets/css/category.css') }}">
@endsection

@section('content')

<div class="container py-4">


    <h2 class="mb-4">Category Management</h2>

            <!-- Tabs -->
        <div class="mt-4">

                <form action="{{ route('admin.categories.storeType') }}" method="POST" class="d-flex add-subtype-form">
                    @csrf
                    <input type="text" name="name" placeholder="Product Type Name" required>
                    <button type="submit">Add Type</button>
                </form>


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
            <!-- Add new Product Type Form -->

        </div>
            <!-- Tab Content -->
        <div class="tab-content mt-3" id="typeTabsContent">
            @foreach($types as $index => $type)

                <div class="tab-pane fade @if($index==0) show active @endif"
                    id="type-{{ $type->id }}" role="tabpanel">
                    {{-- Add new Subtype --}}
                    <form action="{{ route('admin.categories.storeSubtype') }}" method="POST" class="d-flex add-subtype-form mt-3">
                        @csrf
                        <input type="hidden" name="product_type_id" value="{{ $type->id }}">
                        <input type="text" name="name" placeholder="New Subtype" required>
                        <button type="submit">Add Subtype</button>
                    </form>
                    <div class="row">
                        {{-- Only loop through subtypes that belong to this type --}}
                        @forelse($type->subTypes as $subtype)
                            <div class="col-md-4 mb-3">
                                <div class="card category-card">
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
                                            method="POST" class="mt-2" onsubmit="return confirm('Delete this subtype?');">
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

</div>

@endsection
