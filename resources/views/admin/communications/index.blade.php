@extends('layouts.admin')

@section('title', 'Community Center')
@section('page-title', 'Community Center')

@push('styles')
<style>
    .communication-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 120px);
        min-height: 700px;
    }
    .left-panel {
        width: 250px;
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .center-panel {
        flex: 1;
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }
    .right-panel {
        width: 400px;
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }

    .nav-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }
    .nav-tab {
        padding: 10px 20px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        bottom: -2px;
    }
    .nav-tab.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }

    .btn-compose {
        width: 100%;
        padding: 12px;
        background: #6366f1;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        margin-bottom: 25px;
        cursor: pointer;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .folder-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .folder-item {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }
    .folder-item:hover {
        background: #f8fafc;
    }
    .folder-item.active {
        background: #f0f9ff;
        color: #6366f1;
    }
    .folder-count {
        background: #6366f1;
        color: #ffffff;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.8rem;
    }

    .message-list {
        flex: 1;
        overflow-y: auto;
    }
    .message-item {
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .message-item:hover {
        background: #f8fafc;
    }
    .message-item.unread {
        background: #f0f9ff;
    }
    .message-avatar {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.2rem;
    }
    .message-content {
        flex: 1;
        min-width: 0;
    }
    .message-sender {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
        font-size: 1rem;
    }
    .message-subject {
        color: #64748b;
        font-size: 0.95rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .message-time {
        color: #94a3b8;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    .unread-dot {
        width: 8px;
        height: 8px;
        background: #6366f1;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .conversation-header {
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .conversation-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .conversation-participants {
        color: #64748b;
        font-size: 0.9rem;
    }

    .conversation-messages {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    .message-bubble {
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
    }
    .message-bubble.admin-message {
        align-items: flex-end;
    }
    .message-bubble.exhibitor-message {
        align-items: flex-start;
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 6px;
        padding: 0 4px;
    }
    .message-text {
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        line-height: 1.5;
        max-width: 75%;
        word-wrap: break-word;
    }
    .message-bubble.admin-message .message-text {
        background: #6366f1;
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }
    .message-bubble.exhibitor-message .message-text {
        background: #f3f4f6;
        color: #1f2937;
        border-bottom-left-radius: 4px;
    }

    .reply-box {
        border-top: 2px solid #e2e8f0;
        padding-top: 15px;
    }
    .reply-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        margin-bottom: 10px;
        resize: none;
        min-height: 60px;
        max-height: 100px;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    .reply-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn-attach {
        padding: 8px 16px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .btn-send {
        padding: 8px 20px;
        background: #6366f1;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
    }

    .exhibitor-item {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .exhibitor-item:hover {
        background-color: #f8fafc;
        transform: translateX(5px);
    }
    .list-group-item {
        border: 1px solid #e2e8f0;
        margin-bottom: 5px;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="communication-container">
    <!-- Left Panel -->
    <div class="left-panel">
        <h4 class="mb-3">Communication Center</h4>

        <div class="nav-tabs">
            <button class="nav-tab active">Inbox</button>
        </div>

        <button type="button" class="btn-compose" onclick="openComposeModal()">
            <i class="bi bi-plus-circle me-2"></i>+ Compose New Message
        </button>

        <ul class="folder-list">
            <li class="folder-item {{ $folder === 'inbox' ? 'active' : '' }}" onclick="switchFolder('inbox')">
                <span>Inbox</span>
                <span class="folder-count">{{ $messages->where('receiver_id', auth()->id())->where('is_read', false)->where('status', '!=', 'archived')->count() }}</span>
            </li>
            <li class="folder-item {{ $folder === 'sent' ? 'active' : '' }}" onclick="switchFolder('sent')">
                <span>Sent</span>
                <span>{{ $messages->where('sender_id', auth()->id())->where('status', '!=', 'archived')->where('status', '!=', 'deleted')->count() }}</span>
            </li>
            <li class="folder-item {{ $folder === 'archived' ? 'active' : '' }}" onclick="switchFolder('archived')">
                <span>Archived</span>
                <span>{{ $messages->where('status', 'archived')->count() }}</span>
            </li>
            <li class="folder-item {{ $folder === 'deleted' ? 'active' : '' }}" onclick="switchFolder('deleted')">
                <span>Deleted</span>
                <span>{{ $messages->where('status', 'deleted')->count() }}</span>
            </li>
        </ul>
    </div>

    <!-- Center Panel -->
    <div class="center-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>
                @if($folder === 'sent')
                    Sent Messages
                @elseif($folder === 'archived')
                    Archived Messages
                @elseif($folder === 'deleted')
                    Deleted Messages
                @else
                    Inbox ({{ $messages->where('receiver_id', auth()->id())->where('is_read', false)->where('status', '!=', 'archived')->where('status', '!=', 'deleted')->count() }} Unread)
                @endif
            </h5>
            <div>
                @if($folder === 'archived')
                    <button class="btn btn-sm btn-outline-warning me-2" onclick="unarchiveSelected()">Unarchive</button>
                @elseif($folder === 'deleted')
                    <button class="btn btn-sm btn-outline-danger me-2" onclick="deleteSelected()">Permanently Delete</button>
                @else
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="markAsReadSelected()">Mark as Read</button>
                    <button class="btn btn-sm btn-outline-danger me-2" onclick="deleteSelected()">Delete</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="archiveSelected()">Archive</button>
                @endif
            </div>
        </div>

        <div class="message-list">
            @forelse($threads as $thread)
                @php
                    $lastMessage = $thread['last_message'];
                    $otherUser = $thread['other_user'];
                    $unreadCount = $thread['unread_count'];
                @endphp
                <div class="message-item {{ $unreadCount > 0 ? 'unread' : '' }}" onclick="loadMessage({{ $lastMessage->id }})">
                    <input type="checkbox" class="message-checkbox" value="{{ $lastMessage->id }}" onclick="event.stopPropagation()">
                    <div class="message-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-sender">
                            {{ $otherUser->name ?? 'Exhibitor' }}
                        </div>
                        <div class="message-subject">{{ Str::limit($lastMessage->message, 50) }}</div>
                    </div>
                    <div class="message-time">{{ $lastMessage->created_at->format('M d, Y') }}</div>
                    @if($unreadCount > 0 && $folder === 'inbox')
                        <div class="unread-dot"></div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <p>No messages found</p>
                </div>
            @endforelse
        </div>

        <div class="mt-auto pt-3 border-top">
            <small class="text-muted">Showing {{ $threads->count() }} conversation(s)</small>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="right-panel" id="messageDetail">
        <div class="text-center py-5 text-muted">
            <i class="bi bi-chat-left" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">Select a message to view</p>
        </div>
    </div>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Exhibitor to Start New Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="exhibitorSearch" placeholder="Search exhibitors..." onkeyup="filterExhibitors()">
                </div>
                <div id="exhibitorList" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadMessage(messageId) {
    fetch(`{{ url('/admin/communications') }}/${messageId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('messageDetail').innerHTML = html;

            // Attach AJAX submit handler for reply form in the loaded conversation
            const container = document.getElementById('messageDetail');
            const form = container.querySelector('form.conversation-reply-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const messageText = (formData.get('message') || '').toString().trim();
                    if (!messageText) {
                        alert('Please enter a message');
                        return;
                    }

                    fetch(form.action, {
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
                        return { success: true, message_id: messageId };
                    })
                    .then(data => {
                        const newId = (data && typeof data.message_id !== 'undefined')
                            ? data.message_id
                            : messageId;
                        // Reload this conversation in the right panel
                        loadMessage(newId);
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        // As a fallback, reload the whole page
                        window.location.reload();
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading message:', error);
        });
}

function openComposeModal() {
    const modal = new bootstrap.Modal(document.getElementById('composeModal'));
    modal.show();
    
    // Load exhibitors list - use dedicated endpoint
    fetch('{{ route("admin.communications.exhibitors-list") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, try to parse as text first
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Response is not valid JSON');
                }
            });
        }
    })
    .then(data => {
        const listDiv = document.getElementById('exhibitorList');
        if (data && data.exhibitors && data.exhibitors.length > 0) {
            let html = '<div class="list-group">';
            data.exhibitors.forEach(exhibitor => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action exhibitor-item" data-id="${exhibitor.id}" data-name="${exhibitor.name.toLowerCase()}" data-email="${exhibitor.email.toLowerCase()}" data-company="${(exhibitor.company_name || '').toLowerCase()}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${exhibitor.name}</h6>
                        </div>
                        <p class="mb-1 text-muted">${exhibitor.email}</p>
                        <small class="text-muted">${exhibitor.company_name || 'No company'}</small>
                    </a>
                `;
            });
            html += '</div>';
            listDiv.innerHTML = html;
            
            // Add click handlers
            document.querySelectorAll('.exhibitor-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const exhibitorId = this.getAttribute('data-id');
                    openNewChat(exhibitorId);
                    modal.hide();
                });
            });
        } else {
            listDiv.innerHTML = '<div class="text-center py-3 text-muted">No exhibitors found</div>';
        }
    })
    .catch(error => {
        console.error('Error loading exhibitors:', error);
        document.getElementById('exhibitorList').innerHTML = '<div class="text-center py-3 text-danger">Failed to load exhibitors. Please try again.</div>';
    });
}

function filterExhibitors() {
    const searchTerm = document.getElementById('exhibitorSearch').value.toLowerCase();
    document.querySelectorAll('.exhibitor-item').forEach(item => {
        const name = item.getAttribute('data-name');
        const email = item.getAttribute('data-email');
        const company = item.getAttribute('data-company');
        
        if (name.includes(searchTerm) || email.includes(searchTerm) || company.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function openNewChat(exhibitorId) {
    fetch(`{{ url('/admin/communications/new-chat') }}/${exhibitorId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('messageDetail').innerHTML = html;
            // Scroll to the message detail panel
            document.getElementById('messageDetail').scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(error => {
            console.error('Error opening new chat:', error);
            alert('Failed to open new chat. Please try again.');
        });
}

function switchFolder(folder) {
    window.location.href = `{{ route('admin.communications.index') }}?folder=${folder}`;
}

function getSelectedMessageIds() {
    const checkboxes = document.querySelectorAll('.message-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function markAsReadSelected() {
    const messageIds = getSelectedMessageIds();
    if (messageIds.length === 0) {
        alert('Please select at least one message.');
        return;
    }

    fetch('{{ route("admin.communications.mark-as-read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message_ids: messageIds })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return { success: true };
        }
    })
    .then(data => {
        if (data && data.success) {
            window.location.reload();
        } else {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.reload();
    });
}

function deleteSelected() {
    const messageIds = getSelectedMessageIds();
    if (messageIds.length === 0) {
        alert('Please select at least one message.');
        return;
    }

    if (!confirm('Are you sure you want to delete the selected conversation(s)? This action cannot be undone.')) {
        return;
    }

    fetch('{{ route("admin.communications.delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message_ids: messageIds })
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        // Try to parse as JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, assume success and reload
            return { success: true };
        }
    })
    .then(data => {
        if (data && data.success) {
            window.location.reload();
        } else {
            // Even if response doesn't have success flag, reload to check
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Even on error, reload to check if deletion actually happened
        window.location.reload();
    });
}

function archiveSelected() {
    const messageIds = getSelectedMessageIds();
    if (messageIds.length === 0) {
        alert('Please select at least one conversation.');
        return;
    }

    fetch('{{ route("admin.communications.archive") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message_ids: messageIds })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return { success: true };
        }
    })
    .then(data => {
        if (data && data.success) {
            window.location.reload();
        } else {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.reload();
    });
}

function unarchiveSelected() {
    const messageIds = getSelectedMessageIds();
    if (messageIds.length === 0) {
        alert('Please select at least one conversation.');
        return;
    }

    fetch('{{ route("admin.communications.unarchive") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message_ids: messageIds })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return { success: true };
        }
    })
    .then(data => {
        if (data && data.success) {
            window.location.reload();
        } else {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.reload();
    });
}
</script>
@endpush
@endsection
