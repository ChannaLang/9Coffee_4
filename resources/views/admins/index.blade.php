@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<div class="dashboard-container">

    {{-- Stats Cards --}}
    <div class="cardBox">
        <a href="{{ route('all.bookings') }}" class="card">
            <div>
                <div class="numbers">{{ $productsCount }}</div>
                <div class="cardName">
                    <i class="lucide-calendar"></i>
                    Total Bookings
                </div>
            </div>
            <div class="iconBx">
                <i class="lucide-calendar-check"></i>
            </div>
        </a>

        <a href="{{ route('all.orders') }}" class="card">
            <div>
                <div class="numbers">{{ $ordersCount }}</div>
                <div class="cardName">
                    <i class="lucide-shopping-cart"></i>
                    Total Orders
                </div>
            </div>
            <div class="iconBx">
                <i class="lucide-shopping-bag"></i>
            </div>
        </a>

        <a href="{{ route('all.orders') }}" class="card">
            <div>
                <div class="numbers">${{ number_format($earning, 2) }}</div>
                <div class="cardName">
                    <i class="lucide-dollar-sign"></i>
                    Total Earnings
                </div>
            </div>
            <div class="iconBx">
                <i class="lucide-wallet"></i>
            </div>
        </a>
    </div>

    {{-- Recent Orders & Analytics --}}
    <div class="details row">
        {{-- Recent Orders Table --}}
        <div class="recentOrders card flex-fill" id="recentOrdersCard">
            <div class="cardHeader recent-orders-header">
                <h2 class="fw-bold mb-0">
                    <i class="lucide-receipt"></i>
                    Recent Orders
                </h2>
                <a href="{{ route('all.orders') }}" class="btn-view-all">
                    View All
                    <i class="lucide-arrow-right"></i>
                </a>
            </div>

            <div class="recent-orders-table mt-0">
                <table class="table table-hover text-white mb-0">
                    <thead>
                        <tr>
                            <th>
                                <i class="lucide-package"></i>
                                Product
                            </th>
                            <th>
                                <i class="lucide-dollar-sign"></i>
                                Price
                            </th>
                            <th>
                                <i class="lucide-credit-card"></i>
                                Payment
                            </th>
                            <th>
                                <i class="lucide-activity"></i>
                                Status
                            </th>
                            <th>
                                <i class="lucide-clock"></i>
                                Order Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td>
                                <i class="lucide-coffee"></i>
                                {{ $order->product->name ?? 'N/A' }}
                            </td>
                            <td>${{ number_format($order->price, 2) }}</td>
                            <td>{{ $order->payment_status ?? 'Pending' }}</td>
                            <td>
                                <span class="badge
                                    @if(strtolower($order->status)=='pending') bg-warning
                                    @elseif(strtolower($order->status)=='cancelled') bg-danger
                                    @else bg-success
                                    @endif px-3 py-1 rounded-pill">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->timezone('Asia/Phnom_Penh')->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="lucide-inbox"></i>
                                No recent orders
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Analytics Chart --}}
        <div class="analytics card flex-fill" id="analyticsCard">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="lucide-bar-chart-3"></i>
                    Analytics Overview
                </h4>
            </div>
            <div class="card-body">
                <canvas id="analyticsChart"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- Scripts --}}
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Make chart height equal to table height
    function matchChartHeight() {
        const tableCard = document.getElementById('recentOrdersCard');
        const chartCard = document.getElementById('analyticsCard');

        if(tableCard && chartCard){
            const tableHeight = tableCard.offsetHeight;
            chartCard.style.height = tableHeight + 'px';
        }
    }

    window.addEventListener('load', matchChartHeight);
    window.addEventListener('resize', matchChartHeight);

    // Analytics Chart
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    const analyticsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Sales', 'Orders', 'Expenses', 'Profit'],
            datasets: [{
                label: 'Statistics',
                data: [
                    {{ $totalSales ?? 0 }},
                    {{ $ordersCount ?? 0 }},
                    {{ $totalExpenses ?? 0 }},
                    {{ $earning ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(66, 165, 245, 0.8)',
                    'rgba(255, 167, 38, 0.8)',
                    'rgba(239, 83, 80, 0.8)',
                    'rgba(67, 160, 71, 0.8)'
                ],
                borderColor: 'rgba(245, 230, 211, 0.3)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 13,
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        color: '#f5e6d3',
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 24, 16, 0.95)',
                    titleColor: '#d4a373',
                    bodyColor: '#f5e6d3',
                    borderColor: 'rgba(212, 163, 115, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true
                }
            }
        }
    });
</script>

@endsection
