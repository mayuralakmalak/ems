@extends('layouts.exhibitor')

@section('title', 'Document Categories')
@section('page-title', 'Document Categories')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Documents
        </a>
        <a href="{{ route('document-categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Category
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Document Categories</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Documents Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>
                            <span class="badge {{ $category->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </td>
                        <td>{{ $category->order }}</td>
                        <td>{{ $category->documents()->count() }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('document-categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('document-categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No document categories found. <a href="{{ route('document-categories.create') }}">Create one</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
