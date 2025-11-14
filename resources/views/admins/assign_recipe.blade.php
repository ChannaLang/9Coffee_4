@extends('layouts.admin')

@section('content')
<h5 class="mb-3">Assign recipe to Product: {{ $product->name }}</h5>
<link rel="stylesheet" href="{{ asset('assets/css/assign-recipe.css') }}">


@if (Session::has('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ Session::get('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <form action="{{ route('admin.product.addMaterials', $product->id) }}" method="POST" id="recipeForm">
            @csrf
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-black">
                    <strong>Assign Raw Ingredient and Set Quantity per Variant</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex gap-2">
                <select  id="materialSelect" class="form-select" style="width: auto;">
                    <option value="">-Choose ingredient-</option> {{-- Select2 placeholder --}}
                    @foreach($rawMaterials as $mat)
                        <option value="{{ $mat->id }}" data-name="{{ $mat->name }}" data-unit="{{ $mat->unit }}">
                            {{ $mat->name }}
                        </option>
                    @endforeach
                </select>

                        <button type="button" id="addIngredientBtn" class="btn btn-success">Add</button>
                    </div>

                    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-bordered align-middle text-center" id="quantityTable" data-sizes='@json($sizes)'>
                            <thead class="table-light">
                                <tr>
                                    <th>Ingredients</th>
                                    @foreach($sizes as $size)
                                        <th>Qauntity for size({{ $size }})</th>
                                    @endforeach
                                    <th>Unit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Existing assigned materials --}}
                                @foreach($assigned as $matId => $matSizes)
                                    @php $mat = $rawMaterials->firstWhere('id', $matId); @endphp
                                    <tr data-id="{{ $matId }}">
                                        <td>{{ $mat->name }}</td>
                                        @foreach($sizes as $size)
                                            <td>
                                                <input type="number" step="0.01" min="0"
                                                       name="materials[{{ $matId }}][{{ $size }}]"
                                                       value="{{ $matSizes[$size] ?? 0 }}"
                                                       class="form-control">
                                            </td>
                                        @endforeach
                                        <td>{{ $mat->unit }}</td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm btnRemove">X</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">💾 Save Recipe</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
            {{-- Back to Dashboard Button --}}
            <a href="{{ route('admins.dashboard') }}" class="btn btn-back mt-4">
                <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
            </a>

<script src="{{ asset('assets/js/recipe.js') }}"></script>


@endsection
