@extends('layouts.exhibitor')

@section('title', 'Document Categories')
@section('page-title', 'Document Categories')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Documents
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Active Document Categories</h5>
        <small class="text-muted">These are the available categories for your documents</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Order</th>
                        <th>Documents Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ $category->order }}</td>
                        <td>{{ $category->documents()->count() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            No active document categories available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
