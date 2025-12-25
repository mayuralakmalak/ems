<div class="conversation-header">
    <div class="conversation-title">
        Conversation with {{ $otherUser->name ?? 'Exhibitor' }}
    </div>
    <div class="conversation-participants">
        Admin &bull; {{ $otherUser->email ?? '' }}
    </div>
</div>

<div class="conversation-messages" id="conversationMessages" data-thread-id="{{ $threadId ?? ($conversation->first()->thread_id ?? '') }}" data-last-message-id="{{ $conversation->max('id') ?? 0 }}">
    @foreach($conversation as $msg)
    @php
        $isAdmin = $msg->sender_id === auth()->id();
    @endphp
    <div class="message-bubble {{ $isAdmin ? 'admin-message' : 'exhibitor-message' }}" data-message-id="{{ $msg->id }}">
        <div class="message-header">
            <span class="message-author">
                {{ $isAdmin ? 'You' : ($otherUser->name ?? 'Exhibitor') }}
            </span>
            <span class="message-date">{{ $msg->created_at->setTimezone('Asia/Kolkata')->format('M d, Y, h:i A') }}</span>
        </div>
        <div class="message-text">
            {{ $msg->message }}
        </div>
    </div>
    @endforeach
</div>

<div class="reply-box">
    <form action="{{ route('admin.communications.store') }}" method="POST" class="conversation-reply-form" id="replyForm">
        @csrf
        <input type="hidden" name="exhibitor_id" value="{{ $otherUser->id }}">
        <input type="hidden" name="thread_id" value="{{ $conversation->first()->thread_id ?? '' }}">
        <div class="reply-input-wrapper">
            <textarea name="message" class="reply-input" rows="2" placeholder="Reply to {{ $otherUser->name ?? 'Exhibitor' }}..." required id="messageInput"></textarea>
            <button type="submit" class="btn-send">
                <i class="bi bi-send"></i>
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
    function addMessageToUI(message, isAdmin) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-bubble ${isAdmin ? 'admin-message' : 'exhibitor-message'}`;
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
        const url = `{{ url('/admin/communications/thread') }}/${threadId}/new-messages?last_message_id=${lastMessageId}`;
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
                        addMessageToUI(message, message.is_admin);
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
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        const formData = new FormData(replyForm);
        const messageText = formData.get('message');
        
        if (!messageText || !messageText.trim()) {
            alert('Please enter a message');
            return false;
        }
        
        // Disable form while sending
        const submitBtn = replyForm.querySelector('button[type="submit"]');
        const originalIcon = submitBtn.querySelector('i');
        const originalClass = originalIcon.className;
        submitBtn.disabled = true;
        originalIcon.className = 'bi bi-hourglass-split';
        
        // Immediately add message to UI (optimistic update)
        // Format date in Indian timezone
        const now = new Date();
        const indianTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Kolkata' }));
        const formattedDate = indianTime.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
        
        const tempMessage = {
            id: 'temp-' + Date.now(),
            message: messageText,
            created_at: formattedDate,
            sender_name: 'You',
            is_admin: true
        };
        addMessageToUI(tempMessage, true);
        messageInput.value = '';
        
        // Send message via AJAX
        fetch(replyForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
            },
            credentials: 'same-origin'
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            }
            // If not JSON, try to parse as text
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Remove temp message and fetch latest (which will include the real message)
                const tempMsg = document.querySelector('[data-message-id^="temp-"]');
                if (tempMsg) {
                    tempMsg.remove();
                }
                // Update thread_id if provided
                if (data.thread_id && threadId !== data.thread_id) {
                    conversationMessages.setAttribute('data-thread-id', data.thread_id);
                    if (threadId && threadId.trim() !== '') {
                        startPolling();
                    }
                }
                // Fetch new messages immediately to get the real message
                setTimeout(fetchNewMessages, 500);
                
                // Trigger inbox update to refresh the list
                if (typeof updateInboxList === 'function') {
                    setTimeout(updateInboxList, 1000);
                }
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
            originalIcon.className = originalClass;
        });
        
        return false;
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
