@extends('layouts.admin')

@section('title', 'Create Exhibition - Step 4')
@section('page-title', 'Create Exhibition - Step 4: Badge Management & Manual')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Step 4: Badge Management & Exhibition Manual</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.step4.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <h6 class="mb-3">Badge Configuration</h6>
            <div class="row mb-4">
                @foreach(['Primary', 'Secondary', 'Additional'] as $badgeType)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6>{{ $badgeType }} Badge</h6>
                            <input type="hidden" name="badge_configurations[{{ strtolower($badgeType) }}][badge_type]" value="{{ $badgeType }}">
                            
                            <div class="mb-2">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="badge_configurations[{{ strtolower($badgeType) }}][quantity]" class="form-control" min="0" value="0">
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Pricing Type</label>
                                <select name="badge_configurations[{{ strtolower($badgeType) }}][pricing_type]" class="form-select">
                                    <option value="Free">Free</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Price (if Paid)</label>
                                <input type="number" name="badge_configurations[{{ strtolower($badgeType) }}][price]" class="form-control" step="0.01" min="0" value="0">
                            </div>
                            
                            @if($badgeType === 'Additional')
                            <div class="mb-2">
                                <label class="form-label">Needs Admin Approval</label>
                                <select name="badge_configurations[{{ strtolower($badgeType) }}][needs_admin_approval]" class="form-select">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Access Permissions</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="badge_configurations[{{ strtolower($badgeType) }}][access_permissions][]" value="Entry Only" id="entry_{{ strtolower($badgeType) }}">
                                    <label class="form-check-label" for="entry_{{ strtolower($badgeType) }}">Entry Only</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="badge_configurations[{{ strtolower($badgeType) }}][access_permissions][]" value="Lunch" id="lunch_{{ strtolower($badgeType) }}">
                                    <label class="form-check-label" for="lunch_{{ strtolower($badgeType) }}">Lunch</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="badge_configurations[{{ strtolower($badgeType) }}][access_permissions][]" value="Snacks" id="snacks_{{ strtolower($badgeType) }}">
                                    <label class="form-check-label" for="snacks_{{ strtolower($badgeType) }}">Snacks</label>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <h6 class="mb-3">Exhibition Manual</h6>
            <div class="mb-4">
                <label class="form-label">Upload Exhibition Manual (PDF)</label>
                <input type="file" name="exhibition_manual_pdf" class="form-control" accept=".pdf">
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.exhibitions.step3', $exhibition->id) }}" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-success">Complete & Save Exhibition</button>
            </div>
        </form>
    </div>
</div>
@endsection

