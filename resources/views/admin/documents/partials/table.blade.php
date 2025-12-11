<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th width="50">
                    <input type="checkbox" id="selectAll">
                </th>
                <th>Exhibitor</th>
                <th>User</th>
                <th>Document</th>
                <th>Type</th>
                <th>Status</th>
                <th>Uploaded</th>
                <th width="50"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
            <tr onclick="showDocumentDetails({{ $document->id }})">
                <td>
                    <input type="checkbox" class="document-checkbox" value="{{ $document->id }}" onclick="event.stopPropagation()">
                </td>
                <td>{{ $document->user->company_name ?? $document->user->name }}</td>
                <td>{{ $document->user->name ?? '-' }}</td>
                <td>
                    <a href="#" class="document-link" onclick="event.stopPropagation(); showDocumentDetails({{ $document->id }}); return false;">
                        {{ $document->name }}
                    </a>
                </td>
                <td>{{ $document->type }}</td>
                <td>
                    <span class="status-badge {{ $document->status === 'approved' ? 'status-approved' : ($document->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                        {{ ucfirst($document->status ?? 'pending') }}
                    </span>
                </td>
                <td>{{ $document->created_at->format('Y-m-d h:i A') }}</td>
                <td>
                    <i class="bi bi-chevron-right"></i>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">No documents found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
