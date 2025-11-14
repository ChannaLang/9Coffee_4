<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/admin.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/icomoon.css') }}">

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <style>
        /* Fix for sidebar & main content layout */
        #wrapper {
            display: flex;
            min-height: 100vh;
            background: rgba(0,0,0,0.6);
        }

        .navigation {
            width: 250px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            background: rgba(0,0,0,0.8);
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
            color: #fff;
        }
    </style>
</head>
<body style="background-image: url('{{ asset('assets/images/bg_1.jpg') }}');
             background-size: cover;
             background-position: center;
             background-attachment: fixed;
             min-height: 100vh;">

<div id="wrapper">

@auth('admin')
    <!-- Sidebar Navigation -->
    <div class="navigation">
        <ul>
            <li class="{{ Request::routeIs('all.admins') ? 'active' : '' }}">
                <a href="{{ route('all.admins') }}">
                    <span class="icon"><ion-icon name="people"></ion-icon></span>
                    <span class="title">Admin</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('all.bookings') ? 'active' : '' }}">
                <a href="{{ route('all.bookings') }}">
                    <span class="icon"><ion-icon name="calendar-outline"></ion-icon></span>
                    <span class="title">Bookings Management</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('all.orders') ? 'active' : '' }}">
                <a href="{{ route('all.orders') }}">
                    <span class="icon"><ion-icon name="receipt-outline"></ion-icon></span>
                    <span class="title">Order Management</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('admin.raw-material.stock') ? 'active' : '' }}">
                <a href="{{ route('admin.raw-material.stock') }}">
                    <span class="icon"><ion-icon name="cube-outline"></ion-icon></span>
                    <span class="title">Ingredients Management</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('all.products') || Request::routeIs('create.products') ? 'active' : '' }}">
                <a href="{{ route('all.products') }}">
                    <span class="icon"><ion-icon name="cart-outline"></ion-icon></span>
                    <span class="title">Products Management</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('admin.low.stock') ? 'active' : '' }}">
                <a href="{{ route('admin.low.stock') }}">
                    <span class="icon"><ion-icon name="warning-outline"></ion-icon></span>
                    <span class="title">Stock Product Management</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('admins.help') ? 'active' : '' }}">
                <a href="{{ route('admins.help') }}">
                    <span class="icon"><ion-icon name="help-outline"></ion-icon></span>
                    <span class="title">Help</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('staff.sell.form') ? 'active' : '' }}">
                <a href="{{ route('staff.sell.form') }}">
                    <span class="icon"><ion-icon name="cash-outline"></ion-icon></span>
                    <span class="title">Sell Product</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('admin.sales.report') ? 'active' : '' }}">
                <a href="{{ route('admin.sales.report') }}">
                    <span class="icon"><ion-icon name="bar-chart-outline"></ion-icon></span>
                    <span class="title">Total Sales Report</span>
                </a>
            </li>
            <li class="{{ Request::routeIs('admin.expenses') ? 'active' : '' }}">
                <a href="{{ route('admin.expenses') }}">
                    <span class="icon"><ion-icon name="cash-outline"></ion-icon></span>
                    <span class="title">Expenses</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                    <span class="title">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
@endauth

<!-- Main Content -->
<div class="main-content">
    @yield('content')
</div>

</div>

<!-- SweetAlert Delete Confirmation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "This order will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b7410e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                background: '#201d1dff',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    const deleteAllBtn = document.querySelector('.btn-delete-all');
    if(deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Delete All Orders?',
                text: "This will remove all orders permanently.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b7410e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete all!',
                background: '#3e2f2f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    }
});
</script>

</body>
</html>
