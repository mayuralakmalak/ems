@extends('layouts.admin')

@section('title', 'Booking Management')
@section('page-title', 'Booking Management')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>All Bookings</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Exhibition</label>
                <select name="exhibition_id" class="form-select">
                    <option value="">All Exhibitions</option>
                    @foreach($exhibitions as $exhibition)
                        <option value="{{ $exhibition->id }}" {{ (string) $exhibition->id === request('exhibition_id') ? 'selected' : '' }}>
                            {{ $exhibition->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                @php
                    $currentStatus = request('status', 'all');
                @endphp
                <select name="status" class="form-select">
                    <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>All</option>
                    @foreach($availableStatuses as $status)
                        <option value="{{ $status }}" {{ $currentStatus === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User (Name or Email)</label>
                <input type="text" name="user_name" class="form-control" value="{{ request('user_name') }}" placeholder="Search by user">
            </div>
            <div class="col-md-2">
                <label class="form-label">Booth Number</label>
                <input type="text" name="booth_number" class="form-control" value="{{ request('booth_number') }}" placeholder="Search by booth no.">
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-end gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                    Reset
                </a>
                <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                    Filter
                </button>
                @can('Booking Management - Download')
                <button type="submit" name="export" value="1" class="btn btn-success btn-sm text-nowrap">
                    Export
                </button>
                @endcan
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Exhibition</th>
                        <th>Exhibitor</th>
                        <th>Booth</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->booking_number }}</strong></td>
                        <td>{{ $booking->exhibition->name ?? '-' }}</td>
                        <td>{{ $booking->user->name ?? '-' }}<br><small class="text-muted">{{ $booking->user->email ?? '' }}</small></td>
                        <td>
                            @php
                                // Get all booths from selected_booth_ids (for multiple booth bookings)
                                $boothEntries = collect($booking->selected_booth_ids ?? []);
                                if ($boothEntries->isEmpty() && $booking->booth_id) {
                                    // Fallback to primary booth if no selected_booth_ids
                                    $boothEntries = collect([[
                                        'id' => $booking->booth_id,
                                        'name' => $booking->booth->name ?? 'N/A',
                                    ]]);
                                }
                                
                                // Extract booth IDs and names
                                $boothIds = $boothEntries->map(function($entry) {
                                    return is_array($entry) ? ($entry['id'] ?? null) : $entry;
                                })->filter()->values();
                                
                                // Load booth models for names
                                $booths = \App\Models\Booth::whereIn('id', $boothIds)->get()->keyBy('id');
                                
                                // Build booth names list
                                $boothNames = $boothEntries->map(function($entry) use ($booths) {
                                    $isArray = is_array($entry);
                                    $id = $isArray ? ($entry['id'] ?? null) : $entry;
                                    $model = $id ? ($booths[$id] ?? null) : null;
                                    return $isArray ? ($entry['name'] ?? $model?->name ?? 'N/A') : ($model?->name ?? 'N/A');
                                })->filter(fn($name) => $name !== 'N/A');
                            @endphp
                            
                            @if($boothNames->count() > 0)
                                @if($boothNames->count() === 1)
                                    {{ $boothNames->first() }}
                                @else
                                    <span class="badge bg-info me-1">{{ $boothNames->count() }} Booths</span>
                                    <small class="text-muted d-block mt-1">
                                        {{ $boothNames->implode(', ') }}
                                    </small>
                                @endif
                            @else
                                {{ $booking->booth->name ?? '-' }}
                            @endif
                        </td>
                        <td>₹{{ number_format($booking->total_amount, 0) }}</td>
                        <td>₹{{ number_format($booking->paid_amount, 0) }}</td>
                        <td>
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td>
                            @can('Booking Management - View')
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

