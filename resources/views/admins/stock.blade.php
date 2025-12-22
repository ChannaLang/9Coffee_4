@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/raw-material.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<div class="container-fluid mt-4">

    {{-- Add Material Button --}}
    <div class="d-flex justify-content-end mb-3">
        <button id="btnAddMaterial"
                class="btn btn-add-material"
                data-url="{{ route('raw-material.store') }}">
            <i class="lucide-plus-circle" style="width: 20px; height: 20px;"></i>
            Add New Ingredient
        </button>
    </div>

    <div class="card shadow-lg border-0 w-100 raw-card">
        <div class="card-header raw-header">
            <i class="lucide-package" style="width: 24px; height: 24px;"></i>
            <h4 class="mb-0">Raw Ingredient Inventory</h4>
        </div>

        <div class="card-body">

            {{-- Stock Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align: left; padding-left: 20px;">
                                <i class="lucide-tag" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Ingredient
                            </th>
                            <th>
                                <i class="lucide-package" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Quantity
                            </th>
                            <th>
                                <i class="lucide-ruler" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Unit
                            </th>
                            <th>
                                <i class="lucide-activity" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Status
                            </th>
                            <th>
                                <i class="lucide-settings" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rawMaterials as $index => $material)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td id="displayName{{ $material->id }}" style="text-align: left; padding-left: 20px;">
                                <i class="lucide-circle-dot" style="width: 14px; height: 14px; vertical-align: middle; opacity: 0.7;"></i>
                                <strong>{{ $material->name }}</strong>
                            </td>
                            <td id="displayQty{{ $material->id }}">
                                <strong>{{ number_format($material->quantity, 2) }}</strong>
                            </td>
                            <td id="displayUnit{{ $material->id }}">{{ $material->unit }}</td>
                            <td>
                                <span class="badge {{ $material->quantity < 5 ? 'bg-danger' : 'bg-success' }}">
                                    {{ $material->quantity < 5 ? 'Low Stock' : 'In Stock' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                    <button class="btn btn-success btn-action btnAddStock"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            data-unit="{{ $material->unit }}"
                                            title="Add Stock">
                                        <i class="lucide-plus" style="width: 16px; height: 16px;"></i>
                                        <span class="btn-text">Add</span>
                                    </button>

                                    <button class="btn btn-warning btn-action btnReduceStock"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            data-unit="{{ $material->unit }}"
                                            title="Reduce Stock">
                                        <i class="lucide-minus" style="width: 16px; height: 16px;"></i>
                                        <span class="btn-text">Reduce</span>
                                    </button>

                                    <button class="btn btn-primary btn-action btnUpdateMaterial"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            data-unit="{{ $material->unit }}"
                                            title="Update Material">
                                        <i class="lucide-edit" style="width: 16px; height: 16px;"></i>
                                        <span class="btn-text">Edit</span>
                                    </button>

                                    <button class="btn btn-danger btn-action btnDeleteMaterial"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            title="Delete Material">
                                        <i class="lucide-trash-2" style="width: 16px; height: 16px;"></i>
                                        <span class="btn-text">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px 20px; color: rgba(245, 230, 211, 0.5); font-style: italic;">
                                <i class="lucide-package-x" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px; opacity: 0.3;"></i>
                                No ingredients yet. Click "Add New Ingredient" to get started!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="javascript:history.back()" class="btn btn-outline-light fw-bold mt-4">
                <i class="lucide-arrow-left" style="width: 18px; height: 18px;"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
<script src="{{ asset('assets/js/raw-material.js') }}"></script>

@if(Session::has('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '{{ Session::get('success') }}',
    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
    color: '#f5e6d3',
    confirmButtonColor: '#d4a373',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

@endsection
