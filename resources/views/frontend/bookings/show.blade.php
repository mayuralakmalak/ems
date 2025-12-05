@extends('layouts.exhibitor')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@push('styles')
<style>
    .booking-details-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .section-header {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .detail-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .detail-item {
        margin-bottom: 15px;
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
        font-size: 1rem;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-confirmed {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .booth-icon {
        font-size: 2rem;
        color: #6366f1;
        margin-right: 15px;
    }
    
    .booth-features {
        list-style: none;
        padding: 0;
        margin-top: 15px;
    }
    
    .booth-features li {
        padding: 8px 0;
        color: #64748b;
        font-size: 0.95rem;
        position: relative;
        padding-left: 25px;
    }
    
    .booth-features li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #10b981;
        font-weight: bold;
    }
    
    .payment-history-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-history-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .payment-history-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .payment-history-table tr:last-child td {
        border-bottom: none;
    }
    
    .status-paid {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .document-status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .document-status-item:last-child {
        border-bottom: none;
    }
    
    .document-name {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        color: #1e293b;
    }
    
    .document-status {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-uploaded {
        color: #1e40af;
    }
    
    .status-pending-doc {
        color: #f59e0b;
    }
    
    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: sticky;
        top: 20px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        color: #64748b;
        font-size: 0.95rem;
    }
    
    .summary-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .summary-total {
        font-size: 1.2rem;
        font-weight: 700;
    }
    
    .summary-balance {
        color: #ef4444;
        font-weight: 700;
    }
    
    .due-date-note {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 10px;
    }
    
    .action-buttons {
        margin-top: 25px;
    }
    
    .btn-action {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-weight: 500;
        margin-bottom: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-cancel {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-cancel:hover {
        background: #fecaca;
    }
    
    .btn-modify {
        background: #6366f1;
        color: white;
    }
    
    .btn-modify:hover {
        background: #4f46e5;
    }
    
    .btn-download {
        background: #f3f4f6;
        color: #1e293b;
    }
    
    .btn-download:hover {
        background: #e5e7eb;
    }
    
    .contact-emails, .contact-numbers {
        margin-top: 10px;
    }
    
    .contact-item {
        padding: 8px 0;
        color: #64748b;
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')
<div class="booking-details-container">
    <h2 class="section-header mb-4">Booking Details</h2>
    
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Booking Details -->
            <div class="detail-section">
                <h5 class="section-header">Booking Details</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Booking ID</div>
                            <div class="detail-value">{{ $booking->booking_number }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Event Name</div>
                            <div class="detail-value">{{ $booking->exhibition->name }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->start_date->format('F d') }} - {{ $booking->exhibition->end_date->format('d, Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Time</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->start_time ?? '9:00 AM' }} - {{ $booking->exhibition->end_time ?? '5:00 PM' }} Daily
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Duration</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->start_date->diffInDays($booking->exhibition->end_date) + 1 }} Days
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div>
                                <span class="status-badge status-confirmed">Confirmed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Primary Contact Person -->
            <div class="detail-section">
                <h5 class="section-header">Primary Contact Person</h5>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Name</div>
                            <div class="detail-value">{{ auth()->user()->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">{{ auth()->user()->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                
                @if($booking->contact_emails && count($booking->contact_emails) > 0)
                <div class="contact-emails">
                    <div class="detail-label">Additional Emails (up to 5)</div>
                    @foreach($booking->contact_emails as $email)
                    <div class="contact-item">{{ $email }}</div>
                    @endforeach
                </div>
                @endif
                
                @if($booking->contact_numbers && count($booking->contact_numbers) > 0)
                <div class="contact-numbers">
                    <div class="detail-label">Additional Phone Numbers (up to 5)</div>
                    @foreach($booking->contact_numbers as $number)
                    <div class="contact-item">{{ $number }}</div>
                    @endforeach
                </div>
                @endif
            </div>
            
            <!-- Booth Details -->
            <div class="detail-section">
                <h5 class="section-header">
                    <i class="bi bi-grid-3x3-gap booth-icon"></i>Booth Details
                </h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Booth Number</div>
                            <div class="detail-value">{{ $booking->booth->name ?? 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Booth Category</div>
                            <div class="detail-value">{{ $booking->booth->category ?? 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Booth Type</div>
                            <div class="detail-value">{{ $booking->booth->booth_type ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Location</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->venue }}, {{ $booking->exhibition->city }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <ul class="booth-features">
                    <li>High visibility location</li>
                    <li>Larger Footprint (double by)</li>
                    <li>Dedicated Power Outlet</li>
                    <li>Basic Furniture Package</li>
                    <li>High-speed Internet Access</li>
                </ul>
            </div>
            
            <!-- Payment History -->
            <div class="detail-section">
                <h5 class="section-header">Payment History</h5>
                
                <table class="payment-history-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Platform</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>
                                <span class="status-badge {{ $payment->status === 'completed' ? 'status-paid' : 'status-pending' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No payments yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Document Status -->
            <div class="detail-section">
                <h5 class="section-header">Document Status</h5>
                
                @php
                    $requiredDocs = [
                        'Exhibitor Agreement' => $booking->documents->where('type', 'Exhibitor Agreement')->first(),
                        'Company Registration' => $booking->documents->where('type', 'Company Registration')->first(),
                        'Product Catalog' => $booking->documents->where('type', 'Product Catalog')->first(),
                        'Insurance Certificate' => $booking->documents->where('type', 'Insurance Certificate')->first(),
                    ];
                @endphp
                
                @foreach($requiredDocs as $docName => $document)
                <div class="document-status-item">
                    <div class="document-name">
                        <i class="bi bi-circle-fill" style="color: {{ $document && $document->status === 'approved' ? '#1e40af' : '#f59e0b' }}; font-size: 0.5rem;"></i>
                        {{ $docName }}
                    </div>
                    <div class="document-status {{ $document && $document->status === 'approved' ? 'status-uploaded' : 'status-pending-doc' }}">
                        {{ $document && $document->status === 'approved' ? 'Uploaded' : 'Pending' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="summary-card">
                <h5 class="section-header">Booking Summary</h5>
                
                @php
                    $servicesTotal = $booking->bookingServices->sum(function($bs) {
                        return $bs->quantity * $bs->unit_price;
                    });
                    $taxes = ($booking->total_amount - $servicesTotal) * 0.1; // 10% tax
                    $discount = ($booking->total_amount * ($booking->discount_percent ?? 0)) / 100;
                    $totalAmount = $booking->total_amount;
                    $paidAmount = $booking->paid_amount;
                    $balanceDue = $totalAmount - $paidAmount;
                @endphp
                
                <div class="summary-item">
                    <span class="summary-label">Booth/Fee</span>
                    <span class="summary-value">₹{{ number_format($booking->booth->price ?? 0, 2) }}</span>
                </div>
                @if($servicesTotal > 0)
                <div class="summary-item">
                    <span class="summary-label">Service Charges</span>
                    <span class="summary-value">₹{{ number_format($servicesTotal, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label">Taxes</span>
                    <span class="summary-value">₹{{ number_format($taxes, 2) }}</span>
                </div>
                @if($discount > 0)
                <div class="summary-item">
                    <span class="summary-label">Discount</span>
                    <span class="summary-value" style="color: #10b981;">-₹{{ number_format($discount, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label summary-total">Total Amount</span>
                    <span class="summary-value summary-total">₹{{ number_format($totalAmount, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Amount Paid</span>
                    <span class="summary-value">₹{{ number_format($paidAmount, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Balance Due</span>
                    <span class="summary-value summary-balance">₹{{ number_format($balanceDue, 2) }}</span>
                </div>
                
                <div class="due-date-note">
                    Due by {{ $booking->exhibition->start_date->subDays(30)->format('F d, Y') }}
                </div>
                
                <div class="action-buttons">
                    @if($booking->status !== 'cancelled')
                    <a href="{{ route('bookings.cancel.show', $booking->id) }}" class="btn-action btn-cancel">
                        Cancel Booking
                    </a>
                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-modify">
                        Request Modification
                    </a>
                    @endif
                    <button class="btn-action btn-download" onclick="window.print()">
                        Download Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
