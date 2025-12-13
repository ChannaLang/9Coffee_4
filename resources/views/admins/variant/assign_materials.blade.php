@extends('layouts.admin')

@section('content')

{{-- CSS and Scripts --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css">
<link rel="stylesheet" href="{{ asset('assets/css/assign-recipe.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<div class="container mt-4">

    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-title">
            <i class="lucide-chef-hat" style="width: 32px; height: 32px; vertical-align: middle;"></i>
            Recipe Builder
        </h1>
        <p class="page-subtitle">
            <i class="lucide-tag" style="width: 20px; height: 20px; vertical-align: middle;"></i>
            Creating recipe for: <strong style="color: #d4a373;">{{ $variant->name }}</strong>
        </p>
    </div>

    <div class="row">

        {{-- LEFT SIDE: Ingredient List --}}
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header">
                    <i class="lucide-package" style="width: 22px; height: 22px;"></i>
                    Available Ingredients
                </div>
                <div class="card-body">

                    {{-- TomSelect Dropdown --}}
                    <label for="ingredientDropdown" class="ingredient-section-label">
                        <i class="lucide-search" style="width: 18px; height: 18px; vertical-align: middle;"></i>
                        Search & Add
                    </label>
                    <select id="ingredientDropdown" placeholder="Type to search ingredients...">
                        <option value="">Select ingredient...</option>
                        @foreach($rawMaterials as $material)
                            @php
                                $assigned = $variant->rawMaterials->firstWhere('id', $material->id);
                            @endphp
                            @if(!$assigned)
                                <option value="{{ $material->id }}"
                                        data-name="{{ $material->name }}"
                                        data-qty="{{ $material->quantity }}"
                                        data-unit="{{ $material->unit }}">
                                    {{ $material->name }} ({{ $material->quantity }} {{ $material->unit }})
                                </option>
                            @endif
                        @endforeach
                    </select>

                    {{-- Clickable Ingredient List --}}
                    <div id="ingredientList" class="mt-3">
                        @php $hasUnassigned = false; @endphp
                        @foreach($rawMaterials as $material)
                            @php
                                $assigned = $variant->rawMaterials->firstWhere('id', $material->id);
                            @endphp
                            @if(!$assigned)
                                @php $hasUnassigned = true; @endphp
                                <div class="ingredient-item"
                                     data-id="{{ $material->id }}"
                                     data-name="{{ $material->name }}"
                                     data-qty="{{ $material->quantity }}"
                                     data-unit="{{ $material->unit }}">
                                    <strong>
                                        <i class="lucide-circle-dot" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                                        {{ $material->name }}
                                    </strong>
                                    <br>
                                    <small>
                                        <i class="lucide-archive" style="width: 12px; height: 12px; vertical-align: middle;"></i>
                                        Stock: {{ $material->quantity }} {{ $material->unit }}
                                    </small>
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasUnassigned)
                            <div class="ingredient-empty">
                                <i class="lucide-check-circle" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px;"></i>
                                All ingredients have been assigned!
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT SIDE: Selected Materials Table --}}
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-header">
                    <i class="lucide-clipboard-list" style="width: 22px; height: 22px;"></i>
                    Recipe Ingredients
                </div>
                <div class="card-body">

                    <form action="{{ route('admin.product.variants.storeMaterials', $variant->id) }}" method="POST">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="materialTable">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; padding-left: 20px;">
                                            <i class="lucide-package" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                            Material
                                        </th>
                                        <th>
                                            <i class="lucide-database" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                            Available
                                        </th>
                                        <th>
                                            <i class="lucide-beaker" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                            Qty Required
                                        </th>
                                        <th>
                                            <i class="lucide-settings" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($variant->rawMaterials as $material)
                                    <tr data-id="{{ $material->id }}">
                                        <td class="fw-semibold" style="text-align: left; padding-left: 20px;">
                                            <i class="lucide-circle-dot" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                                            {{ $material->name }}
                                        </td>
                                        <td>{{ $material->quantity }} {{ $material->unit }}</td>
                                        <td>
                                            <input type="number"
                                                   class="form-control"
                                                   step="0.01"
                                                   min="0"
                                                   name="materials[{{ $material->id }}]"
                                                   value="{{ $material->pivot->quantity_required ?? 0 }}"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-btn">
                                                <i class="lucide-x" style="width: 14px; height: 14px;"></i>
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 48px 20px; color: rgba(245, 230, 211, 0.5); font-style: italic;">
                                            <i class="lucide-chef-hat" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px; opacity: 0.3;"></i>
                                            No ingredients selected yet. Choose from the list on the left.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="lucide-save" style="width: 20px; height: 20px;"></i>
                                Save Recipe
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Initialize Lucide Icons --}}
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

