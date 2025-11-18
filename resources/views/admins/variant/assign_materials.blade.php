@extends('layouts.admin')

@section('content')
<h2>Assign Raw Materials for {{ $variant->name }}</h2>

<form action="{{ route('admin.product.variants.storeMaterials', $variant->id) }}" method="POST">
    @csrf
    <table border="1">
        <tr>
            <th>Material</th>
            <th>Available Qty</th>
            <th>Qty Required</th>
        </tr>
        @foreach($rawMaterials as $material)
        <tr>
            <td>{{ $material->name }}</td>
            <td>{{ $material->quantity }} {{ $material->unit }}</td>
            <td>
                <input type="number" step="0.01" min="0" name="materials[{{ $material->id }}]"
                       value="{{ $variant->rawMaterials->firstWhere('id', $material->id)->pivot->quantity_required ?? 0 }}">
            </td>
        </tr>
        @endforeach
    </table>
    <button type="submit">Save Materials</button>
</form>
@endsection
