@extends('layouts.frontend')

@section('title', 'Registration Confirmation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-success text-white py-4">
                    <h1 class="h4 mb-0"><i class="bi bi-check-circle me-2"></i>Registration Received</h1>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <p class="lead">Thank you for your registration.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light text-dark fw-semibold" style="width: 40%;">Registration Number</th>
                                <td><strong>{{ $registration->registration_number }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Type</th>
                                <td>{{ ucfirst($registration->type) }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Exhibition</th>
                                <td>{{ $registration->exhibition->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Name</th>
                                <td>{{ $registration->full_name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Email</th>
                                <td>{{ $registration->email }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Fee</th>
                                <td>₹{{ number_format($registration->fee_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Paid</th>
                                <td>₹{{ number_format($registration->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Approval Status</th>
                                <td>
                                    <span class="badge bg-{{ $registration->approval_status === 'approved' ? 'success' : ($registration->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($registration->approval_status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark fw-semibold">Payment Status</th>
                                <td>
                                    <span class="badge bg-{{ $registration->payment_status === 'paid' ? 'success' : ($registration->payment_status === 'partial' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($registration->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    @if(isset($payment))
                        <div class="alert alert-info mt-3">
                            <strong>Payment submitted:</strong> {{ $payment->payment_number }} — ₹{{ number_format($payment->amount, 2) }} ({{ ucfirst($payment->payment_method) }}). Pending admin approval.
                        </div>
                    @endif
                    @if($registration->fee_amount > 0 && !$registration->isFullyPaid())
                        <a href="{{ route('register.payment', $registration->token) }}" class="btn btn-primary mt-3">Complete Payment</a>
                    @endif
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary mt-3 ms-2">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
