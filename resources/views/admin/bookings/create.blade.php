@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="h3">Book Booth for {{ $exhibition->name }}</h1>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.exhibitions.bookings.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label d-block">Exhibitor</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="exhibitor_mode" id="exhibitor_mode_existing" value="existing" {{ old('exhibitor_mode', 'existing') === 'existing' ? 'checked' : '' }}>
                        <label class="form-check-label" for="exhibitor_mode_existing">Existing</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="exhibitor_mode" id="exhibitor_mode_new" value="new" {{ old('exhibitor_mode') === 'new' ? 'checked' : '' }}>
                        <label class="form-check-label" for="exhibitor_mode_new">New (not registered)</label>
                    </div>
                </div>

                <div id="existing-exhibitor-section" class="mb-3">
                    <label for="user_id" class="form-label">Select Existing Exhibitor</label>
                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
                        <option value="">Select Exhibitor</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="new-exhibitor-section" class="border rounded p-3 mb-3" style="display: none;">
                    <h5 class="mb-3">New Exhibitor Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_exhibitor_name" class="form-label">Full Name</label>
                            <input type="text" name="new_exhibitor_name" id="new_exhibitor_name" value="{{ old('new_exhibitor_name') }}" class="form-control @error('new_exhibitor_name') is-invalid @enderror">
                            @error('new_exhibitor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_exhibitor_email" class="form-label">Email</label>
                            <input type="email" name="new_exhibitor_email" id="new_exhibitor_email" value="{{ old('new_exhibitor_email') }}" class="form-control @error('new_exhibitor_email') is-invalid @enderror">
                            @error('new_exhibitor_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_exhibitor_phone" class="form-label">Phone</label>
                            <input type="text" name="new_exhibitor_phone" id="new_exhibitor_phone" value="{{ old('new_exhibitor_phone') }}" class="form-control @error('new_exhibitor_phone') is-invalid @enderror">
                            @error('new_exhibitor_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_exhibitor_company" class="form-label">Company Name</label>
                            <input type="text" name="new_exhibitor_company" id="new_exhibitor_company" value="{{ old('new_exhibitor_company') }}" class="form-control @error('new_exhibitor_company') is-invalid @enderror">
                            @error('new_exhibitor_company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_exhibitor_password" class="form-label">Password</label>
                            <input type="password" name="new_exhibitor_password" id="new_exhibitor_password" class="form-control @error('new_exhibitor_password') is-invalid @enderror">
                            @error('new_exhibitor_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="booth_id" class="form-label">Booth</label>
                    <select name="booth_id" id="booth_id" class="form-control @error('booth_id') is-invalid @enderror">
                        <option value="">Select Booth</option>
                        @foreach($booths as $booth)
                            <option value="{{ $booth->id }}"
                                @if(old('booth_id'))
                                    {{ old('booth_id') == $booth->id ? 'selected' : '' }}
                                @elseif(!empty($preselectedBoothId))
                                    {{ (int)$preselectedBoothId === (int)$booth->id ? 'selected' : '' }}
                                @endif
                                data-size-id="{{ $booth->exhibition_booth_size_id }}"
                                data-size-sqft="{{ $booth->size_sqft }}"
                            >
                                {{ $booth->name }} - {{ $booth->size_sqft }} sq ft ({{ $booth->category ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('booth_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="booth_type" class="form-label">Booth Type</label>
                    <select name="booth_type" id="booth_type" class="form-control @error('booth_type') is-invalid @enderror">
                        <option value="Raw" {{ old('booth_type', 'Raw') === 'Raw' ? 'selected' : '' }}>Raw</option>
                        <option value="Orphand" {{ old('booth_type') === 'Orphand' ? 'selected' : '' }}>Shell (Orphand)</option>
                    </select>
                    @error('booth_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="sides_open" class="form-label">Sides Open</label>
                    <select name="sides_open" id="sides_open" class="form-control @error('sides_open') is-invalid @enderror">
                        <option value="1" {{ old('sides_open', 1) == 1 ? 'selected' : '' }}>One Side Open</option>
                        <option value="2" {{ old('sides_open') == 2 ? 'selected' : '' }}>Two Sides Open</option>
                        <option value="3" {{ old('sides_open') == 3 ? 'selected' : '' }}>Three Sides Open</option>
                        <option value="4" {{ old('sides_open') == 4 ? 'selected' : '' }}>Four Sides Open</option>
                    </select>
                    @error('sides_open')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="contact_email" class="form-label">Contact Email (optional)</label>
                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" class="form-control @error('contact_email') is-invalid @enderror" placeholder="email@example.com">
                    @error('contact_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact Number (optional)</label>
                    <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" class="form-control @error('contact_number') is-invalid @enderror" placeholder="Phone number">
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Company Logo (optional)</label>
                    <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4" />
                <h5 class="mb-3">Additional Services (optional)</h5>
                @if(!empty($addonServiceOptions) && $addonServiceOptions->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th style="width:140px;">Unit Price</th>
                                    <th style="width:140px;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($addonServiceOptions as $idx => $row)
                                    @php
                                        /** @var \App\Models\Service $svc */
                                        $svc = $row['service'];
                                        $unit = (float) ($row['unit_price'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $svc->name }}</strong>
                                            <input type="hidden" name="services[{{ $idx }}][service_id]" value="{{ $svc->id }}">
                                            <input type="hidden" name="services[{{ $idx }}][unit_price]" value="{{ $unit }}">
                                            <input type="hidden" name="services[{{ $idx }}][name]" value="{{ $svc->name }}">
                                        </td>
                                        <td>₹{{ number_format($unit, 2) }}</td>
                                        <td>
                                            <input type="number" min="0" step="1" class="form-control form-control-sm"
                                                   name="services[{{ $idx }}][quantity]"
                                                   value="{{ old('services.' . $idx . '.quantity', 0) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No exhibition add-on services configured.</p>
                @endif

                <hr class="my-4" />
                <h5 class="mb-2">Included Items (extras) (optional)</h5>
                <p class="text-muted">Shown based on selected booth size. Add any extra items and quantity if needed.</p>
                <div id="included-items-container"></div>

                <hr class="my-4" />
                <h5 class="mb-3">Price Summary</h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr><th style="width:55%;">Booth</th><td id="summary_booth">—</td></tr>
                                <tr><th>Additional services</th><td id="summary_services">—</td></tr>
                                <tr><th>Included items (extras)</th><td id="summary_extras">—</td></tr>
                                <tr class="table-light"><th>Base total</th><td id="summary_base">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tbody>
                                <tr><th style="width:55%;">SQM discount</th><td id="summary_sqm">—</td></tr>
                                <tr><th>Member discount</th><td id="summary_member">—</td></tr>
                                <tr><th>Full payment discount</th><td id="summary_fullpay">—</td></tr>
                                <tr class="table-light"><th>Total discount</th><td id="summary_discount">—</td></tr>
                                <tr><th>Gateway charge (2.5%)</th><td id="summary_gateway">—</td></tr>
                                <tr class="table-light"><th>Grand total (after discount)</th><td id="summary_total">—</td></tr>
                                <tr class="table-success"><th>Payable now</th><td id="summary_payable">—</td></tr>
                            </tbody>
                        </table>
                        <div class="text-muted small" id="summary_note"></div>
                    </div>
                </div>

                <hr class="my-4" />
                <h5 class="mb-3">Payment Details</h5>
                <div class="mb-3">
                    <label class="form-label">Payment Coverage</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_coverage" id="payment_coverage_none" value="none" {{ old('payment_coverage', 'none') === 'none' ? 'checked' : '' }}>
                        <label class="form-check-label" for="payment_coverage_none">No payment received yet</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_coverage" id="payment_coverage_initial" value="initial" {{ old('payment_coverage') === 'initial' ? 'checked' : '' }}>
                        <label class="form-check-label" for="payment_coverage_initial">Initial payment received only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_coverage" id="payment_coverage_full" value="full" {{ old('payment_coverage') === 'full' ? 'checked' : '' }}>
                        <label class="form-check-label" for="payment_coverage_full">Full amount received</label>
                    </div>
                    @error('payment_coverage')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="payment-mode-wrapper">
                    <label for="payment_mode" class="form-label">Payment Mode</label>
                    <select name="payment_mode" id="payment_mode" class="form-control @error('payment_mode') is-invalid @enderror">
                        <option value="">Select payment mode</option>
                        <option value="upi" {{ old('payment_mode') === 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="credit_card" {{ old('payment_mode') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="net_banking" {{ old('payment_mode') === 'net_banking' ? 'selected' : '' }}>Net Banking</option>
                        <option value="online" {{ old('payment_mode') === 'online' ? 'selected' : '' }}>Other Online</option>
                        <option value="cash" {{ old('payment_mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="cheque" {{ old('payment_mode') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="bank_transfer" {{ old('payment_mode') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="other" {{ old('payment_mode') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('payment_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Create Booking</button>
                <a href="{{ route('admin.exhibitions.bookings', $exhibition->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    const boothSizeItems = @json($boothSizeItems ?? []);

    const oldIncludedItemExtras = @json(old('included_item_extras', []));

    const quoteUrl = @json(route('admin.exhibitions.bookings.quote', ['exhibition' => $exhibition->id]));
    const csrfToken = @json(csrf_token());

    function formatMoney(n) {
        const v = Number(n || 0);
        return '₹' + v.toFixed(2);
    }

    async function refreshQuote() {
        const boothId = document.getElementById('booth_id')?.value;
        const boothType = document.getElementById('booth_type')?.value || 'Raw';
        const sidesOpen = Number(document.getElementById('sides_open')?.value || 1);
        const paymentCoverage = document.querySelector('input[name="payment_coverage"]:checked')?.value || 'none';
        const exhibitorMode = document.querySelector('input[name="exhibitor_mode"]:checked')?.value || 'existing';
        const userId = document.getElementById('user_id')?.value || '';
        const paymentMode = document.getElementById('payment_mode')?.value || '';

        // Gather services payload
        const serviceRows = [];
        document.querySelectorAll('input[name^="services"][name$="[service_id]"]').forEach((el) => {
            const match = el.name.match(/services\[(\d+)\]\[service_id\]/);
            if (!match) return;
            const idx = match[1];
            const serviceId = Number(el.value || 0);
            const qty = Number(document.querySelector(`input[name="services[${idx}][quantity]"]`)?.value || 0);
            const unitPrice = Number(document.querySelector(`input[name="services[${idx}][unit_price]"]`)?.value || 0);
            const name = document.querySelector(`input[name="services[${idx}][name]"]`)?.value || '';
            serviceRows.push({ service_id: serviceId, quantity: qty, unit_price: unitPrice, name });
        });

        // Gather included extras payload
        const extrasRows = [];
        document.querySelectorAll('input[name^="included_item_extras"][name$="[item_id]"]').forEach((el) => {
            const match = el.name.match(/included_item_extras\[(\d+)\]\[item_id\]/);
            if (!match) return;
            const idx = match[1];
            const itemId = Number(el.value || 0);
            const qty = Number(document.querySelector(`input[name="included_item_extras[${idx}][quantity]"]`)?.value || 0);
            const unitPrice = Number(document.querySelector(`input[name="included_item_extras[${idx}][unit_price]"]`)?.value || 0);
            extrasRows.push({ item_id: itemId, quantity: qty, unit_price: unitPrice });
        });

        if (!boothId) {
            return;
        }

        const res = await fetch(quoteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                exhibitor_mode: exhibitorMode,
                user_id: userId || null,
                booth_id: boothId,
                booth_type: boothType,
                sides_open: sidesOpen,
                payment_coverage: paymentCoverage,
                payment_mode: paymentMode,
                services: serviceRows,
                included_item_extras: extrasRows,
            }),
        });

        const data = await res.json();
        if (!data || !data.ok) return;
        const q = data.quote;

        const useFull = paymentCoverage === 'full';
        const total = useFull ? q.total_full : q.total_part;
        const discountPercent = useFull ? q.discount_percent_full : q.discount_percent_part;

        document.getElementById('summary_booth').textContent = formatMoney(q.booth_price);
        document.getElementById('summary_services').textContent = formatMoney(q.services_total);
        document.getElementById('summary_extras').textContent = formatMoney(q.extras_total);
        document.getElementById('summary_base').textContent = formatMoney(q.base_total);

        document.getElementById('summary_sqm').textContent = (Number(q.sqm_discount_percent || 0).toFixed(2) + '%');
        document.getElementById('summary_member').textContent = (Number(q.member_discount_percent || 0).toFixed(2) + '%');
        document.getElementById('summary_fullpay').textContent = (Number(q.full_payment_discount_percent || 0).toFixed(2) + '%');
        document.getElementById('summary_discount').textContent = (Number(discountPercent || 0).toFixed(2) + '%');
        document.getElementById('summary_total').textContent = formatMoney(total);
        document.getElementById('summary_gateway').textContent = formatMoney(q.gateway_charge || 0);
        document.getElementById('summary_payable').textContent = formatMoney(q.payable_now || total);

        const note = document.getElementById('summary_note');
        if (note) {
            note.textContent = useFull
                ? 'Full payment selected: includes full-payment discount (capped by maximum discount settings) and, for online modes, a 2.5% gateway charge.'
                : 'Part/none selected: full-payment discount is not applied; for online modes, gateway is still 2.5% on the amount being paid now.';
        }
    }

    function toggleExhibitorSections() {
        const mode = document.querySelector('input[name="exhibitor_mode"]:checked')?.value || 'existing';
        const existingSection = document.getElementById('existing-exhibitor-section');
        const newSection = document.getElementById('new-exhibitor-section');
        if (mode === 'new') {
            if (existingSection) existingSection.style.display = 'none';
            if (newSection) newSection.style.display = 'block';
        } else {
            if (existingSection) existingSection.style.display = 'block';
            if (newSection) newSection.style.display = 'none';
        }
    }

    function togglePaymentModeVisibility() {
        const coverage = document.querySelector('input[name="payment_coverage"]:checked')?.value || 'none';
        const wrapper = document.getElementById('payment-mode-wrapper');
        if (!wrapper) return;
        wrapper.style.display = coverage === 'none' ? 'none' : 'block';
    }

    function renderIncludedItems() {
        const select = document.getElementById('booth_id');
        const container = document.getElementById('included-items-container');
        if (!select || !container) return;

        const opt = select.options[select.selectedIndex];
        const sizeId = opt?.getAttribute('data-size-id');
        const items = sizeId && boothSizeItems[sizeId] ? boothSizeItems[sizeId] : [];

        if (!items || items.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">No included items configured for this booth size.</p>';
            return;
        }

        // Build rows with stable indexes (0..n-1)
        let html = '<div class="table-responsive"><table class="table table-sm align-middle">';
        html += '<thead><tr><th>Item</th><th style="width:140px;">Unit Price</th><th style="width:140px;">Quantity</th></tr></thead><tbody>';
        items.forEach((it, idx) => {
            const oldQty = (oldIncludedItemExtras[idx] && oldIncludedItemExtras[idx].quantity) ? oldIncludedItemExtras[idx].quantity : 0;
            const oldPrice = (oldIncludedItemExtras[idx] && oldIncludedItemExtras[idx].unit_price) ? oldIncludedItemExtras[idx].unit_price : it.price;
            html += '<tr>';
            html += '<td><strong>' + (it.name || '') + '</strong>';
            html += '<input type="hidden" name="included_item_extras[' + idx + '][item_id]" value="' + it.id + '"></td>';
            html += '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm" name="included_item_extras[' + idx + '][unit_price]" value="' + oldPrice + '"></td>';
            html += '<td><input type="number" min="0" step="1" class="form-control form-control-sm" name="included_item_extras[' + idx + '][quantity]" value="' + oldQty + '"></td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;
        refreshQuote();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[name="exhibitor_mode"]').forEach(function (el) {
            el.addEventListener('change', toggleExhibitorSections);
        });
        document.querySelectorAll('input[name="payment_coverage"]').forEach(function (el) {
            el.addEventListener('change', togglePaymentModeVisibility);
        });
        const boothSelect = document.getElementById('booth_id');
        if (boothSelect) {
            boothSelect.addEventListener('change', renderIncludedItems);
        }
        const boothType = document.getElementById('booth_type');
        if (boothType) boothType.addEventListener('change', refreshQuote);
        const sidesOpenSelect = document.getElementById('sides_open');
        if (sidesOpenSelect) sidesOpenSelect.addEventListener('change', refreshQuote);
        const paymentModeSelect = document.getElementById('payment_mode');
        if (paymentModeSelect) paymentModeSelect.addEventListener('change', refreshQuote);
        document.querySelectorAll('input[name="payment_coverage"]').forEach(function (el) {
            el.addEventListener('change', function () {
                togglePaymentModeVisibility();
                refreshQuote();
            });
        });
        const userSelect = document.getElementById('user_id');
        if (userSelect) userSelect.addEventListener('change', refreshQuote);
        document.querySelectorAll('input[name="exhibitor_mode"]').forEach(function (el) {
            el.addEventListener('change', function () {
                toggleExhibitorSections();
                refreshQuote();
            });
        });

        // Refresh quote when quantities change
        document.addEventListener('input', function (e) {
            const name = e?.target?.getAttribute('name') || '';
            if (name.includes('services[') || name.includes('included_item_extras[')) {
                refreshQuote();
            }
        });
        toggleExhibitorSections();
        togglePaymentModeVisibility();
        renderIncludedItems();
        refreshQuote();
    });
</script>
@endpush
@endsection

