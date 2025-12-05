@extends('layouts.frontend')

@section('title', 'My Badges')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1">My Badges</h1>
            <p class="text-muted mb-0">View badges generated for your exhibitions</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($badges->isEmpty())
                <p class="text-muted mb-0">No badges generated yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Exhibition</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($badges as $badge)
                            <tr>
                                <td>{{ $badge->name }}</td>
                                <td>{{ $badge->badge_type }}</td>
                                <td>{{ ucfirst($badge->status) }}</td>
                                <td>{{ $badge->exhibition->name ?? '-' }}</td>
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


