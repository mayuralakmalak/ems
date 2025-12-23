@extends('layouts.exhibitor')

@section('title', 'Create Badge')
@section('page-title', 'Create New Badge')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Create New Badge</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('badges.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Booking *</label>
                <select name="booking_id" class="form-select" id="bookingSelect" required>
                    <option value="">Select Booking</option>
                    @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}">{{ $booking->booking_number }} - {{ $booking->exhibition->name ?? 'N/A' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <div id="badgeLimitsBox" class="alert alert-info d-none">
                    <strong>Badge limits for this booking:</strong>
                    <ul id="badgeLimitsList" class="mb-0 mt-2"></ul>
                </div>
                <small id="badgeLimitsEmpty" class="text-muted d-none">
                    No badge configuration found for this event. Please contact the organizer if you believe this is an error.
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">Badge Type *</label>
                <select name="badge_type" class="form-select" id="badgeTypeSelect" required>
                    <option value="">Select Type</option>
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option>
                    <option value="Additional">Additional</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>

            <div class="mb-3">
                <label class="form-label">Valid For Date</label>
                <input type="date" name="valid_for_date" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Access Permissions</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Entry Only" id="entry">
                    <label class="form-check-label" for="entry">Entry Only</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Lunch" id="lunch">
                    <label class="form-check-label" for="lunch">Lunch</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Snacks" id="snacks">
                    <label class="form-check-label" for="snacks">Snacks</label>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('badges.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Badge</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bookingSelect = document.getElementById('bookingSelect');
        const badgeTypeSelect = document.getElementById('badgeTypeSelect');
        const badgeLimitsBox = document.getElementById('badgeLimitsBox');
        const badgeLimitsList = document.getElementById('badgeLimitsList');
        const badgeLimitsEmpty = document.getElementById('badgeLimitsEmpty');

        if (!bookingSelect) {
            return;
        }

        const bookingLimitsUrlTemplate = "{{ route('badges.booking-limits', ['bookingId' => ':bookingId']) }}";

        function updateBadgeTypeOptions(limits) {
            if (!badgeTypeSelect) {
                return;
            }

            // Reset all options first
            Array.from(badgeTypeSelect.options).forEach(option => {
                if (!option.value) {
                    return;
                }
                option.disabled = false;
            });

            limits.forEach(item => {
                const option = Array.from(badgeTypeSelect.options)
                    .find(opt => opt.value === item.badge_type);

                if (option && item.remaining <= 0) {
                    option.disabled = true;
                }
            });
        }

        function clearBadgeLimits() {
            badgeLimitsList.innerHTML = '';
            badgeLimitsBox.classList.add('d-none');
            badgeLimitsEmpty.classList.add('d-none');
            if (badgeTypeSelect) {
                Array.from(badgeTypeSelect.options).forEach(option => {
                    if (!option.value) {
                        return;
                    }
                    option.disabled = false;
                });
            }
        }

        bookingSelect.addEventListener('change', function () {
            const bookingId = this.value;

            if (!bookingId) {
                clearBadgeLimits();
                return;
            }

            const url = bookingLimitsUrlTemplate.replace(':bookingId', bookingId);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.data || data.data.length === 0) {
                        badgeLimitsList.innerHTML = '';
                        badgeLimitsBox.classList.add('d-none');
                        badgeLimitsEmpty.classList.remove('d-none');
                        updateBadgeTypeOptions([]);
                        return;
                    }

                    badgeLimitsList.innerHTML = '';

                    data.data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = `${item.badge_type}: Allowed ${item.allowed}, Used ${item.used}, Remaining ${item.remaining}`;
                        badgeLimitsList.appendChild(li);
                    });

                    badgeLimitsEmpty.classList.add('d-none');
                    badgeLimitsBox.classList.remove('d-none');

                    updateBadgeTypeOptions(data.data);
                })
                .catch(() => {
                    // On error, just clear and hide limits
                    clearBadgeLimits();
                });
        });
    });
</script>
@endpush
@endsection

