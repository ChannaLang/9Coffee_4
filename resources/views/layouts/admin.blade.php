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
    <!-- In layouts/admin.blade.php, inside <head> -->
    <link rel="stylesheet" href="{{ asset('assets/css/category.css') }}">


    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<style>
:root {
    --sidebar-collapsed: 40px; /* width when collapsed */
    --sidebar-expanded: 250px; /* width when expanded on hover */
}

/* Ensure full-page layout */
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    scroll-behavior: smooth;
    background-image: url('{{ asset('assets/images/bg_1.jpg') }}');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

/* Wrapper */
#wrapper {
    display: flex;
    min-height: 100vh;
    position: relative;
}

/* Sidebar */
.navigation {
    width: var(--sidebar-collapsed);
    position: fixed; /* overlay */
    top: 0;
    left: 0;
    height: 100vh;
    background: rgba(0,0,0,0.85);
    overflow-y: auto;
    overflow-x: hidden;
    transition: width 0.3s;
    z-index: 1000;
}

/* Expand on hover */
.navigation:hover {
    width: var(--sidebar-expanded);
}

/* Sidebar items */
.navigation ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.navigation ul li a {
    display: flex;
    align-items: center;
    padding: 15px 10px;
    color: #fff;
    text-decoration: none;
    transition: background 0.2s;
}

.navigation ul li a:hover,
.navigation ul li a.active {
    background: rgba(255,255,255,0.1);
}

.navigation ul li a .icon {
    font-size: 1.5rem;
    width: 40px;
    text-align: center;
}

.navigation ul li a .title {
    margin-left: 10px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s;
}

/* Show text when hovering */
.navigation:hover ul li a .title {
    opacity: 1;
}

/* Main content */
.main-content {
    flex: 1;
    padding: 20px;
    color: #fff;
    min-height: 100vh;
    margin-left: var(--sidebar-collapsed); /* leave space for collapsed sidebar */
    transition: margin-left 0.3s;
    overflow-x: auto;
    overflow-y: auto;
}

/* Ensure content doesn’t shift when sidebar expands */
.navigation:hover ~ .main-content {
    margin-left: var(--sidebar-collapsed);
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

            <li class="{{ Request::routeIs('admins.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admins.dashboard') }}">
                    <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                    <span class="title">Dashboard</span>
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
            <li class="{{ Request::routeIs('admin.categories') ? 'active' : '' }}">
                <a href="{{ route('admin.categories') }}">
                    <span class="icon"><ion-icon name="layers-outline"></ion-icon></span>
                    <span class="title">Categories</span>
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
