<div class="conversation-header">
    <div class="conversation-title">
        New Message to {{ $superAdmin->name ?? 'Super Admin' }}
    </div>
    <div class="conversation-participants">
        You &bull; {{ $superAdmin->email ?? '' }}
    </div>
</div>

<div class="conversation-messages">
    <div class="text-center py-5 text-muted">
        <i class="bi bi-chat-left" style="font-size: 2rem; opacity: 0.3;"></i>
        <p class="mt-3">Start a new conversation</p>
    </div>
</div>

<div class="reply-box">
    <form action="{{ route('messages.store') }}" method="POST" id="newMessageForm">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $superAdmin->id }}">
        <input type="hidden" name="exhibition_id" value="">
        <input type="hidden" name="is_new_chat" value="1">
        <textarea name="message" class="reply-input" rows="2" placeholder="Type your message here..." required></textarea>
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
    const form = document.getElementById('newMessageForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageText = formData.get('message');
            
            if (!messageText.trim()) {
                alert('Please enter a message');
                return;
            }
            
            fetch('{{ route("messages.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                }
            })
            .then(response => {
                if (response.headers.get('content-type')?.includes('application/json')) {
                    return response.json();
                }
                return response.text().then(text => ({ success: true, html: text }));
            })
            .then(data => {
                if (data.success) {
                    // Reload the page to show the new message in the list
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback to form submission
                this.submit();
            });
        });
    }
})();
</script>
