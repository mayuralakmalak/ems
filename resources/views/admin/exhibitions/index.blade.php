@extends('layouts.admin')

@section('title', 'Exhibition Management')
@section('page-title', 'Manage Exhibitions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-calendar-event me-2"></i>All Exhibitions</h2>
        <p class="text-muted mb-0">Manage and configure all your exhibition events</p>
    </div>
    <a href="{{ route('admin.exhibitions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-2"></i>Create New Exhibition
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Exhibition List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Exhibition Name</th>
                        <th>Venue & Location</th>
                        <th>Event Dates</th>
                        <th>Status</th>
                        <th>Booths</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exhibitions as $exhibition)
                    <tr>
                        <td>
                            <div>
                                <strong class="d-block">{{ $exhibition->name }}</strong>
                                <small class="text-muted">{{ Str::limit($exhibition->description, 50) }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-geo-alt text-primary me-1"></i>{{ $exhibition->venue }}
                                <br>
                                <small class="text-muted">{{ $exhibition->city }}, {{ $exhibition->country }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-calendar3 text-info me-1"></i>{{ $exhibition->start_date->format('d M Y') }}
                                <br>
                                <small class="text-muted">to {{ $exhibition->end_date->format('d M Y') }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $exhibition->status === 'active' ? 'success' : ($exhibition->status === 'completed' ? 'info' : ($exhibition->status === 'draft' ? 'secondary' : 'danger')) }} px-3 py-2">
                                <i class="bi bi-{{ $exhibition->status === 'active' ? 'check-circle' : ($exhibition->status === 'completed' ? 'check-all' : 'circle') }} me-1"></i>
                                {{ ucfirst($exhibition->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $exhibition->booths->count() }} Booths
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.exhibitions.show', $exhibition->id) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="btn btn-sm btn-warning" title="Edit Exhibition">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.exhibitions.destroy', $exhibition->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this exhibition?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Exhibition">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No exhibitions found</p>
                                <a href="{{ route('admin.exhibitions.create') }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>Create Your First Exhibition
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exhibitions->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $exhibitions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

