@extends('layouts.admin')

@section('content')
<h2>Edit Variant: {{ $variant->name }}</h2>

<form action="{{ route('admins.variants.update', $variant->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div>
        <label>Name:</label>
        <input type="text" name="name" value="{{ $variant->name }}" required>
    </div>
    <div>
        <label>Price:</label>
        <input type="number" name="price" step="0.01" value="{{ $variant->price }}" required>
    </div>
    <button type="submit">Update Variant</button>
</form>
@endsection
