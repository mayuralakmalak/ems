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
                        @php
                            $startDate = optional($booking->exhibition->start_date)->format('Y-m-d');
                            $endDate = optional($booking->exhibition->end_date)->format('Y-m-d');
                        @endphp
                        <option value="{{ $booking->id }}"
                                data-start-date="{{ $startDate }}"
                                data-end-date="{{ $endDate }}">
                            {{ $booking->booking_number }} - {{ $booking->exhibition->name ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3" id="boothSelectionContainer" style="display: none;">
                <label class="form-label">Select Booth *</label>
                <select name="booth_id" class="form-select" id="boothSelect" required>
                    <option value="">Select Booth</option>
                </select>
                <div id="singleBoothDisplay" class="mt-2 p-2 bg-light rounded" style="display: none;">
                    <small class="text-muted" id="singleBoothText"></small>
                </div>
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
                <small id="badgePriceInfo" class="text-muted d-block mt-1"></small>
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
                <label class="form-label">Valid For Date(s)</label>
                <div id="validDatesContainer">
                    <div class="input-group mb-2 valid-date-row">
                        <input type="date" name="valid_for_dates[]" class="form-control">
                        <button type="button" class="btn btn-outline-danger remove-date d-none">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addValidDateBtn">
                    <i class="bi bi-plus-circle me-1"></i>Add Another Date
                </button>
                <small class="text-muted d-block mt-1">
                    Select one or more dates when this badge is valid. For multi-day exhibitions, you can choose multiple days for daily check-in/check-out.
                </small>
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
        const validDatesContainer = document.getElementById('validDatesContainer');
        const addValidDateBtn = document.getElementById('addValidDateBtn');
        const badgePriceInfo = document.getElementById('badgePriceInfo');
        let lastLimits = [];

        if (!bookingSelect) {
            return;
        }

        const bookingLimitsUrlTemplate = "{{ route('badges.booking-limits', ['bookingId' => ':bookingId']) }}";
        const bookingBoothsUrlTemplate = "{{ route('badges.booking-booths', ['bookingId' => ':bookingId']) }}";
        const boothSelect = document.getElementById('boothSelect');
        const boothSelectionContainer = document.getElementById('boothSelectionContainer');
        const singleBoothDisplay = document.getElementById('singleBoothDisplay');
        const singleBoothText = document.getElementById('singleBoothText');
        let currentBooths = [];

        function loadBoothsForBooking(bookingId) {
            if (!bookingId) {
                if (boothSelectionContainer) boothSelectionContainer.style.display = 'none';
                if (singleBoothDisplay) singleBoothDisplay.style.display = 'none';
                if (boothSelect) {
                    boothSelect.innerHTML = '<option value="">Select Booth</option>';
                }
                currentBooths = [];
                return;
            }

            const url = bookingBoothsUrlTemplate.replace(':bookingId', bookingId);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.data || data.data.length === 0) {
                        if (boothSelectionContainer) boothSelectionContainer.style.display = 'none';
                        if (singleBoothDisplay) singleBoothDisplay.style.display = 'none';
                        currentBooths = [];
                        return;
                    }

                    currentBooths = data.data;

                    if (data.single_booth && data.data.length === 1) {
                        // Single booth - show display only
                        const booth = data.data[0];
                        if (singleBoothText) {
                            singleBoothText.textContent = `Booth: ${booth.name} - ${booth.size_label}`;
                        }
                        if (singleBoothDisplay) singleBoothDisplay.style.display = 'block';
                        if (boothSelectionContainer) boothSelectionContainer.style.display = 'block';
                        if (boothSelect) {
                            boothSelect.style.display = 'none';
                            boothSelect.innerHTML = `<option value="${booth.id}" selected>${booth.name}</option>`;
                            boothSelect.required = false;
                        }
                    } else {
                        // Multiple booths - show dropdown
                        if (boothSelect) {
                            boothSelect.innerHTML = '<option value="">Select Booth</option>';
                            data.data.forEach(booth => {
                                const option = document.createElement('option');
                                option.value = booth.id;
                                option.textContent = `${booth.name} - ${booth.size_label}`;
                                option.dataset.sizeId = booth.size_id || '';
                                boothSelect.appendChild(option);
                            });
                        }
                        if (boothSelectionContainer) boothSelectionContainer.style.display = 'block';
                        if (singleBoothDisplay) singleBoothDisplay.style.display = 'none';
                        if (boothSelect) {
                            boothSelect.style.display = 'block';
                            boothSelect.required = true;
                        }
                    }
                })
                .catch(() => {
                    if (boothSelectionContainer) boothSelectionContainer.style.display = 'none';
                    if (singleBoothDisplay) singleBoothDisplay.style.display = 'none';
                    currentBooths = [];
                });
        }

        function updateValidDateConstraints() {
            if (!bookingSelect || !validDatesContainer) {
                return;
            }

            const selectedOption = bookingSelect.options[bookingSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                // Clear constraints when no booking selected
                validDatesContainer.querySelectorAll('input[type=\"date\"][name=\"valid_for_dates[]\"]').forEach(input => {
                    input.removeAttribute('min');
                    input.removeAttribute('max');
                });
                return;
            }

            const startDate = selectedOption.getAttribute('data-start-date');
            const endDate = selectedOption.getAttribute('data-end-date');

            validDatesContainer.querySelectorAll('input[type=\"date\"][name=\"valid_for_dates[]\"]').forEach(input => {
                if (startDate) {
                    input.setAttribute('min', startDate);
                }
                if (endDate) {
                    input.setAttribute('max', endDate);
                }

                // If current value is outside range, clear it
                if (input.value) {
                    if ((startDate && input.value < startDate) || (endDate && input.value > endDate)) {
                        input.value = '';
                    }
                }
            });
        }

        function updateBadgeTypeOptions(limits) {
            // We no longer disable any badge types in the dropdown.
            // Limits and pricing are only shown as information; server-side
            // validation will enforce any hard restrictions for free quotas.
            if (!badgeTypeSelect) {
                return;
            }
            Array.from(badgeTypeSelect.options).forEach(option => {
                if (!option.value) {
                    return;
                }
                option.disabled = false;
            });
        }

        function updateBadgePriceInfo() {
            if (!badgePriceInfo || !badgeTypeSelect) {
                return;
            }

            const selectedType = badgeTypeSelect.value;
            if (!selectedType || !lastLimits || !Array.isArray(lastLimits)) {
                badgePriceInfo.textContent = '';
                return;
            }

            // Find matching item for selected booth or first matching item
            const selectedBoothId = boothSelect ? boothSelect.value : null;
            let item = null;
            
            if (selectedBoothId) {
                item = lastLimits.find(i => i.badge_type === selectedType && i.booth_id == selectedBoothId);
            }
            
            if (!item) {
                item = lastLimits.find(i => i.badge_type === selectedType);
            }
            
            if (!item) {
                badgePriceInfo.textContent = '';
                return;
            }

            const remainingFree = item.remaining;
            const price = item.price || 0;
            const boothLabel = item.booth_name ? `${item.booth_name} - ${item.booth_size_label || ''}` : (item.booth_size_label ? ` (${item.booth_size_label})` : '');

            if (price > 0) {
                if (remainingFree > 0) {
                    badgePriceInfo.textContent = `Free quota remaining: ${remainingFree}${boothLabel ? ' for ' + boothLabel : ''}. After that, additional ${selectedType} badges will be charged at ₹${price.toFixed(2)} each.`;
                } else {
                    badgePriceInfo.textContent = `This ${selectedType} badge${boothLabel ? ' for ' + boothLabel : ''} will be charged at ₹${price.toFixed(2)} as configured by the organiser (additional badge beyond free quota).`;
                }
            } else {
                badgePriceInfo.textContent = `All ${selectedType} badges${boothLabel ? ' for ' + boothLabel : ''} are free (no additional charge configured).`;
            }
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
                updateValidDateConstraints();
                loadBoothsForBooking(null);
                return;
            }

            // Load booths first
            loadBoothsForBooking(bookingId);
            
            // Load badge limits (will reload when booth is selected if multiple booths)
            loadBadgeLimits(bookingId);
            
            // Always update date constraints based on the selected booking
            updateValidDateConstraints();
        });

        function loadBadgeLimits(bookingId, boothId = null) {
            if (!bookingId) {
                clearBadgeLimits();
                return;
            }

            let url = bookingLimitsUrlTemplate.replace(':bookingId', bookingId);
            if (boothId) {
                url += '?booth_id=' + boothId;
            }

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
                    lastLimits = data.data;

                    // Group by booth if booth-specific data
                    const groupedByBooth = {};
                    data.data.forEach(item => {
                        const boothKey = item.booth_id ? `booth_${item.booth_id}` : (item.booth_size_id ? `size_${item.booth_size_id}` : 'no_booth');
                        if (!groupedByBooth[boothKey]) {
                            groupedByBooth[boothKey] = {
                                boothLabel: item.booth_name ? `${item.booth_name} - ${item.booth_size_label || ''}` : (item.booth_size_label || 'General'),
                                items: []
                            };
                        }
                        groupedByBooth[boothKey].items.push(item);
                    });

                    // Display grouped by booth
                    Object.keys(groupedByBooth).forEach(boothKey => {
                        const group = groupedByBooth[boothKey];
                        const boothHeader = document.createElement('li');
                        boothHeader.innerHTML = `<strong>${group.boothLabel}:</strong>`;
                        boothHeader.style.marginTop = boothKey !== Object.keys(groupedByBooth)[0] ? '10px' : '0';
                        badgeLimitsList.appendChild(boothHeader);
                        
                        group.items.forEach(item => {
                            const li = document.createElement('li');
                            li.style.marginLeft = '20px';
                            const price = item.price || 0;
                            const priceText = price > 0
                                ? ` | Additional Price: ₹${price.toFixed(2)} each beyond free quota`
                                : '';
                            li.textContent = `${item.badge_type}: Allowed ${item.allowed}, Used ${item.used}, Remaining ${item.remaining}${priceText}`;
                            badgeLimitsList.appendChild(li);
                        });
                    });

                    badgeLimitsEmpty.classList.add('d-none');
                    badgeLimitsBox.classList.remove('d-none');

                    updateBadgeTypeOptions(data.data);
                    updateBadgePriceInfo();
                })
                .catch(() => {
                    // On error, just clear and hide limits
                    clearBadgeLimits();
                });
        }

        bookingSelect.addEventListener('change', function () {
            const bookingId = this.value;

            if (!bookingId) {
                clearBadgeLimits();
                updateValidDateConstraints();
                loadBoothsForBooking(null);
                return;
            }

            // Load booths first
            loadBoothsForBooking(bookingId);
            
            // Load badge limits (will reload when booth is selected if multiple booths)
            loadBadgeLimits(bookingId);
            
            // Always update date constraints based on the selected booking
            updateValidDateConstraints();
        });

        // When booth selection changes, reload badge limits if needed
        if (boothSelect) {
            boothSelect.addEventListener('change', function () {
                const bookingId = bookingSelect.value;
                if (bookingId && this.value) {
                    // Reload badge limits with selected booth
                    loadBadgeLimits(bookingId, this.value);
                } else if (bookingId) {
                    // If booth selection cleared, reload all limits
                    loadBadgeLimits(bookingId);
                }
            });
        }

        // Load booths if a booking is already selected on page load
        if (bookingSelect && bookingSelect.value) {
            loadBoothsForBooking(bookingSelect.value);
            loadBadgeLimits(bookingSelect.value);
        }

        // When badge type changes, refresh price info
        if (badgeTypeSelect) {
            badgeTypeSelect.addEventListener('change', updateBadgePriceInfo);
        }

        // Dynamic valid date rows
        if (addValidDateBtn && validDatesContainer) {
            addValidDateBtn.addEventListener('click', function () {
                const firstRow = validDatesContainer.querySelector('.valid-date-row');
                if (!firstRow) {
                    return;
                }

                const newRow = firstRow.cloneNode(true);
                const input = newRow.querySelector('input[type="date"]');
                const removeBtn = newRow.querySelector('.remove-date');

                if (input) {
                    input.value = '';
                }

                if (removeBtn) {
                    removeBtn.classList.remove('d-none');
                    removeBtn.addEventListener('click', function () {
                        newRow.remove();
                    });
                }

                validDatesContainer.appendChild(newRow);
                // Apply current booking date constraints to the new row
                updateValidDateConstraints();
            });

            // Enable removal for dynamically added rows
            validDatesContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-date')) {
                    const row = e.target.closest('.valid-date-row');
                    if (row && validDatesContainer.querySelectorAll('.valid-date-row').length > 1) {
                        row.remove();
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection

