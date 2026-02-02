@extends('layouts.admin')

@section('title', 'Import Discounts')
@section('page-title', 'Import Discounts')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Import Discounts from CSV</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong>
            <ul class="mb-0 mt-2">
                <li>Select an exhibition to associate discounts with</li>
                <li>CSV file must contain columns: <strong>title, code, type, amount, status, email</strong></li>
                <li>Type must be either "fixed" or "percentage"</li>
                <li>Status must be either "active" or "inactive"</li>
                <li>Amount must be a valid number</li>
                <li>Code must be unique</li>
            </ul>
        </div>

        <form action="{{ route('admin.discounts.process-import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Exhibition</label>
                    <select name="exhibition_id" class="form-select @error('exhibition_id') is-invalid @enderror">
                        <option value="">All Exhibitions</option>
                        @foreach($exhibitions as $exhibition)
                            <option value="{{ $exhibition->id }}" {{ old('exhibition_id') == $exhibition->id ? 'selected' : '' }}>
                                {{ $exhibition->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('exhibition_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Leave as "All Exhibitions" to apply discounts to all exhibitions</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CSV File <span class="text-danger">*</span></label>
                    <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror" accept=".csv,.txt" required>
                    @error('csv_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Maximum file size: 2MB</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Sample CSV Format:</label>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>title</th>
                                <th>code</th>
                                <th>type</th>
                                <th>amount</th>
                                <th>status</th>
                                <th>email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Early Bird Discount</td>
                                <td>EARLY2026</td>
                                <td>percentage</td>
                                <td>10</td>
                                <td>active</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Fixed Discount</td>
                                <td>FIXED500</td>
                                <td>fixed</td>
                                <td>500</td>
                                <td>active</td>
                                <td>user@example.com</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-upload me-2"></i>Import Discounts
                </button>
                <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
                <a href="{{ asset('sample_discounts.csv') }}" class="btn btn-outline-primary" download>
                    <i class="bi bi-download me-2"></i>Download Sample CSV
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
