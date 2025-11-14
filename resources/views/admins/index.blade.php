
    @extends('layouts.admin')
    @section('content')

    <body>
        <div class="container">
        <div class="cardBox">
        <a href="{{ route('all.admins') }}" class="card" style="background-color:#6c5ce7; color:#fff;">
            <div>
                <div class="numbers" style="font-size:2rem; font-weight:bold;">{{ $usersCount }}</div>
                <div class="cardName" style="font-size:1rem; font-weight:600; color:#fff; text-shadow:1px 1px 2px rgba(0,0,0,0.5);">
                    Total Admin
                </div>
            </div>
            <div class="iconBx">
                <ion-icon name="people-outline" style="color:#fff; font-size:2rem;"></ion-icon>
            </div>
        </a>

        <a href="{{ route('all.bookings') }}" class="card" style="background-color:#fdcb6e; color:#000;">
            <div>
                <div class="numbers" style="font-size:2rem; font-weight:bold;">{{ $productsCount }}</div>
                <div class="cardName" style="font-size:1rem; font-weight:600; color:#000; text-shadow:1px 1px 2px rgba(255,255,255,0.5);">
                    Total Bookings
                </div>
            </div>
            <div class="iconBx">
                <ion-icon name="calendar-outline" style="color:#000; font-size:2rem;"></ion-icon>
            </div>
        </a>

        <a href="{{ route('all.orders') }}" class="card" style="background-color:{{ $ordersCount > 50 ? '#d63031' : '#00cec9' }}; color:#fff;">
            <div>
                <div class="numbers" style="font-size:2rem; font-weight:bold;">{{ $ordersCount }}</div>
                <div class="cardName" style="font-size:1rem; font-weight:600; color:#fff; text-shadow:1px 1px 2px rgba(0,0,0,0.5);">
                    Total Orders
                </div>
            </div>
            <div class="iconBx">
                <ion-icon name="cart-outline" style="color:#fff; font-size:2rem;"></ion-icon>
            </div>
        </a>

        <a href="{{ route('all.orders') }}" class="card" style="background-color:{{ $earning > 1000 ? '#00b894' : '#e17055' }}; color:#fff;">
            <div>
                <div class="numbers" style="font-size:2rem; font-weight:bold;">${{ $earning }}</div>
                <div class="cardName" style="font-size:1rem; font-weight:600; color:#fff; text-shadow:1px 1px 2px rgba(0,0,0,0.5);">
                    Total Earnings
                </div>
            </div>
            <div class="iconBx">
                <ion-icon name="cash-outline" style="color:#fff; font-size:2rem;"></ion-icon>
            </div>
        </a>
    </div>

                <!-- ================ Order Details List ================= -->
             <div class="details row" style="display:flex; gap:30px; align-items:flex-start;">
        <div class="details row" style="display:flex; gap:30px; align-items:flex-start;">
            <div class="recentOrders card flex-fill" id="recentOrdersCard"
                style="background-color:#5a3d30; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.3); display:flex; flex-direction:column;">
                <!-- Header -->
        <div class="cardHeader recent-orders-header" style="display:flex; justify-content:space-between; align-items:center; padding:15px 20px;">
            <h2 class="fw-bold mb-0">☕ Recent Orders</h2>
            <a href="{{ route('all.orders') }}" class="btn-view-all" style="color:#fff; text-decoration:none; font-weight:600;">View All</a>
        </div>
        <!-- Table -->
        <div class="recent-orders-table mt-0" style="overflow-y:auto;">
            <table class="table table-hover text-white mb-0" style="background-color:#5a3d30;">
                <thead>
                    <tr style="background-color:#3e2f2f;">
                        <th>Product</th>
                        <th>Price</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                        <td>{{ $order->product->name ?? 'N/A' }}</td>
                        <td>${{ $order->price }}</td>
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
                        <td colspan="5" class="text-center text-muted py-3">No recent orders</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="analytics card flex-fill" id="analyticsCard"
         style="background-color:#2c2c2c; color:white; display:flex; flex-direction:column;">
        <div class="card-header" style="background-color:#3e2f2f; padding:10px 15px;">
            <h4 class="mb-0">Analytics Overview</h4>
        </div>
        <div class="card-body" style="flex:1; display:flex; align-items:center; justify-content:center;">
            <canvas id="analyticsChart" style="width:100%; height:100%;"></canvas>
        </div>
    </div>

</div>

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

    // Run on load and on window resize
    window.addEventListener('load', matchChartHeight);
    window.addEventListener('resize', matchChartHeight);
</script>
</div>

        <!-- =========== Scripts =========  -->
        <script src="assets/js/main.js"></script>

        <!-- ====== ionicons ======= -->
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        const analyticsChart = new Chart(ctx, {
            type: 'bar', // can change to 'line', 'pie', etc.
            data: {
                labels: ['SaleTotal', 'Orders', 'Expense', 'Earnings'],
                datasets: [{
    label: 'Statistics',
    data: [{{ $totalSales ?? 0 }}, {{ $ordersCount ?? 0 }}, {{ $totalExpenses ?? 0 }}, {{ $earning ?? 0 }}],
    backgroundColor: [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)'
    ],
    borderColor: [
        'rgba(54, 162, 235, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)'
    ],
    borderWidth: 1,
    borderRadius: 8,
}]

            },
            options: {
    responsive: true,
    scales: {
        y: {
            min: 1,
            beginAtZero: false,
            ticks: {
                stepSize: 1
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
}
        });
    </script>
    </body>
    @endsection

