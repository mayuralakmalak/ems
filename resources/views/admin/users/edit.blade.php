@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User & Assign Roles')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>User Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">State</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="form-control">
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-key me-2"></i>Assign Roles</h5>
                    <div class="row">
                        @foreach($roles as $role)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $role->name }}"
                                    id="role_{{ $role->id }}"
                                    {{ $user->roles->contains('name', $role->name) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


