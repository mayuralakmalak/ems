<div class="conversation-header">
    <div class="conversation-title">{{ Str::limit($message->message, 30) }}</div>
    <div class="conversation-participants">
        {{ $message->sender->name ?? 'Admin' }} -> You
    </div>
</div>

<div class="conversation-messages">
    <div class="message-bubble">
        <div class="message-header">
            <span class="message-author">{{ $message->sender->name ?? 'Admin' }}</span>
            <span class="message-date">{{ $message->created_at->format('M d, Y, h:i A') }}</span>
        </div>
        <div class="message-text">{{ $message->message }}</div>
    </div>
    
    @if($message->receiver_id === auth()->id())
    <div class="message-bubble">
        <div class="message-header">
            <span class="message-author">You</span>
            <span class="message-date">{{ now()->format('M d, Y, h:i A') }}</span>
        </div>
        <div class="message-text">Thanks for reaching out! I'm always keen to explore potential collaborations. Does either of those work for you? Looking forward to connecting.</div>
    </div>
    @endif
</div>

<div class="reply-box">
    <form action="{{ route('messages.store') }}" method="POST">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $message->sender_id }}">
        <input type="hidden" name="exhibition_id" value="{{ $message->exhibition_id }}">
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

