@extends('layouts.admin')

@section('title', 'Admin - Exhibition booking step 3')
@section('page-title', 'Admin - Exhibition booking step 3')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin - Exhibition booking step 3</h4>
            <span class="text-muted">24 / 36</span>
        </div>
        <div class="text-center mb-4">
            <h5>Step 3</h5>
        </div>
    </div>
</div>

<form action="{{ route('admin.exhibitions.step3.store', $exhibition->id) }}" method="POST" id="paymentScheduleForm">
    @csrf
    
    <!-- Payment Schedule Setup -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Payment Schedule Setup</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Number of payment ports (fixed, cannot change after creation)</p>
            
            <input type="hidden" name="payment_parts" id="payment_parts" value="3">
            
            <div id="paymentPartsContainer">
                @php
                    $schedules = $exhibition->paymentSchedules ?? collect();
                    $defaultParts = $schedules->count() > 0 ? $schedules : collect([
                        (object)['part_number' => 1, 'percentage' => null, 'due_date' => null],
                        (object)['part_number' => 2, 'percentage' => null, 'due_date' => null],
                        (object)['part_number' => 3, 'percentage' => null, 'due_date' => null],
                    ]);
                @endphp
                
                @foreach($defaultParts as $index => $part)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Part {{ $part->part_number ?? ($index + 1) }}: Percentage</label>
                        <input type="number" name="parts[{{ $index }}][percentage]" class="form-control" step="0.01" min="0" max="100" 
                               value="{{ $part->percentage ?? '' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Part {{ $part->part_number ?? ($index + 1) }}: Due Date</label>
                        <input type="date" name="parts[{{ $index }}][due_date]" class="form-control" 
                               value="{{ $part->due_date ? \Carbon\Carbon::parse($part->due_date)->format('Y-m-d') : '' }}" required>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Stall Scheme Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Stall Scheme Configuration</h6>
        </div>
        <div class="card-body">
            <!-- For 9 sq m stalls -->
            <div class="mb-4">
                <h6 class="mb-3">For 9 sq m stalls:</h6>
                <div id="stallScheme9Container">
                    @php
                        $scheme9 = $exhibition->stallSchemes->where('size_sqm', 9)->first();
                        $items9 = $scheme9 ? $scheme9->items : [['item_name' => '1 table, 2 chairs, 2 lights', 'quantity' => 1]];
                    @endphp
                    
                    @foreach($items9 as $itemIndex => $item)
                    <div class="row mb-2 stall-item-9" data-index="{{ $itemIndex }}">
                        <div class="col-md-5">
                            <input type="text" name="stall_scheme_9[{{ $itemIndex }}][item_name]" class="form-control" 
                                   placeholder="Item Name" value="{{ $item['item_name'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="stall_scheme_9[{{ $itemIndex }}][quantity]" class="form-control" 
                                   placeholder="Quantity" value="{{ $item['quantity'] ?? 1 }}" min="1">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-sm btn-success add-stall-item-9">
                                + Add
                            </button>
                            <button type="button" class="btn btn-sm btn-danger remove-stall-item" data-target=".stall-item-9">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- For 18 sq m stalls -->
            <div class="mb-4">
                <h6 class="mb-3">For 18 sq m stalls:</h6>
                <div id="stallScheme18Container">
                    @php
                        $scheme18 = $exhibition->stallSchemes->where('size_sqm', 18)->first();
                        $items18 = $scheme18 ? $scheme18->items : [];
                    @endphp
                    
                    @if(count($items18) > 0)
                        @foreach($items18 as $itemIndex => $item)
                        <div class="row mb-2 stall-item-18" data-index="{{ $itemIndex }}">
                            <div class="col-md-5">
                                <input type="text" name="stall_scheme_18[{{ $itemIndex }}][item_name]" class="form-control" 
                                       placeholder="Item Name" value="{{ $item['item_name'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="stall_scheme_18[{{ $itemIndex }}][quantity]" class="form-control" 
                                       placeholder="Quantity" value="{{ $item['quantity'] ?? 1 }}" min="1">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-success add-stall-item-18">
                                    + Add
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-stall-item" data-target=".stall-item-18">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="row mb-2 stall-item-18" data-index="0">
                            <div class="col-md-5">
                                <input type="text" name="stall_scheme_18[0][item_name]" class="form-control" placeholder="Item Name">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="stall_scheme_18[0][quantity]" class="form-control" placeholder="Quantity" value="1" min="1">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-success add-stall-item-18">
                                    + Add
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-stall-item" data-target=".stall-item-18">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cut-off Dates -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Cut-off Dates</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Add-on services cut-off date:</label>
                    <div class="input-group">
                        <input type="date" name="addon_services_cutoff_date" class="form-control" 
                               value="{{ $exhibition->addon_services_cutoff_date ? $exhibition->addon_services_cutoff_date->format('Y-m-d') : '' }}">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Document upload deadlines:</label>
                    <div class="input-group">
                        <input type="date" name="document_upload_deadline" class="form-control" 
                               value="{{ $exhibition->document_upload_deadline ? $exhibition->document_upload_deadline->format('Y-m-d') : '' }}">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="btn btn-secondary me-2">Back</a>
        <button type="submit" class="btn btn-primary">Save and Continue to Step 4</button>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex9 = {{ count($items9) }};
    let itemIndex18 = {{ count($items18 ?? []) }};
    
    // Add item for 9 sq m
    $(document).on('click', '.add-stall-item-9', function() {
        const html = `
            <div class="row mb-2 stall-item-9" data-index="${itemIndex9}">
                <div class="col-md-5">
                    <input type="text" name="stall_scheme_9[${itemIndex9}][item_name]" class="form-control" placeholder="Item Name">
                </div>
                <div class="col-md-3">
                    <input type="number" name="stall_scheme_9[${itemIndex9}][quantity]" class="form-control" placeholder="Quantity" value="1" min="1">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-success add-stall-item-9">+ Add</button>
                    <button type="button" class="btn btn-sm btn-danger remove-stall-item" data-target=".stall-item-9">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
        $('#stallScheme9Container').append(html);
        itemIndex9++;
    });
    
    // Add item for 18 sq m
    $(document).on('click', '.add-stall-item-18', function() {
        const html = `
            <div class="row mb-2 stall-item-18" data-index="${itemIndex18}">
                <div class="col-md-5">
                    <input type="text" name="stall_scheme_18[${itemIndex18}][item_name]" class="form-control" placeholder="Item Name">
                </div>
                <div class="col-md-3">
                    <input type="number" name="stall_scheme_18[${itemIndex18}][quantity]" class="form-control" placeholder="Quantity" value="1" min="1">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-success add-stall-item-18">+ Add</button>
                    <button type="button" class="btn btn-sm btn-danger remove-stall-item" data-target=".stall-item-18">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
        $('#stallScheme18Container').append(html);
        itemIndex18++;
    });
    
    // Remove item
    $(document).on('click', '.remove-stall-item', function() {
        $(this).closest('.row').remove();
    });
});
</script>
@endpush
@endsection