{{-- JavaScript --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    // Reinitialize Lucide after DOM changes
    function refreshIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // --- TomSelect Dropdown ---
    let select = new TomSelect("#ingredientDropdown", {
        placeholder: "Type to search ingredients...",
        allowEmptyOption: true,
        maxOptions: 200,
        hideSelected: true,
        closeAfterSelect: true,
        sortField: { field: "text" },
        render: {
            option: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        }
    });

    // --- Add ingredient to table ---
    function addIngredientRow(id, name, qty, unit) {
        if (document.querySelector(`#materialTable tbody tr[data-id="${id}"]`)) return;

        // Remove empty state if exists
        const emptyRow = document.querySelector('#materialTable tbody tr td[colspan="4"]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }

        document.querySelector("#materialTable tbody").insertAdjacentHTML("beforeend", `
            <tr data-id="${id}">
                <td class="fw-semibold" style="text-align: left; padding-left: 20px;">
                    <i class="lucide-circle-dot" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                    ${name}
                </td>
                <td>${qty} ${unit}</td>
                <td>
                    <input type="number" class="form-control" step="0.01" min="0"
                           name="materials[${id}]" placeholder="0.00" value="0">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-btn">
                        <i class="lucide-x" style="width: 14px; height: 14px;"></i>
                        Remove
                    </button>
                </td>
            </tr>
        `);

        refreshIcons();
        attachRemoveHandlers();
    }

    // --- Dropdown selection ---
    select.on("change", function(value) {
        if (!value) return;

        let option = select.options[value];
        if (!option) return;

        let name = option.name || option.text.split(' (')[0];
        let qty = option.qty || '';
        let unit = option.unit || '';

        addIngredientRow(value, name, qty, unit);

        select.removeOption(value);
        select.clear();

        let leftItem = document.querySelector(`#ingredientList .ingredient-item[data-id="${value}"]`);
        if (leftItem) leftItem.remove();

        // Check if all assigned
        if (document.querySelectorAll('#ingredientList .ingredient-item').length === 0) {
            document.getElementById('ingredientList').innerHTML = `
                <div class="ingredient-empty">
                    <i class="lucide-check-circle" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px;"></i>
                    All ingredients have been assigned!
                </div>
            `;
            refreshIcons();
        }
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

        // Check if all assigned
        if (document.querySelectorAll('#ingredientList .ingredient-item').length === 0) {
            document.getElementById('ingredientList').innerHTML = `
                <div class="ingredient-empty">
                    <i class="lucide-check-circle" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px;"></i>
                    All ingredients have been assigned!
                </div>
            `;
            refreshIcons();
        }
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
                let name = row.children[0].innerText.trim();
                let qtyUnit = row.children[1].innerText.split(" ");
                let qty = qtyUnit[0] || "";
                let unit = qtyUnit[1] || "";

                select.addOption({
                    value: id,
                    text: `${name} (${qty} ${unit})`,
                    name: name,
                    qty: qty,
                    unit: unit
                });

                // Remove empty state if present
                const emptyDiv = document.querySelector('#ingredientList .ingredient-empty');
                if (emptyDiv) {
                    emptyDiv.remove();
                }

                document.getElementById("ingredientList").insertAdjacentHTML("beforeend", `
                    <div class="ingredient-item"
                         data-id="${id}"
                         data-name="${name}"
                         data-qty="${qty}"
                         data-unit="${unit}">
                        <strong>
                            <i class="lucide-circle-dot" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                            ${name}
                        </strong>
                        <br>
                        <small>
                            <i class="lucide-archive" style="width: 12px; height: 12px; vertical-align: middle;"></i>
                            Stock: ${qty} ${unit}
                        </small>
                    </div>
                `);

                document.querySelector(`#ingredientList .ingredient-item[data-id="${id}"]`)
                    .addEventListener("click", ingredientClickHandler);

                refreshIcons();
                row.remove();

                // Check if table is empty
                if (document.querySelectorAll('#materialTable tbody tr[data-id]').length === 0) {
                    document.querySelector('#materialTable tbody').innerHTML = `
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 48px 20px; color: rgba(245, 230, 211, 0.5); font-style: italic;">
                                <i class="lucide-chef-hat" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px; opacity: 0.3;"></i>
                                No ingredients selected yet. Choose from the list on the left.
                            </td>
                        </tr>
                    `;
                    refreshIcons();
                }
            };
        });
    }

    attachRemoveHandlers();
});
</script>

@endsection
