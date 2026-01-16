@extends('layouts.admin')

@section('title', 'Service Configuration')
@section('page-title', 'Service Configuration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="bi bi-plus-circle me-1"></i>Add
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Service Management</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Service Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $service->id }}" class="service-checkbox"></td>
                        <td><strong>{{ $service->name }}</strong></td>
                        <td>{{ Str::limit($service->description ?? '-', 50) }}</td>
                        <td>
                            <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary me-1" onclick="editService({{ $service->id }})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.services.config.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                        <td colspan="5" class="text-center">No services found.</td>
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

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetModal()"></button>
            </div>
            <form id="serviceForm" action="{{ route('admin.services.config.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="service_name" class="form-control" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="service_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" id="service_image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep existing image</small>
                            <div id="currentImage" class="mt-2" style="display: none;">
                                <img id="currentImagePreview" src="" alt="Current Image" style="max-width: 200px; max-height: 150px; border-radius: 8px;">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="service_is_active" checked>
                                <label class="form-check-label" for="service_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Create Service</button>
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

function resetModal() {
    const form = document.getElementById('serviceForm');
    const modal = document.getElementById('addServiceModal');
    
    // Reset form
    form.reset();
    form.action = '{{ route("admin.services.config.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('modalTitle').textContent = 'Add New Service';
    document.getElementById('submitBtn').textContent = 'Create Service';
    document.getElementById('service_is_active').checked = true;
    
    // Hide current image
    document.getElementById('currentImage').style.display = 'none';
    document.getElementById('currentImagePreview').src = '';
    
    // Clear file input
    document.getElementById('service_image').value = '';
}

function editService(id) {
    console.log('Editing service ID:', id);
    
    // Prevent modal reset
    window.isEditingService = true;
    
    // Fetch service data and populate modal
    const url = `{{ url('/admin/services/config') }}/${id}`;
    console.log('Fetching from URL:', url);
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('Service not found: ' + response.status);
            });
        }
        return response.json();
    })
    .then(service => {
        console.log('Service data received:', service);
        
        // Clear form first to ensure clean state
        document.getElementById('serviceForm').reset();
        
        // Populate form fields
        document.getElementById('service_name').value = service.name || '';
        document.getElementById('service_description').value = service.description || '';
        document.getElementById('service_is_active').checked = service.is_active === true || service.is_active === 1;
        
        // Show current image if exists
        if (service.image) {
            const imageUrl = `{{ asset('storage/') }}/${service.image}`;
            document.getElementById('currentImagePreview').src = imageUrl;
            document.getElementById('currentImage').style.display = 'block';
        } else {
            document.getElementById('currentImage').style.display = 'none';
        }
        
        // Change form action to update
        const form = document.getElementById('serviceForm');
        const updateUrl = `{{ url('/admin/services/config') }}/${id}`;
        form.action = updateUrl;
        document.getElementById('formMethod').value = 'PUT';
        
        // Update modal title and button
        document.getElementById('modalTitle').textContent = 'Edit Service';
        document.getElementById('submitBtn').textContent = 'Update Service';
        
        // Show modal AFTER populating form
        const modalElement = document.getElementById('addServiceModal');
        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modal.show();
        
        // Reset flag after a short delay
        setTimeout(() => {
            window.isEditingService = false;
        }, 500);
        
        console.log('Form populated successfully');
    })
    .catch(error => {
        console.error('Error loading service:', error);
        alert('Error loading service details: ' + error.message);
        window.isEditingService = false;
    });
}

// Reset modal when opening for new service (but not when editing)
document.getElementById('addServiceModal').addEventListener('show.bs.modal', function (event) {
    // Only reset if it's not an edit operation
    if (!window.isEditingService) {
        resetModal();
    }
});

// Reset flag when modal is hidden
document.getElementById('addServiceModal').addEventListener('hidden.bs.modal', function (event) {
    window.isEditingService = false;
});

document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.service-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
