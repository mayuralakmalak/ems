@extends('layouts.frontend')

@section('title', 'Messages')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1">Messages</h1>
            <p class="text-muted mb-0">Send messages to the admin team</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>New Message</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('messages.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Message</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Conversation</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($messages as $msg)
                        <div class="mb-3">
                            <small class="text-muted d-block">
                                {{ $msg->created_at->format('d M Y H:i') }} -
                                {{ $msg->sender_id === auth()->id() ? 'You' : 'Admin' }}
                            </small>
                            <div class="p-2 rounded {{ $msg->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                                {{ $msg->message }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No messages yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


