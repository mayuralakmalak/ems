<div class="conversation-header">
    <div class="conversation-title">
        New Message to {{ $exhibitor->name }}
    </div>
    <div class="conversation-participants">
        Admin &bull; {{ $exhibitor->email }}
    </div>
</div>

<div class="conversation-messages">
    <div class="text-center py-5 text-muted">
        <i class="bi bi-chat-left" style="font-size: 2rem; opacity: 0.3;"></i>
        <p class="mt-3">Start a new conversation</p>
    </div>
</div>

<div class="reply-box">
    <form id="newMessageForm" onsubmit="return false;">
        @csrf
        <input type="hidden" name="exhibitor_id" value="{{ $exhibitor->id }}">
        <input type="hidden" name="is_new_chat" value="1">
        <div class="reply-input-wrapper">
            <textarea name="message" class="reply-input" rows="2" placeholder="Type your message here..." required></textarea>
            <button type="button" class="btn-send" id="sendMessageBtn">
                <i class="bi bi-send"></i>
            </button>
        </div>
    </form>
</div>

<script>
// Initialize immediately when script loads (for dynamically loaded content)
(function() {
    console.log('New chat form script loaded');
    
    const form = document.getElementById('newMessageForm');
    const sendBtn = document.getElementById('sendMessageBtn');
    
    if (!form) {
        console.error('Form not found');
        return;
    }
    
    if (!sendBtn) {
        console.error('Send button not found');
        return;
    }
    
    console.log('Form and button found, attaching handler');
    
    // Remove any existing listeners by removing and re-adding the event
    const newHandler = function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('Send button clicked');
        
        const formData = new FormData(form);
        const messageText = formData.get('message');
        
        console.log('Message text:', messageText);
        
        if (!messageText || !messageText.trim()) {
            alert('Please enter a message');
            return false;
        }
        
        // Disable form while sending
        const originalIcon = sendBtn.querySelector('i');
        const originalClass = originalIcon.className;
        sendBtn.disabled = true;
        originalIcon.className = 'bi bi-hourglass-split';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token');
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Missing');
        
        fetch('{{ route("admin.communications.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            }
            return response.text().then(text => {
                console.log('Non-JSON response:', text);
                return { success: true, html: text };
            });
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                // Clear the input
                form.querySelector('textarea').value = '';
                
                // Load the conversation view with the new message
                if (data.message_id) {
                    console.log('Loading conversation for message ID:', data.message_id);
                    // Wait a moment for the message to be saved, then load the conversation
                    setTimeout(() => {
                        // Try to use loadMessage function if available (from index page)
                        if (typeof loadMessage === 'function') {
                            console.log('Using loadMessage function');
                            loadMessage(data.message_id);
                        } else {
                            console.log('loadMessage not available, fetching directly');
                            // If loadMessage is not available, fetch the conversation directly
                            fetch(`{{ url('/admin/communications') }}/${data.message_id}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            })
                                .then(response => response.text())
                                .then(html => {
                                    const messageDetail = document.getElementById('messageDetail');
                                    if (messageDetail) {
                                        messageDetail.innerHTML = html;
                                        
                                        // Execute any scripts in the loaded HTML
                                        const scripts = messageDetail.querySelectorAll('script');
                                        scripts.forEach(oldScript => {
                                            const newScript = document.createElement('script');
                                            Array.from(oldScript.attributes).forEach(attr => {
                                                newScript.setAttribute(attr.name, attr.value);
                                            });
                                            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                                            oldScript.parentNode.replaceChild(newScript, oldScript);
                                        });
                                    }
                                })
                                .catch(err => {
                                    console.error('Error loading conversation:', err);
                                    alert('Message sent but failed to load conversation. Please refresh the page.');
                                });
                        }
                    }, 300);
                } else {
                    console.warn('No message_id in response');
                    alert('Message sent but message ID not returned. Please refresh to see the conversation.');
                }
                
                // Re-enable form (though it will be replaced by conversation view)
                sendBtn.disabled = false;
                originalIcon.className = originalClass;
            } else {
                console.error('Response indicates failure:', data);
                alert(data.message || 'Failed to send message');
                sendBtn.disabled = false;
                originalIcon.className = originalClass;
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message: ' + error.message);
            sendBtn.disabled = false;
            originalIcon.className = originalClass;
        });
        
        return false;
    };
    
    // Remove old listener if exists and add new one
    sendBtn.removeEventListener('click', newHandler);
    sendBtn.addEventListener('click', newHandler, true);
    
    console.log('Event handler attached successfully');
})();
</script>
