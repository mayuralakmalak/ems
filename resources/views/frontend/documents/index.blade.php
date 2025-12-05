@extends('layouts.exhibitor')

@section('title', 'My Documents')
@section('page-title', 'My Documents')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1">My Documents</h3>
            <p class="text-muted mb-0">Upload and manage documents for your bookings</p>
        </div>
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-2"></i>Upload Document
        </a>
    </div>
</div>

    <div class="card">
        <div class="card-body">
            @if($documents->isEmpty())
                <p class="text-muted mb-0">No documents uploaded yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                            <tr>
                                <td>{{ $document->name }}</td>
                                <td>{{ $document->type }}</td>
                                <td>{{ ucfirst($document->status) }}</td>
                                <td>{{ number_format(($document->file_size ?? 0) / 1024, 1) }} KB</td>
                                <td>
                                    <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>
                                    <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


