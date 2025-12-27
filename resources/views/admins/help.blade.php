@extends('layouts.admin')

@section('title', 'Help Center')

@section('content')
<link href="{{ asset('assets/css/help-admin.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

<div class="help-container">
    <div class="help-main">
        <div class="card-header">
            <h4 class="mb-0">Help Center</h4>
            <div style="width: 180px;"></div>
        </div>
        <div class="card-body">
            <p class="intro-text text-center mb-4">
                Welcome to the Help Center! Explore the sections below to manage your Coffee Shop Admin Dashboard efficiently.
            </p>

            <div class="help-cards">

                <!-- Dashboard Card -->
                <div class="help-card text-center">
                    <div class="help-icon">📊</div>
                    <h5>Dashboard</h5>
                    <ul>
                        <li><strong>Daily Views:</strong> Track visitors and peak hours.</li>
                        <li><strong>Sales Overview:</strong> Completed orders & revenue trends.</li>
                        <li><strong>Comments:</strong> Monitor customer feedback.</li>
                        <li><strong>Earnings:</strong> Total revenue & summaries.</li>
                        <li><strong>Reports:</strong> Export dashboard data.</li>
                    </ul>
                </div>

                <!-- Customers Card -->
                <div class="help-card text-center">
                    <div class="help-icon">👥</div>
                    <h5>Customers</h5>
                    <ul>
                        <li><strong>Search & Filter:</strong> Find by name, email, location.</li>
                        <li><strong>Details:</strong> View profile, orders, preferences.</li>
                        <li><strong>Edit Info:</strong> Update contact info.</li>
                        <li><strong>Delete:</strong> Remove inactive/test accounts.</li>
                        <li><strong>Communication:</strong> Send targeted notifications.</li>
                    </ul>
                </div>

                <!-- Orders Card -->
                <div class="help-card text-center">
                    <div class="help-icon">🛒</div>
                    <h5>Orders</h5>
                    <ul>
                        <li><strong>Order Info:</strong> Products, quantities, payment & delivery status.</li>
                        <li><strong>Update Status:</strong> Pending, Delivered, In Progress, Returned.</li>
                        <li><strong>Payment:</strong> Confirm "Paid" or "Due".</li>
                        <li><strong>Track Time:</strong> Order creation & delivery timestamps.</li>
                        <li><strong>Bulk Actions:</strong> Export or delete multiple orders.</li>
                    </ul>
                </div>

                <!-- Messages Card -->
                <div class="help-card text-center">
                    <div class="help-icon">✉️</div>
                    <h5>Messages</h5>
                    <ul>
                        <li><strong>Inbox:</strong> Read inquiries & feedback.</li>
                        <li><strong>Respond:</strong> Reply promptly to maintain satisfaction.</li>
                        <li><strong>Issues:</strong> Resolve complaints efficiently.</li>
                        <li><strong>Mark Important:</strong> Flag messages for follow-up.</li>
                        <li><strong>History:</strong> Review past conversations.</li>
                    </ul>
                </div>

                <!-- Settings Card -->
                <div class="help-card text-center">
                    <div class="help-icon">⚙️</div>
                    <h5>Settings</h5>
                    <ul>
                        <li><strong>Profile:</strong> Update name, picture, contact info.</li>
                        <li><strong>Preferences:</strong> Change language, theme, time zone.</li>
                        <li><strong>Notifications:</strong> Enable/disable alerts.</li>
                        <li><strong>Security:</strong> Two-factor authentication if available.</li>
                        <li><strong>Backup:</strong> Export your settings.</li>
                    </ul>
                </div>

                <!-- Password Card -->
                <div class="help-card text-center">
                    <div class="help-icon">🔒</div>
                    <h5>Password</h5>
                    <ul>
                        <li><strong>Strong Password:</strong> Symbols, numbers, uppercase.</li>
                        <li><strong>Update Regularly:</strong> Change every few months.</li>
                        <li><strong>Security Alert:</strong> Change if compromised.</li>
                        <li><strong>Password Recovery:</strong> Use "Forgot Password".</li>
                        <li><strong>Do Not Share:</strong> Never share credentials.</li>
                    </ul>
                </div>

                <!-- Sign Out Card -->
                <div class="help-card text-center">
                    <div class="help-icon">🚪</div>
                    <h5>Sign Out</h5>
                    <ul>
                        <li><strong>End Session:</strong> Always log out after work.</li>
                        <li><strong>Security:</strong> Prevent unauthorized access.</li>
                        <li><strong>Multiple Devices:</strong> Log out from unused devices.</li>
                        <li><strong>Session Timeout:</strong> Auto-expire for safety.</li>
                        <li><strong>Check Active Logins:</strong> Monitor recent logins.</li>
                    </ul>
                </div>

            </div>

            <p class="support-text text-center mt-4">
                Need more help? Contact <a href="mailto:support@coffeeshop.com">support@coffeeshop.com</a>
            </p>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
