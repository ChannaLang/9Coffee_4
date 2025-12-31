@extends('layouts.admin')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/all-order.css') }}">

<div class="orders-container">


    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">
                📦
            </div>
            <div class="stat-content">
                <h4>Total Orders</h4>
                <p>{{ $allOrders->total() }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon revenue">
                💰
            </div>
            <div class="stat-content">
                <h4>Total Revenue</h4>
                <p>${{ number_format($allOrders->sum('price'), 2) }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                ⏳
            </div>
            <div class="stat-content">
                <h4>Pending Orders</h4>
                <p>{{ $allOrders->where('status', 'Pending')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(Session::has('update'))
        <div class="alert-custom success">
            <i class="lucide lucide-check-circle"></i>
            {{ Session::get('update') }}
        </div>
    @endif
    @if(Session::has('delete'))
        <div class="alert-custom danger">
            <i class="lucide lucide-trash-2"></i>
            {{ Session::get('delete') }}
        </div>
    @endif

    <!-- Orders Card -->
    <div class="orders-card">
        <div class="table-responsive">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = ($allOrders->currentPage() - 1) * $allOrders->perPage() + 1; @endphp
                    @foreach ($allOrders as $order)
                        <tr>
                            <td>{{ $counter }}</td>
                            <td><strong>{{ $order->first_name }}</strong></td>
                            <td>
                                {{ $order->product->name ?? 'N/A' }}
                                @if($order->quantity > 1)
                                    <span class="quantity-badge">(×{{ $order->quantity }})</span>
                                @endif
                            </td>
                            <td><strong>${{ number_format($order->price, 2) }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($order->order_created_at ?? $order->created_at)->format('M d, Y') }}</td>
                            <td>
                                <span class="status-badge {{ strtolower($order->status) }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-action info btn-edit-status"
                                        data-id="{{ $order->id }}"
                                        data-status="{{ $order->status }}">
                                        <i class="lucide lucide-edit"></i>
                                        Change
                                    </button>
                                    <button type="button" class="btn-action danger btn-delete"
                                        data-name="{{ $order->first_name ?? '' }}"
                                        data-price="{{ number_format($order->price, 2) }}"
                                        data-id="{{ $order->id }}">
                                        <i class="lucide lucide-trash-2"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @php $counter++; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">
                            <strong>Total Revenue:</strong>
                        </td>
                        <td colspan="2">
                            <strong>${{ number_format($allOrders->sum('price'), 2) }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $allOrders->links('pagination::bootstrap-5') }}
        </div>

        <!-- Bottom Actions -->
        <div class="bottom-actions">
            <button type="button" class="btn-bulk delete-all">
                <i class="lucide lucide-trash-2"></i>
                Delete All Orders
            </button>

            <a href="{{ route('orders.export') }}" class="btn-bulk export">
                <i class="lucide lucide-download"></i>
                Export to Excel
            </a>

            <form action="{{ route('delete.all.orders') }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
<script src="{{ asset('assets/js/all-allorder.js') }}"></script>

@endsection
