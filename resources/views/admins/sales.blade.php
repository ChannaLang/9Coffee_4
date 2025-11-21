@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-5">
      <div class="mb-4">
                <a href="javascript:history.back()" class="btn btn-outline-light fw-bold">
                    <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
                </a>
            </div>
    <div class="card shadow-sm border-0 rounded-4" style="background-color:#3e2f2f; color:#f5f5f5;">
        <div class="card-header" style="background-color:#db770cff; color:#fff;">
            <a href="{{ route('admin.sales.report.download') }}" class="btn btn-success mb-3">
    📥 Download Report
</a>

            <h4 class="mb-0">📈 Sales Report (Last 30 Days)</h4>
        </div>
        <div class="card-body">
            <hr class="my-4">

<h4 class="text-center mb-3">📊 Sales Distribution (Last 30 Days)</h4>

<div style="max-width: 300px; margin: 0 auto;">
    <canvas id="salesPieChart"></canvas>
</div>

            <table class="table table-bordered table-striped table-hover" style="color:#f5f5f5;">
                <thead style="background-color:#5a3d30;">
                    <tr class="text-center">
                        <th>Date</th>
                        <th>Total Orders</th>
                        <th>Total Sales ($)</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse ($sales as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td>{{ $row->total_orders }}</td>
                            <td>${{ number_format($row->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No sales data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
                    '{{ $row->date }}',
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
                    '#6b4c3b',
                    '#4b3a2f',
                    '#ff9800',
                    '#795548',
                    '#c57a44'
                ],
                borderColor: '#3e2f2f',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#fff' }
                }
            }
        }
    });
</script>

@endsection
