@extends("layouts.admin")

@section("title", "Checklist Management")
@section("page-title", "Checklist Management")

@section("content")
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Checklist Item</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.checklists.store') }}" method="POST">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>There were some problems:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Exhibition</label>
                        <div class="form-control" style="background:#f8f9fa;" readonly>
                            {{ $currentExhibition->name ?? 'All Exhibitions' }}
                        </div>
                        <input type="hidden" name="exhibition_id" value="{{ request('exhibition_id', $currentExhibition->id ?? null) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Type <span class="text-danger">*</span></label>
                        @php($types = ['textbox' => 'Textbox', 'textarea' => 'Textarea', 'file' => 'File', 'multiple_file' => 'Multiple File', 'checkbox' => 'Checkbox'])
                        <select name="item_type" class="form-select" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('item_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1">
                            <label class="form-check-label" for="is_required">Required</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date (Days Before Event)</label>
                        <input type="number" name="due_date_days_before" class="form-control" value="{{ old('due_date_days_before') }}" min="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="visible_to_user" id="visible_to_user" value="1" checked>
                            <label class="form-check-label" for="visible_to_user">Visible to User</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="visible_to_admin" id="visible_to_admin" value="1" checked>
                            <label class="form-check-label" for="visible_to_admin">Visible to Admin</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Add Item
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Existing Checklist Items</h5>
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
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
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
                                <th>Name</th>
                                <th>Exhibition</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Due Days</th>
                                <th>Visibility</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($checklistItems as $item)
                                <tr>
                                    <td><strong>{{ $item->name }}</strong></td>
                                    <td>{{ $item->exhibition->name ?? 'All' }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $item->item_type ?? 'textbox') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->is_required ? 'danger' : 'secondary' }}">
                                            {{ $item->is_required ? 'Required' : 'Optional' }}
                                        </span>
                                    </td>
                                    <td>{{ $item->due_date_days_before ?? '-' }}</td>
                                    <td>
                                        <small class="text-muted">
                                            @if($item->visible_to_user && $item->visible_to_admin)
                                                User & Admin
                                            @elseif($item->visible_to_user)
                                                User Only
                                            @elseif($item->visible_to_admin)
                                                Admin Only
                                            @else
                                                Hidden
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editChecklistModal"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"
                                            data-description="{{ $item->description }}"
                                            data-type="{{ $item->item_type }}"
                                            data-required="{{ $item->is_required }}"
                                            data-due="{{ $item->due_date_days_before }}"
                                            data-visible-user="{{ $item->visible_to_user }}"
                                            data-visible-admin="{{ $item->visible_to_admin }}"
                                            data-exhibition="{{ $item->exhibition_id }}"
                                            data-exhibition-name="{{ $item->exhibition->name ?? 'All Exhibitions' }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.checklists.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                    <td colspan="7" class="text-center">No checklist items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $checklistItems->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Checklist Modal -->
<div class="modal fade" id="editChecklistModal" tabindex="-1" aria-labelledby="editChecklistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editChecklistModalLabel">Edit Checklist Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editChecklistForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Exhibition</label>
                            <div class="form-control" id="edit_exhibition_name" style="background:#f8f9fa;" readonly></div>
                            <input type="hidden" name="exhibition_id" id="edit_exhibition_id" value="{{ request('exhibition_id', $currentExhibition->id ?? null) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Item Type <span class="text-danger">*</span></label>
                            <select name="item_type" class="form-select" required>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date (Days Before Event)</label>
                            <input type="number" name="due_date_days_before" class="form-control" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-center flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_required" id="edit_is_required" value="1">
                                <label class="form-check-label" for="edit_is_required">Required</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="visible_to_user" id="edit_visible_to_user" value="1">
                                <label class="form-check-label" for="edit_visible_to_user">Visible to User</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="visible_to_admin" id="edit_visible_to_admin" value="1">
                                <label class="form-check-label" for="edit_visible_to_admin">Visible to Admin</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const editModal = document.getElementById('editChecklistModal');
    editModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) return;

        const form = document.getElementById('editChecklistForm');
        const id = button.getAttribute('data-id');
        form.action = "{{ url('/admin/checklists') }}/" + id;

        form.querySelector("input[name='name']").value = button.getAttribute('data-name') || '';
        form.querySelector("textarea[name='description']").value = button.getAttribute('data-description') || '';
        form.querySelector("select[name='item_type']").value = button.getAttribute('data-type') || 'textbox';
        form.querySelector("input[name='due_date_days_before']").value = button.getAttribute('data-due') || '';
        form.querySelector("#edit_exhibition_id").value = button.getAttribute('data-exhibition') || '';
        const exhibitionName = button.getAttribute('data-exhibition-name') || 'All Exhibitions';
        document.getElementById('edit_exhibition_name').textContent = exhibitionName;

        form.querySelector('#edit_is_required').checked = button.getAttribute('data-required') === '1';
        form.querySelector('#edit_visible_to_user').checked = button.getAttribute('data-visible-user') === '1';
        form.querySelector('#edit_visible_to_admin').checked = button.getAttribute('data-visible-admin') === '1';
    });
</script>
@endpush
@endsection
