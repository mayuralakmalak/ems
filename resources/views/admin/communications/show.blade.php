<div class="conversation-header">
    <div class="conversation-title">
        Conversation with {{ $otherUser->name ?? 'Exhibitor' }}
    </div>
    <div class="conversation-participants">
        Admin &bull; {{ $otherUser->email ?? '' }}
    </div>
</div>

<div class="conversation-messages">
    @foreach($conversation as $msg)
    @php
        $isAdmin = $msg->sender_id === auth()->id();
    @endphp
    <div class="message-bubble {{ $isAdmin ? 'admin-message' : 'exhibitor-message' }}">
        <div class="message-header">
            <span class="message-author">
                {{ $isAdmin ? 'You' : ($otherUser->name ?? 'Exhibitor') }}
            </span>
            <span class="message-date">{{ $msg->created_at->format('M d, Y, h:i A') }}</span>
        </div>
        <div class="message-text">
            {{ $msg->message }}
        </div>
    </div>
    @endforeach
</div>

<div class="reply-box">
    <form action="{{ route('admin.communications.store') }}" method="POST" class="conversation-reply-form">
        @csrf
        <input type="hidden" name="exhibitor_id" value="{{ $otherUser->id }}">
        <textarea name="message" class="reply-input" rows="2" placeholder="Reply to {{ $otherUser->name ?? 'Exhibitor' }}..." required></textarea>
        <div class="reply-actions">
            <button type="button" class="btn-attach">
                <i class="bi bi-paperclip me-2"></i>Attach File
            </button>
            <button type="submit" class="btn-send">
                <i class="bi bi-send me-2"></i>Send
            </button>
        </div>
    </form>
</div>
