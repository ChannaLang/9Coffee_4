@extends('layouts.admin')

@section('content')

{{-- Tom Select CSS + JS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css">
<link rel="stylesheet" href="{{ asset('assets/css/assign-recipe.css') }}">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<div class="container mt-4">
    <div class="row">

        {{-- LEFT SIDE: Ingredient List --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-secondary text-white rounded-top-4">
                    <h5 class="mb-0">Ingredients</h5>
                </div>
                <div class="card-body">

                    <!-- TomSelect dropdown -->
                    <label for="ingredientDropdown" class="fw-bold mb-2">Add Ingredient</label>
                    <select id="ingredientDropdown" placeholder="Pick an ingredient...">

                        <option value="">Select or Search ingredient</option>
                        @foreach($variant->rawMaterials as $material)
                            @php $inputId = 'material_' . $material->id; @endphp
                            <tr data-id="{{ $material->id }}">
                                <td class="fw-semibold">{{ $material->name }}</td>
                                <td>{{ $material->quantity }} {{ $material->unit }}</td>

                            </tr>
                        @endforeach

                    </select>

                    <!-- Clickable ingredient list -->
                    <div id="ingredientList" class="mt-3">
                        @foreach($rawMaterials as $material)
                            @php
                                $assigned = $variant->rawMaterials->firstWhere('id', $material->id);
                            @endphp
                            @if(!$assigned)
                                <div class="ingredient-item"
                                     data-id="{{ $material->id }}"
                                     data-name="{{ $material->name }}"
                                     data-qty="{{ $material->quantity }}"
                                     data-unit="{{ $material->unit }}">
                                    <strong>{{ $material->name }}</strong><br>
                                    <small class="text-muted">Stock: {{ $material->quantity }} {{ $material->unit }}</small>
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT SIDE: Selected Materials Table --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">

                    <form action="{{ route('admin.product.variants.storeMaterials', $variant->id) }}" method="POST">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="materialTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Material</th>
                                        <th>Available Qty</th>
                                        <th style="width: 180px;">Qty Required</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variant->rawMaterials as $material)
                                    <tr data-id="{{ $material->id }}">
                                        <td class="fw-semibold">{{ $material->name }}</td>
                                        <td>{{ $material->quantity }} {{ $material->unit }}</td>
                                        <td>
                                            <input type="number"
                                                class="form-control"
                                                step="0.01"
                                                min="0"
                                                name="materials[{{ $material->id }}]"
                                                value="{{ $material->pivot->quantity_required ?? 0 }}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-btn">Remove</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                                Save Materials
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- JavaScript --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    // --- TomSelect Dropdown ---
    let select = new TomSelect("#ingredientDropdown", {
        placeholder: "Select or Search ingredient",
        allowEmptyOption: false,
        maxOptions: 200,
        hideSelected: true,
        closeAfterSelect: true,
        sortField: { field: "text" }
    });

    // --- Add ingredient to table ---
    function addIngredientRow(id, name, qty, unit) {
        if (document.querySelector(`#materialTable tbody tr[data-id="${id}"]`)) return;

        document.querySelector("#materialTable tbody").insertAdjacentHTML("beforeend", `
            <tr data-id="${id}">
                <td class="fw-semibold">${name}</td>
                <td>${qty} ${unit}</td>
                <td>
                    <input type="number" class="form-control" step="0.01" min="0"
                    name="materials[${id}]">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-btn">Remove</button>
                </td>
            </tr>
        `);

        attachRemoveHandlers();
    }

    // --- Dropdown selection ---
    select.on("change", function(value) {
        if (!value) return;

        let option = document.querySelector(`#ingredientDropdown option[value="${value}"]`);
        let name = option.dataset.name;
        let qty = option.dataset.qty;
        let unit = option.dataset.unit;

        addIngredientRow(value, name, qty, unit);

        select.removeOption(value);
        select.clear();

        let leftItem = document.querySelector(`#ingredientList .ingredient-item[data-id="${value}"]`);
        if (leftItem) leftItem.remove();
    });

    // --- Clickable left panel selection ---
    function ingredientClickHandler() {
        let id = this.dataset.id;
        let name = this.dataset.name;
        let qty = this.dataset.qty;
        let unit = this.dataset.unit;

        addIngredientRow(id, name, qty, unit);

        this.remove();
        select.removeOption(id);
    }

    document.querySelectorAll(".ingredient-item").forEach(item => {
        item.addEventListener("click", ingredientClickHandler);
    });

    // --- Remove button in table ---
    function attachRemoveHandlers() {
        document.querySelectorAll(".remove-btn").forEach(btn => {
            btn.onclick = function () {
                let row = this.closest("tr");
                let id = row.dataset.id;
                let name = row.children[0].innerText;
                let qtyUnit = row.children[1].innerText.split(" ");
                let qty = qtyUnit[0] || "";
                let unit = qtyUnit[1] || "";

                select.addOption({ value: id, text: name, data: { name: name, qty: qty, unit: unit } });

                document.getElementById("ingredientList").insertAdjacentHTML("beforeend", `
                    <div class="ingredient-item"
                         data-id="${id}"
                         data-name="${name}"
                         data-qty="${qty}"
                         data-unit="${unit}">
                         <strong>${name}</strong><br>
                         <small class="text-muted">Stock: ${qty} ${unit}</small>
                    </div>
                `);

                document.querySelector(`#ingredientList .ingredient-item[data-id="${id}"]`)
                    .addEventListener("click", ingredientClickHandler);

                row.remove();
            };
        });
    }

    attachRemoveHandlers();

});
</script>

@endsection
