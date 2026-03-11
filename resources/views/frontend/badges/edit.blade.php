@extends('layouts.exhibitor')

@section('title', 'Edit Badge')
@section('page-title', 'Edit Badge')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Edit Badge</h5>
    </div>
    <div class="card-body">
        <div class="mb-4 p-3 border rounded bg-light">
            <div class="row">
                <div class="col-md-4">
                    <strong>Exhibition:</strong>
                    <div>{{ $badge->exhibition->name ?? 'N/A' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Badge Type:</strong>
                    <div>{{ $badge->badge_type }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Current Valid Date(s):</strong>
                    <div>
                        @php
                            $datesToShow = [];
                            if (is_array($badge->valid_for_dates) && count($badge->valid_for_dates) > 0) {
                                foreach ($badge->valid_for_dates as $d) {
                                    if ($d) {
                                        try {
                                            $datesToShow[] = \Carbon\Carbon::parse($d)->format('d M Y');
                                        } catch (\Exception $e) {
                                            $datesToShow[] = $d;
                                        }
                                    }
                                }
                            } elseif ($badge->valid_for_date) {
                                try {
                                    $datesToShow[] = $badge->valid_for_date->format('d M Y');
                                } catch (\Exception $e) {
                                    $datesToShow[] = (string) $badge->valid_for_date;
                                }
                            }
                        @endphp
                        {{ count($datesToShow) ? implode(', ', $datesToShow) : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <form id="badgeEditForm" action="{{ route('badges.update', $badge->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Valid For Date Range(s)</label>
                <div id="validDateRangesContainer">
                    <div class="row g-2 mb-2 valid-date-range-row">
                        <div class="col-md-12">
                            <input type="text" class="form-control valid-date-range-input" placeholder="Select date range">
                        </div>
                    </div>
                </div>
                <div id="expandedValidDatesContainer"></div>
                <small class="text-muted d-block mt-1">
                    Select one or more date ranges when this badge is valid. Each range will be expanded into individual dates for daily check-in/check-out.
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $badge->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $badge->email) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $badge->phone) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                @if($badge->photo)
                    <small class="text-muted d-block mt-1">A new upload will replace the existing photo.</small>
                @endif
            </div>

            @php
                $selectedPermissions = (array) old('access_permissions', $badge->access_permissions ?? []);
            @endphp

            <div class="mb-3">
                <label class="form-label">Access Permissions</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Entry Only" id="entry"
                        {{ in_array('Entry Only', $selectedPermissions) ? 'checked' : '' }}>
                    <label class="form-check-label" for="entry">Entry Only</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Lunch" id="lunch"
                        {{ in_array('Lunch', $selectedPermissions) ? 'checked' : '' }}>
                    <label class="form-check-label" for="lunch">Lunch</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Snacks" id="snacks"
                        {{ in_array('Snacks', $selectedPermissions) ? 'checked' : '' }}>
                    <label class="form-check-label" for="snacks">Snacks</label>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('badges.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Badge</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const badgeEditForm = document.getElementById('badgeEditForm');
        const validDateRangesContainer = document.getElementById('validDateRangesContainer');
        const addValidDateRangeBtn = document.getElementById('addValidDateRangeBtn');
        const expandedValidDatesContainer = document.getElementById('expandedValidDatesContainer');

        const startDate = '{{ optional($badge->exhibition->start_date)->format('Y-m-d') }}';
        const endDate = '{{ optional($badge->exhibition->end_date)->format('Y-m-d') }}';

        const existingValidDates = @json(
            old('valid_for_dates',
                is_array($badge->valid_for_dates) && count($badge->valid_for_dates)
                    ? $badge->valid_for_dates
                    : ($badge->valid_for_date ? [$badge->valid_for_date->format('Y-m-d')] : [])
            )
        );

        function initDateRangePicker(input) {
            if (!window.jQuery || !$.fn || !$.fn.daterangepicker || !input) {
                return;
            }

            const $input = $(input);
            const existing = $input.data('daterangepicker');
            const existingValue = $input.val();

            if (existing) {
                existing.remove();
            }

            $input.daterangepicker({
                autoUpdateInput: true,
                autoApply: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                minDate: startDate || false,
                maxDate: endDate || false
            });

            if (existingValue) {
                $input.val(existingValue);
            }

            $input.on('cancel.daterangepicker', function () {
                $(this).val('');
            });
        }

        function refreshAllDateRangePickers() {
            if (!validDateRangesContainer) {
                return;
            }
            const inputs = validDateRangesContainer.querySelectorAll('.valid-date-range-input');
            inputs.forEach(input => initDateRangePicker(input));
        }

        function groupDatesIntoRanges(dates) {
            if (!Array.isArray(dates) || dates.length === 0) {
                return [];
            }

            const sorted = dates
                .filter(Boolean)
                .map(d => new Date(d))
                .filter(d => !isNaN(d.getTime()))
                .sort((a, b) => a - b);

            if (!sorted.length) {
                return [];
            }

            const ranges = [];
            let rangeStart = sorted[0];
            let prev = sorted[0];

            for (let i = 1; i < sorted.length; i++) {
                const current = sorted[i];
                const nextDay = new Date(prev);
                nextDay.setDate(nextDay.getDate() + 1);

                if (current.toISOString().slice(0, 10) === nextDay.toISOString().slice(0, 10)) {
                    prev = current;
                } else {
                    ranges.push([rangeStart, prev]);
                    rangeStart = current;
                    prev = current;
                }
            }

            ranges.push([rangeStart, prev]);
            return ranges;
        }

        function formatDateForInput(date) {
            if (!(date instanceof Date) || isNaN(date.getTime())) {
                return '';
            }
            return date.toISOString().slice(0, 10);
        }

        function buildInitialRanges() {
            if (!validDateRangesContainer) {
                return;
            }

            const baseRow = validDateRangesContainer.querySelector('.valid-date-range-row');
            validDateRangesContainer.innerHTML = '';

            const ranges = groupDatesIntoRanges(existingValidDates || []);

            if (!ranges.length) {
                // Keep a single empty row
                validDateRangesContainer.appendChild(baseRow);
                refreshAllDateRangePickers();
                return;
            }

            ranges.forEach((range, index) => {
                const row = baseRow.cloneNode(true);
                const rangeInput = row.querySelector('.valid-date-range-input');
                const removeBtn = row.querySelector('.remove-date-range');

                if (rangeInput) {
                    const fromVal = formatDateForInput(range[0]);
                    const toVal = formatDateForInput(range[1]);
                    if (fromVal && toVal) {
                        rangeInput.value = `${fromVal} - ${toVal}`;
                    } else if (fromVal) {
                        rangeInput.value = fromVal;
                    }
                }

                if (removeBtn) {
                    if (index === 0 && ranges.length === 1) {
                        removeBtn.classList.add('d-none');
                    } else {
                        removeBtn.classList.remove('d-none');
                    }
                }

                validDateRangesContainer.appendChild(row);
            });

            refreshAllDateRangePickers();
        }

        // Dynamic valid date range rows
        if (addValidDateRangeBtn && validDateRangesContainer) {
            addValidDateRangeBtn.addEventListener('click', function () {
                const firstRow = validDateRangesContainer.querySelector('.valid-date-range-row');
                if (!firstRow) {
                    return;
                }

                const newRow = firstRow.cloneNode(true);
                const rangeInput = newRow.querySelector('.valid-date-range-input');
                const removeBtn = newRow.querySelector('.remove-date-range');

                if (rangeInput) {
                    rangeInput.value = '';
                }

                if (removeBtn) {
                    removeBtn.classList.remove('d-none');
                }

                validDateRangesContainer.appendChild(newRow);
                refreshAllDateRangePickers();
            });

            validDateRangesContainer.addEventListener('click', function (e) {
                const removeBtn = e.target.closest('.remove-date-range');
                if (removeBtn) {
                    const row = removeBtn.closest('.valid-date-range-row');
                    if (row && validDateRangesContainer.querySelectorAll('.valid-date-range-row').length > 1) {
                        row.remove();
                    }
                }
            });
        }

        // On submit, expand date ranges into individual valid_for_dates[]
        if (badgeEditForm && expandedValidDatesContainer && validDateRangesContainer) {
            badgeEditForm.addEventListener('submit', function (e) {
                expandedValidDatesContainer.innerHTML = '';

                const rows = validDateRangesContainer.querySelectorAll('.valid-date-range-row');
                const allDates = [];
                let hasError = false;

                rows.forEach(row => {
                    row.classList.remove('border', 'border-danger');

                    const rangeInput = row.querySelector('.valid-date-range-input');
                    const value = rangeInput ? rangeInput.value.trim() : '';

                    if (!value) {
                        return;
                    }

                    let fromVal = '';
                    let toVal = '';

                    const parts = value.split(' - ');
                    if (parts.length === 2) {
                        fromVal = parts[0];
                        toVal = parts[1];
                    } else {
                        fromVal = value;
                    }

                    if (fromVal && !toVal) {
                        allDates.push(fromVal);
                        return;
                    }
                    if (!fromVal && toVal) {
                        allDates.push(toVal);
                        return;
                    }

                    if (toVal < fromVal) {
                        hasError = true;
                        row.classList.add('border', 'border-danger');
                        return;
                    }

                    // Safely expand date range without timezone issues
                    const startParts = fromVal.split('-').map(Number);
                    const endParts = toVal.split('-').map(Number);

                    if (startParts.length !== 3 || endParts.length !== 3) {
                        hasError = true;
                        row.classList.add('border', 'border-danger');
                        return;
                    }

                    let current = new Date(startParts[0], startParts[1] - 1, startParts[2]);
                    const end = new Date(endParts[0], endParts[1] - 1, endParts[2]);

                    if (isNaN(current.getTime()) || isNaN(end.getTime())) {
                        hasError = true;
                        row.classList.add('border', 'border-danger');
                        return;
                    }

                    while (current <= end) {
                        const y = current.getFullYear();
                        const m = String(current.getMonth() + 1).padStart(2, '0');
                        const d = String(current.getDate()).padStart(2, '0');
                        const iso = `${y}-${m}-${d}`;
                        allDates.push(iso);
                        current.setDate(current.getDate() + 1);
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    alert('Please ensure each selected date range is valid.');
                    return;
                }

                const uniqueDates = Array.from(new Set(allDates));

                uniqueDates.forEach(dateStr => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'valid_for_dates[]';
                    hidden.value = dateStr;
                    expandedValidDatesContainer.appendChild(hidden);
                });
            });
        }

        buildInitialRanges();
    });
</script>
@endpush
@endsection

