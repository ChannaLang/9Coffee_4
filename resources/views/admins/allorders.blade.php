@extends('layouts.admin')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-5">

    <div class="card shadow-sm border-0 rounded-4 w-100" style="background-color: #3e2f2f; color: #f5f5f5;">

        {{-- Card Header --}}
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #db770c; color: #fff;">
            <a href="javascript:history.back()" class="btn btn-outline-light fw-bold">
                <i class="bi bi-arrow-left-circle"></i> Back
            </a>
            <h4 class="mb-0 text-center grow">Orders List</h4>
            <div></div> {{-- Empty div to balance flex spacing --}}
        </div>

        {{-- Card Body --}}
        <div class="card-body">

            {{-- Flash Messages --}}
            @if(Session::has('update'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('update') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(Session::has('delete'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ Session::get('delete') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Orders Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center" style="border:1px solid #6b4c3b; color:#f5f5f5;">
                    <thead style="background-color: #5a3d30;">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach ($allOrders as $order)
                            @php
                                $statusColors = [
                                    'Pending'   => '#db770c', // caramel orange
                                    'Paid'      => '#6b4c3b', // café brown
                                    'Cancelled' => '#b02a37'  // deep red
                                ];
                            @endphp
                            <tr>
                                <th scope="row">{{ $counter }}</th>
                                <td>{{ $order->first_name }}</td>
                                <td>
                                    {{ $order->product->name ?? 'N/A' }}
                                    @if($order->quantity > 1)
                                        (x{{ $order->quantity }})
                                    @endif
                                </td>
                                <td>${{ number_format($order->price,2) }}</td>
                                <td>{{ $order->order_created_at ?? $order->created_at }}</td>
                                <td>
                                    <span class="badge px-3 py-1" style="background-color: {{ $statusColors[$order->status] ?? '#6b4c3b' }}; color: #fff;">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-info rounded-pill btn-edit-status"
                                            data-id="{{ $order->id }}"
                                            data-status="{{ $order->status }}">
                                            Change Status
                                        </button>
                                        <form action="{{ route('delete.orders', $order->id)}}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger rounded-pill btn-delete"
                                                data-name="{{ $order->address ?? '' }}"
                                                data-price="{{ number_format($order->price, 2) }}"
                                                data-id="{{ $order->id }}">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @php $counter++; @endphp
                        @endforeach
                    </tbody>
                    <tfoot style="background-color:#5a3d30; color:#fff;">
                        <tr>
                            <td colspan="5" class="text-end"><strong>Total Price:</strong></td>
                            <td colspan="2">${{ number_format($allOrders->sum('price'),2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-center mt-4" >
    {{ $allOrders->links('pagination::bootstrap-5') }}
</div>


            </div>

            {{-- Bulk Actions --}}
            <div class="d-flex justify-content-between mt-4 flex-wrap">
    <form action="{{ route('delete.all.orders') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all orders?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger ">
            <i class="bi bi-trash3-fill"></i> Delete All Orders
        </button>
    </form>

    <a href="{{ route('orders.export') }}" class="btn btn-success">
        <i class="bi bi-download"></i> Download Excel
    </a>
</div>


        </div>
    </div>
</div>
<link rel="stylesheet" href="{{ asset('assets/css/all-order.css') }}">

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/all-allorder.js') }}"></script>
@endsection
