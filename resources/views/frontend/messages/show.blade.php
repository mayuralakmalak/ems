<div class="conversation-header">
    <div class="conversation-title">
        Conversation with {{ $otherUser->name ?? 'Admin' }}
    </div>
    <div class="conversation-participants">
        You &bull; {{ $otherUser->email ?? '' }}
    </div>
</div>

<div class="conversation-messages" id="conversationMessages" data-thread-id="{{ $threadId ?? ($conversation->first()->thread_id ?? '') }}" data-last-message-id="{{ $conversation->max('id') ?? 0 }}">
    @foreach($conversation as $msg)
    @php
        $isUser = $msg->sender_id === auth()->id();
    @endphp
    <div class="message-bubble {{ $isUser ? 'user-message' : 'admin-message' }}" data-message-id="{{ $msg->id }}">
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
    <form action="{{ route('messages.store') }}" method="POST" class="conversation-reply-form" id="replyForm">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
        <input type="hidden" name="exhibition_id" value="">
        <input type="hidden" name="thread_id" value="{{ $conversation->first()->thread_id ?? '' }}">
        <textarea name="message" class="reply-input" rows="2" placeholder="Reply to message..." id="messageInput"></textarea>
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

<script>
(function() {
    const conversationMessages = document.getElementById('conversationMessages');
    const replyForm = document.getElementById('replyForm');
    const messageInput = document.getElementById('messageInput');
    
    if (!conversationMessages || !replyForm) return;
    
    const threadId = conversationMessages.getAttribute('data-thread-id');
    let lastMessageId = parseInt(conversationMessages.getAttribute('data-last-message-id')) || 0;
    let pollingInterval = null;
    let isPolling = false;
    
    // Don't start polling if threadId is missing or empty
    if (!threadId || threadId.trim() === '') {
        console.warn('Thread ID is missing, real-time updates disabled');
    }
    
    // Function to scroll to bottom
    function scrollToBottom() {
        conversationMessages.scrollTop = conversationMessages.scrollHeight;
    }
    
    // Function to add message to UI
    function addMessageToUI(message, isUser) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-bubble ${isUser ? 'user-message' : 'admin-message'}`;
        messageDiv.setAttribute('data-message-id', message.id);
        messageDiv.innerHTML = `
            <div class="message-header">
                <span class="message-author">${message.sender_name}</span>
                <span class="message-date">${message.created_at}</span>
            </div>
            <div class="message-text">${escapeHtml(message.message)}</div>
        `;
        conversationMessages.appendChild(messageDiv);
        scrollToBottom();
        lastMessageId = Math.max(lastMessageId, message.id);
        conversationMessages.setAttribute('data-last-message-id', lastMessageId);
    }
    
    // Function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Function to fetch new messages
    function fetchNewMessages() {
        if (isPolling || !threadId) return;
        
        isPolling = true;
        const url = `{{ url('/messages/thread') }}/${threadId}/new-messages?last_message_id=${lastMessageId}`;
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(message => {
                    // Check if message already exists
                    if (!document.querySelector(`[data-message-id="${message.id}"]`)) {
                        addMessageToUI(message, message.is_user);
                    }
                });
                if (data.last_message_id) {
                    lastMessageId = data.last_message_id;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching new messages:', error);
        })
        .finally(() => {
            isPolling = false;
        });
    }
    
    // Start polling for new messages every 2 seconds
    function startPolling() {
        if (pollingInterval) return;
        pollingInterval = setInterval(fetchNewMessages, 2000);
    }
    
    // Stop polling
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }
    
    // Handle form submission
    replyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(replyForm);
        const messageText = formData.get('message');
        
        if (!messageText || !messageText.trim()) {
            alert('Please enter a message');
            return;
        }
        
        // Disable form while sending
        const submitBtn = replyForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
        
        // Immediately add message to UI (optimistic update)
        const tempMessage = {
            id: 'temp-' + Date.now(),
            message: messageText,
            created_at: new Date().toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true }),
            sender_name: 'You',
            is_user: true
        };
        addMessageToUI(tempMessage, true);
        messageInput.value = '';
        
        // Send message
        fetch(replyForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json();
            }
            return { success: true };
        })
        .then(data => {
            if (data.success) {
                // Remove temp message and fetch latest (which will include the real message)
                const tempMsg = document.querySelector('[data-message-id^="temp-"]');
                if (tempMsg) {
                    tempMsg.remove();
                }
                // Fetch new messages immediately to get the real message
                setTimeout(fetchNewMessages, 500);
            } else {
                // Remove temp message on error
                const tempMsg = document.querySelector('[data-message-id^="temp-"]');
                if (tempMsg) {
                    tempMsg.remove();
                }
                alert(data.message || 'Failed to send message');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            // Remove temp message on error
            const tempMsg = document.querySelector('[data-message-id^="temp-"]');
            if (tempMsg) {
                tempMsg.remove();
            }
            alert('Failed to send message. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Start polling when page loads (only if threadId exists)
    if (threadId && threadId.trim() !== '') {
        startPolling();
    }
    
    // Stop polling when page is hidden (tab switch)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });
    
    // Initial scroll to bottom
    scrollToBottom();
})();
</script>
