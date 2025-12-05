@extends('layouts.exhibitor')

@section('title', 'Communication Center page')
@section('page-title', 'Communication Center page')

@push('styles')
<style>
    .communication-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 200px);
    }
    
    .left-panel {
        width: 250px;
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .center-panel {
        flex: 1;
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }
    
    .right-panel {
        width: 400px;
        background: white;
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
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        margin-bottom: 25px;
        cursor: pointer;
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
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
    }
    
    .message-list {
        flex: 1;
        overflow-y: auto;
    }
    
    .message-item {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .message-item:hover {
        background: #f8fafc;
    }
    
    .message-item.unread {
        background: #f0f9ff;
    }
    
    .message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .message-content {
        flex: 1;
        min-width: 0;
    }
    
    .message-sender {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }
    
    .message-subject {
        color: #64748b;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .message-time {
        color: #94a3b8;
        font-size: 0.85rem;
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
        margin-bottom: 20px;
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .message-author {
        font-weight: 600;
        color: #1e293b;
    }
    
    .message-date {
        color: #94a3b8;
        font-size: 0.85rem;
    }
    
    .message-text {
        color: #64748b;
        line-height: 1.6;
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
    }
    
    .reply-box {
        border-top: 2px solid #e2e8f0;
        padding-top: 20px;
    }
    
    .reply-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        margin-bottom: 10px;
        resize: none;
    }
    
    .reply-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .btn-attach {
        padding: 8px 16px;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        cursor: pointer;
    }
    
    .btn-send {
        padding: 8px 20px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
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
            <button class="nav-tab">Notifications</button>
            <button class="nav-tab">Support Tickets</button>
        </div>
        
        <a href="{{ route('messages.create') }}" class="btn-compose">
            <i class="bi bi-plus-circle me-2"></i>+ Compose New Message
        </a>
        
        <ul class="folder-list">
            <li class="folder-item active">
                <span>Inbox</span>
                <span class="folder-count">{{ $messages->where('receiver_id', auth()->id())->where('is_read', false)->count() }}</span>
            </li>
            <li class="folder-item">
                <span>Sent</span>
                <span>{{ $messages->where('sender_id', auth()->id())->count() }}</span>
            </li>
            <li class="folder-item">
                <span>Archived</span>
                <span>{{ $messages->where('status', 'archived')->count() }}</span>
            </li>
        </ul>
    </div>
    
    <!-- Center Panel -->
    <div class="center-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Inbox ({{ $messages->where('receiver_id', auth()->id())->where('is_read', false)->count() }} Unread)</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary me-2">Mark as Read</button>
                <button class="btn btn-sm btn-outline-danger">Delete</button>
            </div>
        </div>
        
        <div class="message-list">
            @forelse($messages as $message)
            <div class="message-item {{ $message->receiver_id === auth()->id() && !$message->is_read ? 'unread' : '' }}" 
                 onclick="loadMessage({{ $message->id }})">
                <input type="checkbox" onclick="event.stopPropagation()">
                <div class="message-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div class="message-content">
                    <div class="message-sender">
                        {{ $message->sender_id === auth()->id() ? 'You' : ($message->sender->name ?? 'Admin') }}
                    </div>
                    <div class="message-subject">{{ Str::limit($message->message, 50) }}</div>
                </div>
                <div class="message-time">{{ $message->created_at->format('M d, Y') }}</div>
                @if($message->receiver_id === auth()->id() && !$message->is_read)
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
            <small class="text-muted">Showing 1-{{ $messages->count() }} of {{ $messages->count() }} messages</small>
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

@push('scripts')
<script>
function loadMessage(messageId) {
    fetch(`/ems-laravel/public/messages/${messageId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('messageDetail').innerHTML = html;
        });
}
</script>
@endpush
@endsection
