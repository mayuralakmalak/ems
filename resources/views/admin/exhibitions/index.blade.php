@extends('layouts.admin')

@section('title', 'Exhibition Management')
@section('page-title', 'Exhibition Management')

@section('content')
<div class="row">
    <!-- Exhibition List Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Exhibition List</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="showCreateForm()">
                    + Create New
                </button>
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
                                    <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="text-primary me-2" onclick="editExhibition({{ $exhibition->id }}); return false;">Edit</a>
                                    <form action="{{ route('admin.exhibitions.destroy', $exhibition->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0">Delete</button>
                                    </form>
                                    <a href="#" class="text-info ms-2" onclick="duplicateExhibition({{ $exhibition->id }}); return false;">Duplicate</a>
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
                    {{ $exhibitions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create/Edit Exhibition Form Section -->
    <div class="col-12">
        <div class="card" id="exhibitionFormCard">
            <div class="card-header">
                <h5 class="mb-0">Create/Edit Exhibition</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-3">Basic Information</h6>
                <form action="{{ route('admin.exhibitions.store') }}" method="POST" id="exhibitionForm">
                    @csrf
                    <input type="hidden" name="exhibition_id" id="exhibition_id" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exhibition Name</label>
                            <input type="text" name="name" id="exhibition_name" class="form-control" placeholder="Exhibition Name" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start Date and Time</label>
                            <div class="input-group">
                                <input type="datetime-local" name="start_datetime" id="start_datetime" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Duration Name</label>
                            <input type="text" name="duration_name" id="duration_name" class="form-control" placeholder="Duration Name">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End Date and Time</label>
                            <div class="input-group">
                                <input type="datetime-local" name="end_datetime" id="end_datetime" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Venue</label>
                            <input type="text" name="venue" id="venue" class="form-control" placeholder="Venue" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="location" class="form-control" placeholder="Location">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Rich text editor placeholder..."></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-dark">Save and Continue to Step 2</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showCreateForm() {
    document.getElementById('exhibitionForm').reset();
    document.getElementById('exhibition_id').value = '';
    document.getElementById('exhibitionFormCard').scrollIntoView({ behavior: 'smooth' });
}

function editExhibition(id) {
    // Load exhibition data and populate form
    fetch(`/admin/exhibitions/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('exhibition_id').value = data.id;
            document.getElementById('exhibition_name').value = data.name;
            document.getElementById('venue').value = data.venue;
            document.getElementById('location').value = data.city || '';
            document.getElementById('description').value = data.description || '';
            // Set datetime values
            const startDate = new Date(data.start_date + ' ' + (data.start_time || '00:00'));
            const endDate = new Date(data.end_date + ' ' + (data.end_time || '00:00'));
            document.getElementById('start_datetime').value = formatDateTimeLocal(startDate);
            document.getElementById('end_datetime').value = formatDateTimeLocal(endDate);
            
            // Change form action to update
            document.getElementById('exhibitionForm').action = `/admin/exhibitions/${id}`;
            document.getElementById('exhibitionForm').innerHTML += '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('exhibitionFormCard').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = `/admin/exhibitions/${id}/edit`;
        });
}

function duplicateExhibition(id) {
    if (confirm('Duplicate this exhibition?')) {
        // Implementation for duplication
        window.location.href = `/admin/exhibitions/${id}/duplicate`;
    }
}

function formatDateTimeLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Handle form submission
document.getElementById('exhibitionForm').addEventListener('submit', function(e) {
    const exhibitionId = document.getElementById('exhibition_id').value;
    if (exhibitionId) {
        // Update existing
        this.action = `/admin/exhibitions/${exhibitionId}`;
    } else {
        // Create new
        this.action = '{{ route("admin.exhibitions.store") }}';
    }
});
</script>
@endpush
@endsection

