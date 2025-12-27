@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<div class="dashboard-container">

    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div class="welcome-content">
            <h1 class="welcome-title">
                <i class="lucide-sparkles"></i>
                Welcome Back, Admin!
            </h1>
            <p class="welcome-subtitle">Here's what's happening with your coffee shop today</p>
        </div>
        <div class="welcome-date">
            <i class="lucide-calendar-days"></i>
            <span id="currentDate"></span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="cardBox">
        <a href="{{ route('all.bookings') }}" class="stat-card stat-card-bookings">
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="lucide-calendar-check"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="lucide-trending-up"></i>
                        <span>+12%</span>
                    </div>
                </div>
                <div class="stat-details">
                    <div class="stat-label">
                        <i class="lucide-calendar"></i>
                        Total Bookings
                    </div>
                    <div class="stat-number">{{ $productsCount }}</div>
                    <div class="stat-footer">Updated just now</div>
                </div>
            </div>
        </a>

        <a href="{{ route('all.orders') }}" class="stat-card stat-card-orders">
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="lucide-shopping-bag"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="lucide-trending-up"></i>
                        <span>+8%</span>
                    </div>
                </div>
                <div class="stat-details">
                    <div class="stat-label">
                        <i class="lucide-shopping-cart"></i>
                        Total Orders
                    </div>
                    <div class="stat-number">{{ $ordersCount }}</div>
                    <div class="stat-footer">Active orders tracking</div>
                </div>
            </div>
        </a>

        <a href="{{ route('all.orders') }}" class="stat-card stat-card-earnings">
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon-wrapper">
                        <i class="lucide-wallet"></i>
                    </div>
                    <div class="stat-trend trend-up">
                        <i class="lucide-trending-up"></i>
                        <span>+15%</span>
                    </div>
                </div>
                <div class="stat-details">
                    <div class="stat-label">
                        <i class="lucide-dollar-sign"></i>
                        Total Earnings
                    </div>
                    <div class="stat-number">${{ number_format($earning, 2) }}</div>
                    <div class="stat-footer">Revenue generated</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Recent Orders & Analytics --}}
    <div class="details">
        {{-- Recent Orders Table --}}
        <div class="recentOrders" id="recentOrdersCard">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="lucide-receipt"></i>
                    </div>
                    <div>
                        <h2>Recent Orders</h2>
                        <p class="section-subtitle">Latest transactions from your customers</p>
                    </div>
                </div>
                <a href="{{ route('all.orders') }}" class="btn-view-all">
                    View All
                    <i class="lucide-arrow-right"></i>
                </a>
            </div>

            <div class="recent-orders-table">
                <table class="orders-table">
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
                                Date & Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr class="order-row">
                            <td class="product-cell">
                                <div class="product-info">
                                    <div class="product-icon">
                                        <i class="lucide-coffee"></i>
                                    </div>
                                    <span>{{ $order->product->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="price-cell">${{ number_format($order->price, 2) }}</td>
                            <td class="payment-cell">
                                <span class="payment-badge">{{ $order->payment_status ?? 'Pending' }}</span>
                            </td>
                            <td class="status-cell">
                                <span class="status-badge status-{{ strtolower($order->status) }}">
                                    <i class="lucide-{{ strtolower($order->status) == 'pending' ? 'clock' : (strtolower($order->status) == 'cancelled' ? 'x-circle' : 'check-circle') }}"></i>
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="date-cell">
                                <div class="datetime-wrapper">
                                    <span class="date-text">{{ $order->created_at->timezone('Asia/Phnom_Penh')->format('d M Y') }}</span>
                                    <span class="time-text">{{ $order->created_at->timezone('Asia/Phnom_Penh')->format('H:i') }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <div class="empty-content">
                                    <i class="lucide-inbox"></i>
                                    <p>No recent orders</p>
                                    <span>Orders will appear here once customers start ordering</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Analytics Chart --}}
        <div class="analytics" id="analyticsCard">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="lucide-bar-chart-3"></i>
                    </div>
                    <div>
                        <h2>Analytics Overview</h2>
                        <p class="section-subtitle">Performance metrics at a glance</p>
                    </div>
                </div>
            </div>
            <div class="chart-body">
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

        // Update current date
        const dateElement = document.getElementById('currentDate');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateElement.textContent = new Date().toLocaleDateString('en-US', options);
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
                    '#42a5f5',
                    '#ffa726',
                    '#ef5350',
                    '#43a047'
                ],
                borderColor: 'rgba(26, 15, 10, 0.5)',
                borderWidth: 3,
                hoverBorderWidth: 4,
                hoverBorderColor: '#f5e6d3'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 13,
                            family: "'Inter', 'Segoe UI', sans-serif",
                            weight: '600'
                        },
                        color: '#f5e6d3',
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 12,
                        boxHeight: 12
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 24, 16, 0.95)',
                    titleColor: '#d4a373',
                    bodyColor: '#f5e6d3',
                    borderColor: 'rgba(212, 163, 115, 0.5)',
                    borderWidth: 2,
                    padding: 16,
                    cornerRadius: 12,
                    displayColors: true,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '$' + context.parsed.toFixed(2);
                            return label;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
</script>

@endsection
