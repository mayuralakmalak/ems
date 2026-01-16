@extends('layouts.admin')

@section('title', 'Exhibition Management')
@section('page-title', 'Exhibition Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <a href="{{ route('admin.exhibitions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add
    </a>
</div>
<div class="row">
    <!-- Exhibition List Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Exhibition List</h5>
                <span class="text-muted small">{{ $exhibitions->count() }} total</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Exhibition Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Venue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exhibitions as $exhibition)
                            <tr>
                                <td>
                                    <strong>{{ $exhibition->name }}</strong>
                                </td>
                                <td>{{ $exhibition->start_date->format('Y-m-d') }}</td>
                                <td>{{ $exhibition->end_date->format('Y-m-d') }}</td>
                                <td>{{ $exhibition->venue }}</td>
                                <td>
                                    <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('admin.exhibitions.show', $exhibition->id) }}" class="btn btn-sm btn-info me-1" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exhibitions.bookings', $exhibition->id) }}" class="btn btn-sm btn-success me-1" title="View Booked Booths">
                                        <i class="bi bi-grid"></i>
                                    </a>
                                    <a href="{{ route('admin.checklists.index', ['exhibition_id' => $exhibition->id]) }}" class="btn btn-sm btn-warning me-1" title="Checklist">
                                        <i class="bi bi-list-check"></i>
                                    </a>
                                    @if($exhibition->end_date && $exhibition->end_date->isPast())
                                        <a href="{{ route('admin.reports.index', ['exhibition_id' => $exhibition->id]) }}" class="btn btn-sm btn-secondary me-1" title="View Report">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.exhibitions.destroy', $exhibition->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No exhibitions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($exhibitions->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $exhibitions->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

