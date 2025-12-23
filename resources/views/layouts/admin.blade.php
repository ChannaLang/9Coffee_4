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
/* =========================================================
   IMPROVED NAVIGATION BAR - COFFEE THEME
========================================================= */

:root {
    --sidebar-collapsed: 70px;
    --sidebar-expanded: 260px;
    --coffee-dark: #2d1b14;
    --coffee-medium: #3e2723;
    --caramel: #e67e22;
    --caramel-dark: #d35400;
    --cream: #fef5e7;
}

/* Wrapper */
#wrapper {
    display: flex;
    min-height: 100vh;
    position: relative;
}

/* Navigation Sidebar */
.navigation {
    width: var(--sidebar-collapsed);
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    background: rgba(62, 39, 35, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-right: 1px solid rgba(230, 126, 34, 0.2);
    overflow-y: auto;
    overflow-x: hidden;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
}

/* Expand on hover */
.navigation:hover {
    width: var(--sidebar-expanded);
}

/* Custom Scrollbar */
.navigation::-webkit-scrollbar {
    width: 6px;
}

.navigation::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
}

.navigation::-webkit-scrollbar-thumb {
    background: rgba(230, 126, 34, 0.4);
    border-radius: 3px;
}

.navigation::-webkit-scrollbar-thumb:hover {
    background: rgba(241, 240, 238, 0.6);
}

/* Logo/Brand Section */
.sidebar-brand {
    padding: 24px 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(230, 126, 34, 0.2);
    margin-bottom: 12px;
    cursor: pointer;
}

.brand-icon {
    width: 46px;
    height: 46px;
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(230, 126, 34, 0.5);
    transition: transform 0.3s;
}

.navigation:hover .brand-icon {
    transform: rotate(360deg);
}

.brand-text {
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s;
    white-space: nowrap;
    overflow: hidden;
}

.navigation:hover .brand-text {
    opacity: 1;
    transform: translateX(0);
}

.brand-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--caramel);
    margin: 0;
    line-height: 1.2;
}

.brand-subtitle {
    font-size: 11px;
    color: rgba(245, 230, 211, 0.5);
    margin: 0;
}

/* Navigation Menu */
.navigation ul {
    list-style: none;
    padding: 0 8px;
    margin: 0;
}

.navigation ul li {
    margin-bottom: 4px;
}

.navigation ul li a {
    display: flex;
    align-items: center;
    padding: 12px 10px;
    color: rgba(245, 230, 211, 0.7);
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

/* Accent border on hover/active */
.navigation ul li a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background: var(--caramel);
    transform: scaleY(0);
    transition: transform 0.3s;
    border-radius: 0 3px 3px 0;
}

.navigation ul li a:hover::before,
.navigation ul li.active a::before {
    transform: scaleY(1);
}

.navigation ul li a:hover,
.navigation ul li.active a {
    background: rgba(230, 126, 34, 0.2);
    color: var(--cream);
}

/* Icon styling */
.navigation ul li a .icon {
    width: 46px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 22px;
}

.navigation ul li a .icon ion-icon {
    color: rgba(255, 255, 255, 0.9);
    transition: all 0.3s;
}

.navigation ul li a:hover .icon ion-icon,
.navigation ul li.active a .icon ion-icon {
    color: var(--caramel);
    transform: scale(1.1);
}

/* Title text */
.navigation ul li a .title {
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s;
}

.navigation:hover ul li a .title {
    opacity: 1;
    transform: translateX(0);
}

/* Logout button special styling */
.navigation ul li:last-child {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(230, 126, 34, 0.15);
}

.navigation ul li:last-child a {
    background: rgba(239, 83, 80, 0.1);
    border: 1px solid rgba(239, 83, 80, 0.2);
}

.navigation ul li:last-child a:hover {
    background: rgba(239, 83, 80, 0.2);
    border-color: rgba(239, 83, 80, 0.4);
    color: #ff6b6b;
}

.navigation ul li:last-child a .icon ion-icon {
    color: rgba(239, 83, 80, 0.8);
}

.navigation ul li:last-child a:hover .icon ion-icon {
    color: #ff6b6b;
}

/* Tooltip for collapsed state */
.navigation ul li a {
    position: relative;
}

.navigation ul li a::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-left: 12px;
    padding: 8px 14px;
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 1001;
    box-shadow: 0 4px 12px rgba(230, 126, 34, 0.4);
}

.navigation:not(:hover) ul li a:hover::after {
    opacity: 1;
}

/* Main content adjustment */
.main-content {
    flex: 1;
    padding: 20px;
    color: #fff;
    min-height: 100vh;
    margin-left: var(--sidebar-collapsed);
    transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-x: auto;
    overflow-y: auto;
}

/* Badge for notifications (optional) */
.nav-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ef5350;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(239, 83, 80, 0.4);
}

/* Hover effect animations */
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.navigation:hover ul li {
    animation: slideInRight 0.3s ease-out backwards;
}

.navigation:hover ul li:nth-child(1) { animation-delay: 0.05s; }
.navigation:hover ul li:nth-child(2) { animation-delay: 0.08s; }
.navigation:hover ul li:nth-child(3) { animation-delay: 0.11s; }
.navigation:hover ul li:nth-child(4) { animation-delay: 0.14s; }
.navigation:hover ul li:nth-child(5) { animation-delay: 0.17s; }
.navigation:hover ul li:nth-child(6) { animation-delay: 0.20s; }
.navigation:hover ul li:nth-child(7) { animation-delay: 0.23s; }
.navigation:hover ul li:nth-child(8) { animation-delay: 0.26s; }
.navigation:hover ul li:nth-child(9) { animation-delay: 0.29s; }
.navigation:hover ul li:nth-child(10) { animation-delay: 0.32s; }
.navigation:hover ul li:nth-child(11) { animation-delay: 0.35s; }
.navigation:hover ul li:nth-child(12) { animation-delay: 0.38s; }

/* Active indicator pulse */
@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.6);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(230, 126, 34, 0);
    }
}

.navigation ul li.active a .icon {
    animation: pulse 2s ease-in-out infinite;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .navigation {
        width: var(--sidebar-collapsed);
    }

    .main-content {
        margin-left: var(--sidebar-collapsed);
        padding: 16px;
    }
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
