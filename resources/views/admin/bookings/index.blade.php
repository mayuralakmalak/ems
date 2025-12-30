@extends('layouts.admin')

@section('title', 'Booking Management')
@section('page-title', 'Booking Management')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>All Bookings</h5>
    </div>
    <div class="card-body">
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
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
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

