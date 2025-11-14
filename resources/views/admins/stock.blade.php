@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/raw-material.css') }}">

<div class="container-fluid mt-4">

    <!-- Add Material Button -->
    <div class="d-flex justify-content-end mb-3">
        <button id="btnAddMaterial"
                class="btn btn-add-material"
                data-url="{{ route('raw-material.store') }}">
            ➕ Add New Raw Ingredient
        </button>
    </div>

    <div class="card shadow-sm border-0 rounded-4 w-100 raw-card">
        <div class="card-header raw-header">
            <h4 class="mb-0">🧾 Raw Ingredient Stock</h4>
        </div>

        <div class="card-body">

            <!-- Stock Table -->
            <div class="table-responsive">
                <table class="table align-middle mb-0 raw-table">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th>Raw Ingredient</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        @foreach($rawMaterials as $index => $material)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td id="displayName{{ $material->id }}">{{ $material->name }}</td>
                            <td id="displayQty{{ $material->id }}">{{ number_format($material->quantity, 2) }}</td>
                            <td id="displayUnit{{ $material->id }}">{{ $material->unit }}</td>

                            <td>
                                <span class="badge {{ $material->quantity < 5 ? 'bg-danger' : 'bg-success' }}">
                                    {{ $material->quantity < 5 ? 'Low' : 'OK' }}
                                </span>
                            </td>

                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-action btn-success btnAddStock"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            data-unit="{{ $material->unit }}">
                                        ➕
                                    </button>

                                    <button class="btn btn-action btn-warning btnReduceStock"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            data-unit="{{ $material->unit }}">
                                        ➖
                                    </button>

                                    <button class="btn btn-action btn-primary btnUpdateMaterial"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}"
                                            data-unit="{{ $material->unit }}">
                                        🔄
                                    </button>

                                    <button class="btn btn-action btn-danger btnDeleteMaterial"
                                            data-id="{{ $material->id }}"
                                            data-name="{{ $material->name }}">
                                        🗑
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            <a href="javascript:history.back()" class="btn btn-outline-light fw-bold mt-4">
                <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/raw-material.js') }}"></script>

@if(Session::has('success'))
<script>
Swal.fire({
  icon: 'success',
  title: 'Success!',
  text: '{{ Session::get('success') }}',
  confirmButtonColor: '#db770c'
});
</script>
@endif
@endsection
