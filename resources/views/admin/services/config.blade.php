@extends('layouts.admin')

@section('title', 'Service Configuration')
@section('page-title', 'Service Configuration')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Service Management</h5>
        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-circle me-2"></i>Add Service
        </button>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="exhibition_id" class="form-select">
                        <option value="">All Exhibitions</option>
                        @foreach($exhibitions as $exhibition)
                        <option value="{{ $exhibition->id }}" {{ request('exhibition_id') == $exhibition->id ? 'selected' : '' }}>{{ $exhibition->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Service Name</th>
                        <th>Category</th>
                        <th>Exhibition</th>
                        <th>Price</th>
                        <th>Price Unit</th>
                        <th>Available From</th>
                        <th>Available To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $service->id }}" class="service-checkbox"></td>
                        <td><strong>{{ $service->name }}</strong></td>
                        <td>{{ $service->category }}</td>
                        <td>{{ $service->exhibition->name ?? '-' }}</td>
                        <td>₹{{ number_format($service->price, 2) }}</td>
                        <td>{{ $service->price_unit }}</td>
                        <td>{{ $service->available_from ? $service->available_from->format('d M Y') : '-' }}</td>
                        <td>{{ $service->available_to ? $service->available_to->format('d M Y') : '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-warning" onclick="editService({{ $service->id }}); return false;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.services.config.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No services found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <div>
                <select id="bulkAction" class="form-select d-inline-block" style="width: auto;">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="button" class="btn btn-primary ms-2" onclick="applyBulkAction()">Apply</button>
            </div>
            <div>
                {{ $services->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.services.config.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exhibition <span class="text-danger">*</span></label>
                            <select name="exhibition_id" class="form-select" required>
                                @foreach($exhibitions as $exhibition)
                                <option value="{{ $exhibition->id }}">{{ $exhibition->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" name="type" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" step="0.01" min="0" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price Unit <span class="text-danger">*</span></label>
                            <input type="text" name="price_unit" class="form-control" value="per person" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Available From</label>
                            <input type="date" name="available_from" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Available To</label>
                            <input type="date" name="available_to" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const checked = document.querySelectorAll('.service-checkbox:checked');
    if (!action || checked.length === 0) {
        alert('Please select an action and at least one service.');
        return;
    }
    const ids = Array.from(checked).map(cb => cb.value);
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.services.config.bulk-action") }}';
    form.innerHTML = '@csrf<input type="hidden" name="action" value="' + action + '">' +
        ids.map(id => '<input type="hidden" name="ids[]" value="' + id + '">').join('');
    document.body.appendChild(form);
    form.submit();
}
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.service-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
