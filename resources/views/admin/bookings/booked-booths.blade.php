@extends('layouts.admin')

@section('title', 'Booked Booths')
@section('page-title', 'Booked Booths')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Exhibitions
    </a>
    <span class="text-muted small">{{ $bookings->total() }} total bookings</span>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Booked Booths</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking #</th>
                        <th>Exhibitor</th>
                        <th>Booth</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_number }}</td>
                        <td>
                            {{ $booking->user->name ?? '-' }}<br>
                            <small class="text-muted">{{ $booking->user->email ?? '' }}</small>
                        </td>
                        <td>{{ $booking->booth->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this booking? This will free the booth.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No booked booths found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
