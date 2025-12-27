@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('assets/css/booking-admin.css') }}">

<div class="container-fluid booking-container">


    {{-- Flash Messages --}}
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show booking-alert" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ Session::get('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show booking-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Create Booking Form --}}
        <div class="col-lg-5">
            <div class="booking-card booking-form-card">
                <div class="booking-card-header">
                    <h4><i class="bi bi-calendar-plus"></i> Create New Booking</h4>
                </div>
                <div class="booking-card-body">
                    <form action="{{ route('store.bookings') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="booking-label">
                                    <i class="bi bi-person"></i> First Name
                                </label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}"
                                       class="booking-input" placeholder="Enter first name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="booking-label">
                                    <i class="bi bi-person"></i> Last Name
                                </label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}"
                                       class="booking-input" placeholder="Enter last name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="booking-label">
                                    <i class="bi bi-calendar-event"></i> Date
                                </label>
                                <input type="date" name="date" value="{{ old('date') }}"
                                       class="booking-input" required>
                            </div>
                            <div class="col-md-6">
                                <label class="booking-label">
                                    <i class="bi bi-clock"></i> Time
                                </label>
                                <input type="time" name="time" value="{{ old('time') }}"
                                       class="booking-input" required>
                            </div>
                            <div class="col-12">
                                <label class="booking-label">
                                    <i class="bi bi-telephone"></i> Phone
                                </label>
                                <input type="tel" name="phone" value="{{ old('phone') }}"
                                       class="booking-input" placeholder="Enter phone number" required>
                            </div>
                            <div class="col-12">
                                <label class="booking-label">
                                    <i class="bi bi-chat-left-text"></i> Message (Optional)
                                </label>
                                <textarea name="message" rows="3" class="booking-input booking-textarea"
                                          placeholder="Add a message or special request">{{ old('message') }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit-booking">
                            <i class="bi bi-check-circle"></i> Create Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bookings Table --}}
        <div class="col-lg-7">
            <div class="booking-card booking-table-card">
                <div class="booking-card-header">
                    <h4><i class="bi bi-list-check"></i> All Bookings</h4>
                </div>
                <div class="booking-card-body">
                    <div class="booking-table-container">
                        <table class="booking-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Date & Time</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bookings as $booking)
                                    <tr id="booking-{{ $booking->id }}" class="booking-row">
                                        <td class="booking-number">{{ $loop->iteration }}</td>
                                        <td class="booking-name">
                                            <div class="name-wrapper">
                                                <i class="bi bi-person-circle"></i>
                                                <span>{{ $booking->first_name }} {{ $booking->last_name }}</span>
                                            </div>
                                        </td>
                                        <td class="booking-datetime">
                                            <div class="datetime-wrapper">
                                                <span class="date-text">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                                                </span>
                                                <span class="time-text">
                                                    <i class="bi bi-clock"></i>
                                                    {{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="booking-phone">
                                            <i class="bi bi-telephone"></i>
                                            {{ $booking->phone }}
                                        </td>
                                        <td class="booking-status">
                                            <span class="status-badge status-{{ strtolower($booking->status) }}">
                                                <i class="bi bi-{{ $booking->status == 'Pending' ? 'hourglass-split' : ($booking->status == 'Confirmed' ? 'check-circle' : 'x-circle') }}"></i>
                                                {{ $booking->status }}
                                            </span>
                                        </td>
                                        <td class="booking-actions">
                                            <div class="action-buttons">
                                                <button type="button"
                                                    class="btn-action btn-edit"
                                                    data-id="{{ $booking->id }}"
                                                    data-status="{{ $booking->status }}"
                                                    title="Change Status">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn-action btn-delete"
                                                    data-id="{{ $booking->id }}"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-inbox"></i>
                                                <p>No bookings found</p>
                                                <span>Create your first booking to get started</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/booking-admin.js') }}"></script>
@endsection
