@extends('layouts.admin')

@section('title', 'Edit Email Notification')
@section('page-title', 'Edit Email Notification')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Email Notification</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.emails.update', $notification->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Event Type</label>
                <input type="text" class="form-control" value="{{ str_replace('_', ' ', ucwords($notification->event_type)) }}" disabled>
                <small class="text-muted">Event type cannot be changed.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject Line <span class="text-danger">*</span></label>
                <input type="text" name="subject_line" class="form-control @error('subject_line') is-invalid @enderror" value="{{ old('subject_line', $notification->subject_line) }}" required>
                @error('subject_line')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email Body</label>
                <textarea name="email_body" class="form-control @error('email_body') is-invalid @enderror" rows="10">{{ old('email_body', $notification->email_body) }}</textarea>
                @error('email_body')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Recipients <span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recipients[]" value="Exhibitor Contact" id="recipient_exhibitor" {{ in_array('Exhibitor Contact', old('recipients', $notification->recipients ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="recipient_exhibitor">Exhibitor Contact</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recipients[]" value="Attendee" id="recipient_attendee" {{ in_array('Attendee', old('recipients', $notification->recipients ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="recipient_attendee">Attendee</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recipients[]" value="All Users" id="recipient_all" {{ in_array('All Users', old('recipients', $notification->recipients ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="recipient_all">All Users</label>
                </div>
                @error('recipients')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_enabled" id="is_enabled" {{ old('is_enabled', $notification->is_enabled) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_enabled">Enable this notification</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Update Notification
                </button>
                <a href="{{ route('admin.emails.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
