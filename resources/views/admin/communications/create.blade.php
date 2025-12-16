@extends('layouts.admin')

@section('title', 'Start New Chat')
@section('page-title', 'Start New Chat with Exhibitor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">New Chat</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.communications.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Exhibitor <span class="text-danger">*</span></label>
                        <select name="exhibitor_id" class="form-select" required>
                            <option value="">Choose exhibitor...</option>
                            @foreach($exhibitors as $exhibitor)
                                <option value="{{ $exhibitor->id }}" {{ old('exhibitor_id') == $exhibitor->id ? 'selected' : '' }}>
                                    {{ $exhibitor->name }} ({{ $exhibitor->company_name ?? 'No company' }})
                                </option>
                            @endforeach
                        </select>
                        @error('exhibitor_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Type your message..." required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.exhibitors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Start Chat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
