@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('assets/css/sales-report.css') }}">

<div class="container-fluid sales-container">


    {{-- Main Report Card --}}
    <div class="sales-card">
        <div class="sales-card-header">
            <div class="header-content">
                <h4><i class="bi bi-graph-up-arrow"></i> Sales Report (Last 30 Days)</h4>
                <a href="{{ route('admin.sales.report.download') }}" class="btn-download">
                    <i class="bi bi-download"></i> Download Report
                </a>
            </div>
        </div>

        <div class="sales-card-body">
            {{-- Summary Stats --}}
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card stat-orders">
                        <div class="stat-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Total Orders</span>
                            <span class="stat-value">{{ $sales->sum('total_orders') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card stat-sales">
                        <div class="stat-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Total Sales</span>
                            <span class="stat-value">${{ number_format($sales->sum('total_sales'), 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card stat-average">
                        <div class="stat-icon">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Average Order</span>
                            <span class="stat-value">${{ $sales->sum('total_orders') > 0 ? number_format($sales->sum('total_sales') / $sales->sum('total_orders'), 2) : '0.00' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Section --}}
            <div class="chart-section">
                <h5 class="chart-title">
                    <i class="bi bi-pie-chart"></i> Sales Distribution (Last 30 Days)
                </h5>
                <div class="chart-container">
                    <canvas id="salesPieChart"></canvas>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="table-section">
                <h5 class="table-title">
                    <i class="bi bi-table"></i> Detailed Sales Data
                </h5>
                <div class="sales-table-container">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-calendar3"></i> Date</th>
                                <th><i class="bi bi-bag"></i> Total Orders</th>
                                <th><i class="bi bi-cash-stack"></i> Total Sales ($)</th>
                                <th><i class="bi bi-graph-up"></i> Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $row)
                                <tr class="sales-row">
                                    <td class="date-cell">
                                        <span class="date-badge">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</span>
                                    </td>
                                    <td class="orders-cell">
                                        <span class="orders-badge">{{ $row->total_orders }}</span>
                                    </td>
                                    <td class="sales-cell">
                                        <span class="sales-amount">${{ number_format($row->total_sales, 2) }}</span>
                                    </td>
                                    <td class="performance-cell">
                                        <div class="performance-bar">
                                            <div class="performance-fill" style="width: {{ ($row->total_sales / $sales->max('total_sales')) * 100 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <div class="empty-content">
                                            <i class="bi bi-inbox"></i>
                                            <p>No sales data available</p>
                                            <span>Data will appear here once you have sales</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($sales->isNotEmpty())
                        <tfoot>
                            <tr class="total-row">
                                <td class="total-label">Total</td>
                                <td class="total-orders">{{ $sales->sum('total_orders') }}</td>
                                <td class="total-sales" colspan="2">${{ number_format($sales->sum('total_sales'), 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const pieCtx = document.getElementById('salesPieChart').getContext('2d');

    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: [
                @foreach ($sales as $row)
                    '{{ \Carbon\Carbon::parse($row->date)->format('M d') }}',
                @endforeach
            ],
            datasets: [{
                label: 'Sales ($)',
                data: [
                    @foreach ($sales as $row)
                        {{ $row->total_sales }},
                    @endforeach
                ],
                backgroundColor: [
                    '#db770c',
                    '#ffb74d',
                    '#ffa726',
                    '#ff9800',
                    '#fb8c00',
                    '#f57c00',
                    '#ef6c00',
                    '#e65100',
                    '#d84315',
                    '#c57a44',
                    '#b8845e',
                    '#a1887f',
                    '#8d6e63',
                    '#795548',
                    '#6d4c41',
                    '#5d4037',
                    '#4e342e',
                    '#3e2723'
                ],
                borderColor: '#1a0f0a',
                borderWidth: 3,
                hoverBorderWidth: 4,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#f5e6d3',
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        padding: 15,
                        boxWidth: 15,
                        boxHeight: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 24, 16, 0.95)',
                    titleColor: '#f5e6d3',
                    bodyColor: '#d4a373',
                    borderColor: '#db770c',
                    borderWidth: 2,
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return ' $' + context.parsed.toFixed(2);
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });
</script>

@endsection
