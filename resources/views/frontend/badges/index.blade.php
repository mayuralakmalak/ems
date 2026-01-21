@extends('layouts.exhibitor')

@section('title', 'Badge Management')
@section('page-title', 'Badge Management')

@push('styles')
<style>
    .badge-management-container {
        display: flex;
        gap: 20px;
    }
    
    .left-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }

    .section-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .section-title-row .section-title {
        margin-bottom: 0;
    }

    .section-title-row .btn-add-badge {
        width: auto;
        margin-top: 0;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 1rem;
        width: 100%;
    }
    
    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }
    
    .radio-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .radio-option:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .radio-option.selected {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .radio-option input[type="radio"] {
        margin: 0;
    }
    
    .badge-assignment-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .badge-assignment-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .badge-assignment-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .badge-assignment-table tr:last-child td {
        border-bottom: none;
    }
    
    .action-icons {
        display: flex;
        gap: 8px;
    }
    
    .action-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-icon.download {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .action-icon.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-add-badge {
        width: 100%;
        padding: 12px 20px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        margin-top: 15px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: 0.4s;
        border-radius: 24px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #10b981;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    .btn-generate {
        width: 100%;
        padding: 12px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        margin-top: 15px;
        cursor: pointer;
    }
    
    .download-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-download {
        width: 100%;
        padding: 12px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .badge-preview {
        width: 100%;
        height: 400px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        position: relative;
    }
    
    .badge-preview-content {
        text-align: center;
    }
    
    .badge-id {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .qr-code-placeholder {
        width: 200px;
        height: 200px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    
    .detail-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .detail-item {
        margin-bottom: 12px;
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .tab {
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
    
    .tab.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
</style>
@endpush

@section('content')
<div class="badge-management-container">
    <!-- Left Panel -->
    <div class="left-panel">
        <!-- Badge Assignment -->
        <div class="section-card">
            <div class="section-title-row">
                <h5 class="section-title">Badge Assignment</h5>
                <a href="{{ route('badges.create') }}" class="btn-add-badge">
                    <i class="bi bi-plus-circle me-2"></i>Add Badge
                </a>
            </div>
            
            <table class="badge-assignment-table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Valid Date(s)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($badges as $badge)
                    <tr>
                        <td>{{ $badge->name }}</td>
                        <td>{{ $badge->badge_type }}</td>
                        <td>
                            @php
                                $datesToShow = [];
                                if (is_array($badge->valid_for_dates) && count($badge->valid_for_dates) > 0) {
                                    foreach ($badge->valid_for_dates as $d) {
                                        if ($d) {
                                            try {
                                                $datesToShow[] = \Carbon\Carbon::parse($d)->format('d M Y');
                                            } catch (\Exception $e) {
                                                $datesToShow[] = $d;
                                            }
                                        }
                                    }
                                } elseif ($badge->valid_for_date) {
                                    try {
                                        $datesToShow[] = $badge->valid_for_date->format('d M Y');
                                    } catch (\Exception $e) {
                                        $datesToShow[] = (string) $badge->valid_for_date;
                                    }
                                }
                            @endphp
                            {{ count($datesToShow) ? implode(', ', $datesToShow) : 'N/A' }}
                        </td>
                        <td>
                            <div class="action-icons">
                                <a href="{{ route('badges.download', $badge->id) }}" class="action-icon download" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form action="{{ route('badges.destroy', $badge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this badge?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-icon delete" title="Delete" style="border: none; background: none; padding: 0;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-3 text-muted">No badges assigned yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($badges->hasPages())
            <div class="mt-4">
                {{ $badges->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
