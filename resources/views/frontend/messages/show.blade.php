<div class="conversation-header">
    <div class="conversation-title">
        Conversation with {{ $otherUser->name ?? 'Admin' }}
    </div>
    <div class="conversation-participants">
        You &bull; {{ $otherUser->email ?? '' }}
    </div>
</div>

<div class="conversation-messages">
    @foreach($conversation as $msg)
    @php
        $isUser = $msg->sender_id === auth()->id();
    @endphp
    <div class="message-bubble {{ $isUser ? 'user-message' : 'admin-message' }}">
        <div class="message-header">
            <span class="message-author">
                {{ $isUser ? 'You' : ($otherUser->name ?? 'Admin') }}
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
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
        <input type="hidden" name="exhibition_id" value="">
        <textarea name="message" class="reply-input" rows="3" placeholder="Reply to message..."></textarea>
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
