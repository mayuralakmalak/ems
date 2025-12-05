@extends('layouts.admin')

@section('title', 'Admin system settings')
@section('page-title', 'Admin system settings')

@push('styles')
<style>
    .settings-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .section-subtitle {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
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
    
    .form-check-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .btn-save {
        padding: 12px 30px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .btn-save:hover {
        background: #4f46e5;
    }
    
    .btn-check {
        padding: 12px 30px;
        background: #f3f4f6;
        color: #1e293b;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .input-group {
        display: flex;
        align-items: center;
    }
    
    .input-group-text {
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-left: none;
        border-radius: 0 8px 8px 0;
        color: #64748b;
    }
    
    .input-group .form-control {
        border-right: none;
        border-radius: 8px 0 0 8px;
    }
</style>
@endpush

@section('content')
<!-- Payment Gateway -->
<div class="settings-section">
    <h4 class="section-title">Payment Gateway</h4>
    <p class="section-subtitle">Configure payment processing settings.</p>
    
    <form action="{{ route('admin.settings.save-payment-gateway') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <input type="text" name="api_key" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Secret API Key</label>
                    <input type="text" name="secret_api_key" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Secret Key</label>
                    <input type="text" name="secret_key" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Access Key</label>
                    <input type="text" name="access_key" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Payment Gateway</label>
                    <select name="payment_gateway" class="form-select" required>
                        <option value="Stripe" selected>Stripe</option>
                        <option value="Razorpay">Razorpay</option>
                        <option value="PayPal">PayPal</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label d-flex align-items-center">
                        <input type="checkbox" name="test_mode" class="form-check-input me-2">
                        Enable Test Mode
                    </label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Payment Gateway Settings</button>
    </form>
</div>

<!-- Email/SMS Settings -->
<div class="settings-section">
    <h4 class="section-title">Email/SMS Settings</h4>
    <p class="section-subtitle">Configure email and SMS notification settings.</p>
    
    <form action="{{ route('admin.settings.save-email-sms') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMTP Port</label>
                    <input type="text" name="smtp_port" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMTP User</label>
                    <input type="text" name="smtp_user" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMTP Pass</label>
                    <input type="password" name="smtp_pass" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">From Name</label>
                    <input type="text" name="from_name" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">From Email</label>
                    <input type="email" name="from_email" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="admin_email" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Admin Phone</label>
                    <input type="text" name="admin_phone" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Twilio SID</label>
                    <input type="text" name="twilio_sid" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Twilio Auth Token</label>
                    <input type="text" name="twilio_auth_token" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Twilio From Number</label>
                    <input type="text" name="twilio_from_number" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMS Gateway</label>
                    <select name="sms_gateway" class="form-select">
                        <option value="Twilio" selected>Twilio</option>
                        <option value="AWS SNS">AWS SNS</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMS API Key</label>
                    <input type="text" name="sms_api_key" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMS Sender ID</label>
                    <input type="text" name="sms_sender_id" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SMS Route</label>
                    <input type="text" name="sms_route" class="form-control">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Email/SMS Settings</button>
    </form>
</div>

<!-- OTP/DLT Registration -->
<div class="settings-section">
    <h4 class="section-title">OTP/DLT Registration</h4>
    <p class="section-subtitle">Do you have DLT Registered Number?</p>
    
    <form action="{{ route('admin.settings.save-otp-dlt') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">DLT Registered No</label>
                    <input type="text" name="dlt_registered_no" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">DLT Template ID (OTP)</label>
                    <input type="text" name="dlt_template_id_otp" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">DLT Template ID (SMS)</label>
                    <input type="text" name="dlt_template_id_sms" class="form-control">
                </div>
            </div>
        </div>
        <div class="d-flex gap-3">
            <button type="submit" class="btn-save">Save OTP/DLT Settings</button>
            <button type="button" class="btn-check">Check DLT Status</button>
        </div>
    </form>
</div>

<!-- Default Pricing -->
<div class="settings-section">
    <h4 class="section-title">Default Pricing</h4>
    <p class="section-subtitle">Set default pricing for your new rooms.</p>
    
    <form action="{{ route('admin.settings.save-default-pricing') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Default Payment Gateway</label>
                    <select name="default_payment_gateway" class="form-select">
                        <option value="Stripe" selected>Stripe</option>
                        <option value="Razorpay">Razorpay</option>
                        <option value="PayPal">PayPal</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Default Price</label>
                    <div class="input-group">
                        <input type="number" name="default_price" class="form-control" step="0.01">
                        <span class="input-group-text">INR</span>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Default Pricing</button>
    </form>
</div>

<!-- Cancellation Charges -->
<div class="settings-section">
    <h4 class="section-title">Cancellation Charges</h4>
    <p class="section-subtitle">Specify charges to be levied on cancellations.</p>
    
    <form action="{{ route('admin.settings.save-cancellation-charges') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Cancellation Before (Hrs)</label>
                    <div class="input-group">
                        <input type="number" name="cancellation_before_hrs" class="form-control">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Cancellation Charge (%)</label>
                    <div class="input-group">
                        <input type="number" name="cancellation_charge_percent" class="form-control" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">24-48 Hrs Cancellation (%)</label>
                    <div class="input-group">
                        <input type="number" name="cancellation_24_48_hrs" class="form-control" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">12-24 Hrs Cancellation (%)</label>
                    <div class="input-group">
                        <input type="number" name="cancellation_12_24_hrs" class="form-control" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Less than 12 Hrs Cancellation (%)</label>
                    <div class="input-group">
                        <input type="number" name="cancellation_less_12_hrs" class="form-control" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Cancellation Charges</button>
    </form>
</div>
@endsection

