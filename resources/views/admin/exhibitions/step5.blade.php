@extends('layouts.admin')

@section('title', 'Admin - Exhibition booking step 5')
@section('page-title', 'Admin - Exhibition booking step 5')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            @if(isset($exhibition) && $exhibition->id)
                <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 1: Exhibition Details</a>
                <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</a>
                <a href="{{ route('admin.exhibitions.step3', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 3: Payment Schedule</a>
                <a href="{{ route('admin.exhibitions.step4', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 4: Badge & Manual</a>
                <span class="text-primary fw-bold" style="padding: 8px 16px; color: white; border-radius: 4px;">Step 5: Stall Comments</span>
            @else
                <small class="text-muted" style="padding: 8px 16px;">Step 1: Exhibition Details</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 3: Payment Schedule</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 4: Badge & Manual</small>
                <small class="text-primary fw-bold" style="padding: 8px 16px; color: white; border-radius: 4px;">Step 5: Stall Comments</small>
            @endif
        </div>
    </div>
</div>

<form action="{{ route('admin.exhibitions.step5.store', $exhibition->id) }}" method="POST">
    @csrf

    @php
        $floors = $exhibition->floors ?? collect();
        $unassignedBooths = ($exhibition->booths ?? collect())->whereNull('floor_id')->sortBy('name');
    @endphp

    @if($floors->isEmpty() && $unassignedBooths->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-0">No booths have been configured for this exhibition yet.</p>
            </div>
        </div>
    @else
        @foreach($floors as $floor)
            @php
                $booths = ($floor->booths ?? collect())->sortBy('name');
            @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-layers me-2"></i>
                        {{ $floor->name }} (Hall #{{ $floor->floor_number }})
                    </h6>
                    @if($floor->description)
                        <small class="text-muted">{{ $floor->description }}</small>
                    @endif
                </div>
                <div class="card-body">
                    @if($booths->isEmpty())
                        <p class="text-muted mb-0">No booths on this hall.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 12%;">Stall</th>
                                        <th style="width: 10%;">Category</th>
                                        <th style="width: 10%;">Size (sq m)</th>
                                        <th style="width: 10%;">Status</th>
                                        <th>Comment shown to exhibitor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($booths as $booth)
                                        @php
                                            $status = $booth->is_booked ? 'Booked' : ($booth->is_available ? 'Available' : 'Reserved');
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $booth->name }}</strong></td>
                                            <td>{{ $booth->category ?? '—' }}</td>
                                            <td>{{ $booth->size_sqft ?? '—' }}</td>
                                            <td>
                                                <span class="badge
                                                    @if($booth->is_booked) bg-warning
                                                    @elseif($booth->is_available) bg-success
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ $status }}
                                                </span>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="booth_comments[{{ $booth->id }}]"
                                                    class="form-control form-control-sm"
                                                    maxlength="500"
                                                    placeholder="Short note that will appear in the confirmation popup"
                                                    value="{{ old('booth_comments.' . $booth->id, $booth->comment) }}"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if($unassignedBooths->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Booths without assigned hall
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">Stall</th>
                                    <th style="width: 10%;">Category</th>
                                    <th style="width: 10%;">Size (sq m)</th>
                                    <th style="width: 10%;">Status</th>
                                    <th>Comment shown to exhibitor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unassignedBooths as $booth)
                                    @php
                                        $status = $booth->is_booked ? 'Booked' : ($booth->is_available ? 'Available' : 'Reserved');
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $booth->name }}</strong></td>
                                        <td>{{ $booth->category ?? '—' }}</td>
                                        <td>{{ $booth->size_sqft ?? '—' }}</td>
                                        <td>
                                            <span class="badge
                                                @if($booth->is_booked) bg-warning
                                                @elseif($booth->is_available) bg-success
                                                @else bg-secondary
                                                @endif">
                                                {{ $status }}
                                            </span>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="booth_comments[{{ $booth->id }}]"
                                                class="form-control form-control-sm"
                                                maxlength="500"
                                                placeholder="Short note that will appear in the confirmation popup"
                                                value="{{ old('booth_comments.' . $booth->id, $booth->comment) }}"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">
                        These booths are not linked to any hall. They will still show comments on the exhibitor floorplan if selectable.
                    </small>
                </div>
            </div>
        @endif
    @endif

    <div class="d-flex justify-content-end">
        <a href="{{ route('admin.exhibitions.step4', $exhibition->id) }}" class="btn btn-secondary me-2">Back</a>
        <button type="submit" class="btn btn-primary">Save Stall Comments &amp; Finish</button>
    </div>
</form>
@endsection

