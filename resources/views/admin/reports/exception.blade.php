@extends('layouts.admin')

@section('title', 'Exception Report')
@section('page-title', 'Exception Report')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Generate Exception Report</h5>
                <p class="text-muted mb-0 small">View all admin overrides made for selected clients. Report can only be generated after exhibition end date.</p>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="GET" action="{{ route('admin.reports.exception') }}" id="exceptionReportForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="exhibition_id" class="form-label">Select Exhibition <span class="text-danger">*</span></label>
                            <select name="exhibition_id" id="exhibition_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Select Exhibition --</option>
                                @foreach($exhibitions as $ex)
                                    <option value="{{ $ex->id }}" {{ $selectedExhibitionId == $ex->id ? 'selected' : '' }}>
                                        {{ $ex->name }} (Ended: {{ $ex->end_date->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only exhibitions that have ended are available</small>
                        </div>
                    </div>
                </form>

                @if($selectedExhibitionId)
                    @php
                        $exhibition = \App\Models\Exhibition::find($selectedExhibitionId);
                    @endphp
                    
                    @if($exhibition && $exhibition->end_date >= \Carbon\Carbon::now())
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This exhibition has not ended yet. Exception report can only be generated after the exhibition end date ({{ $exhibition->end_date->format('M d, Y') }}).
                        </div>
                    @elseif($exhibition)
                        <form method="POST" action="{{ route('admin.reports.exception.generate') }}" id="generateReportForm" class="mt-4">
                            @csrf
                            <input type="hidden" name="exhibition_id" value="{{ $selectedExhibitionId }}">
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Select Clients</label>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="selectAllClients" onchange="toggleAllClients(this)">
                                                <label class="form-check-label fw-bold" for="selectAllClients">
                                                    Select All Clients
                                                </label>
                                            </div>
                                            <hr class="my-2">
                                            @if($clients->isEmpty())
                                                <p class="text-muted mb-0">No clients found for this exhibition.</p>
                                            @else
                                                <div class="row g-2" style="max-height: 300px; overflow-y: auto;">
                                                    @foreach($clients as $client)
                                                        <div class="col-md-6 col-lg-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input client-checkbox" type="checkbox" 
                                                                       name="client_ids[]" 
                                                                       value="{{ $client->id }}" 
                                                                       id="client_{{ $client->id }}"
                                                                       {{ in_array($client->id, $selectedClientIds) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="client_{{ $client->id }}">
                                                                    {{ $client->name }} ({{ $client->company_name ?? 'N/A' }})
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        Bookings: {{ $client->bookings->count() }}
                                                                    </small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <small class="text-muted">Leave all unchecked to generate report for all clients</small>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="format" value="pdf" class="btn btn-primary">
                                            <i class="bi bi-file-earmark-pdf me-2"></i>Generate PDF Report
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                                            <i class="bi bi-printer me-2"></i>Print Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if($exceptions->isNotEmpty())
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Exception Report Preview</h5>
                                    <span class="badge bg-primary">{{ $exceptions->count() }} Exception(s) Found</span>
                                </div>

                                @php
                                    $groupedByClient = $exceptions->groupBy('user_id');
                                @endphp

                                @foreach($groupedByClient as $userId => $clientExceptions)
                                    @php
                                        $client = $clientExceptions->first()->user;
                                    @endphp
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="bi bi-person me-2"></i>
                                                {{ $client->name }}
                                                <small class="text-muted">({{ $client->company_name ?? 'N/A' }})</small>
                                                <span class="badge bg-secondary ms-2">{{ $clientExceptions->count() }} override(s)</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Type</th>
                                                            <th>Description</th>
                                                            <th>Old Value</th>
                                                            <th>New Value</th>
                                                            <th>Booking #</th>
                                                            <th>Created By</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($clientExceptions as $exception)
                                                            <tr>
                                                                <td>{{ $exception->created_at->format('M d, Y H:i') }}</td>
                                                                <td>
                                                                    <span class="badge bg-warning text-dark">
                                                                        {{ ucfirst(str_replace('_', ' ', $exception->exception_type)) }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $exception->description }}</td>
                                                                <td>
                                                                    @if($exception->old_value)
                                                                        @if(is_array($exception->old_value))
                                                                            <pre class="mb-0 small">{{ json_encode($exception->old_value, JSON_PRETTY_PRINT) }}</pre>
                                                                        @else
                                                                            {{ $exception->old_value }}
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($exception->new_value)
                                                                        @if(is_array($exception->new_value))
                                                                            <pre class="mb-0 small">{{ json_encode($exception->new_value, JSON_PRETTY_PRINT) }}</pre>
                                                                        @else
                                                                            {{ $exception->new_value }}
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($exception->booking)
                                                                        <a href="{{ route('admin.bookings.show', $exception->booking->id) }}" target="_blank">
                                                                            {{ $exception->booking->booking_number }}
                                                                        </a>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $exception->createdBy->name ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mt-4">
                                <i class="bi bi-info-circle me-2"></i>
                                No exceptions found for the selected criteria.
                            </div>
                        @endif
                    @endif
                @else
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Please select an exhibition to view exception reports.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleAllClients(checkbox) {
        const clientCheckboxes = document.querySelectorAll('.client-checkbox');
        clientCheckboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    // Update form when client selection changes
    document.querySelectorAll('.client-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const form = document.getElementById('exceptionReportForm');
            const exhibitionId = document.getElementById('exhibition_id').value;
            if (exhibitionId) {
                const checkedClients = Array.from(document.querySelectorAll('.client-checkbox:checked'))
                    .map(cb => cb.value);
                const url = new URL(form.action);
                url.searchParams.set('exhibition_id', exhibitionId);
                if (checkedClients.length > 0) {
                    checkedClients.forEach(id => url.searchParams.append('client_ids[]', id));
                }
                window.location.href = url.toString();
            }
        });
    });
</script>
@endpush

