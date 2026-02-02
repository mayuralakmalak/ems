@extends('layouts.admin')

@section('title', 'Add CMS Page')
@section('page-title', 'Add CMS Page')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.cms-pages.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to CMS Pages
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create CMS Page</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.cms-pages.store') }}" method="POST" id="cmsPageForm">
            @csrf

            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="auto-generated from title if empty">
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_in_footer" value="0">
                    <input class="form-check-input" type="checkbox" name="show_in_footer" id="show_in_footer" value="1" {{ old('show_in_footer', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_in_footer">Show link in footer</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_in_header" value="0">
                    <input class="form-check-input" type="checkbox" name="show_in_header" id="show_in_header" value="1" {{ old('show_in_header') ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_in_header">Show link in header</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.cms-pages.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Page</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.css" rel="stylesheet">
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && jQuery().summernote) {
        jQuery('#content').summernote({ height: 400, placeholder: 'Page content...' });
    }
});
</script>
@endpush
@endsection
