@extends('layouts.admin')

@section('title', 'Manage Booths')
@section('page-title', 'Manage Booths - ' . $exhibition->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.exhibitions.show', $exhibition->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Exhibition
    </a>
    <a href="{{ route('admin.booths.create', $exhibition->id) }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Create New Booth
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Booths for {{ $exhibition->name }}</h5>
    </div>
    <div class="card-body">
        @if($booths->isEmpty())
            <p class="text-muted text-center">No booths created yet. <a href="{{ route('admin.booths.create', $exhibition->id) }}">Create your first booth</a></p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Booth Name</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Size (sq meter)</th>
                            <th>Sides Open</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booths as $booth)
                        <tr>
                            <td><strong>{{ $booth->name }}</strong></td>
                            <td>{{ $booth->category }}</td>
                            <td>{{ $booth->booth_type }}</td>
                            <td>{{ $booth->size_sqft }}</td>
                            <td>{{ $booth->sides_open }} Side{{ $booth->sides_open > 1 ? 's' : '' }}</td>
                            <td>
                                @if($booth->is_free)
                                    <span class="badge bg-info">Free</span>
                                @else
                                    ₹{{ number_format($booth->price, 0) }}
                                @endif
                            </td>
                            <td>
                                @if($booth->is_booked)
                                    <span class="badge bg-danger">Booked</span>
                                @elseif($booth->is_available)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-secondary">Unavailable</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.booths.show', [$exhibition->id, $booth->id]) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.booths.edit', [$exhibition->id, $booth->id]) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(!$booth->is_booked)
                                <form action="{{ route('admin.booths.destroy', [$exhibition->id, $booth->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this booth?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

