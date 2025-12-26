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
<!-- General Settings -->
<div class="settings-section">
    <h4 class="section-title">General Company Information</h4>
    <p class="section-subtitle">Configure your exhibition company's general information and contact details.</p>
    
    <form action="{{ route('admin.settings.save-general') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Company Logo</label>
                    @if(isset($generalSettings['company_logo']) && $generalSettings['company_logo'])
                        <div class="mb-2">
                            <img src="{{ Storage::url($generalSettings['company_logo']) }}" alt="Company Logo" style="max-height: 80px; max-width: 200px; object-fit: contain; border: 1px solid #e2e8f0; padding: 5px; border-radius: 5px;">
                        </div>
                    @endif
                    <input type="file" name="company_logo" class="form-control" accept="image/*">
                    <small class="text-muted">Recommended size: 200x80px. Max file size: 2MB. Supported formats: JPG, PNG, GIF</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" class="form-control" value="{{ isset($generalSettings['company_name']) ? $generalSettings['company_name'] : '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Company Email</label>
                    <input type="email" name="company_email" class="form-control" value="{{ isset($generalSettings['company_email']) ? $generalSettings['company_email'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="{{ isset($generalSettings['contact_number']) ? $generalSettings['contact_number'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Support Email</label>
                    <input type="email" name="support_email" class="form-control" value="{{ isset($generalSettings['support_email']) ? $generalSettings['support_email'] : '' }}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ isset($generalSettings['address']) ? $generalSettings['address'] : '' }}</textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ isset($generalSettings['city']) ? $generalSettings['city'] : '' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ isset($generalSettings['state']) ? $generalSettings['state'] : '' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <select name="country" class="form-select">
                        <option value="">Select Country</option>
                        @if(isset($countries) && $countries->count() > 0)
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" {{ (isset($generalSettings['country']) && $generalSettings['country'] == $country->name) ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No countries available</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Pincode/ZIP Code</label>
                    <input type="text" name="pincode" class="form-control" value="{{ isset($generalSettings['pincode']) ? $generalSettings['pincode'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="{{ isset($generalSettings['website']) ? $generalSettings['website'] : '' }}" placeholder="https://example.com">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save General Settings</button>
    </form>
</div>

<!-- Social Media Links -->
<div class="settings-section">
    <h4 class="section-title">Social Media Links</h4>
    <p class="section-subtitle">Configure your company's social media profile links.</p>
    
    <form action="{{ route('admin.settings.save-general') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-control" value="{{ isset($generalSettings['facebook_url']) ? $generalSettings['facebook_url'] : '' }}" placeholder="https://facebook.com/yourpage">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Instagram URL</label>
                    <input type="url" name="instagram_url" class="form-control" value="{{ isset($generalSettings['instagram_url']) ? $generalSettings['instagram_url'] : '' }}" placeholder="https://instagram.com/yourpage">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Twitter/X URL</label>
                    <input type="url" name="twitter_url" class="form-control" value="{{ isset($generalSettings['twitter_url']) ? $generalSettings['twitter_url'] : '' }}" placeholder="https://twitter.com/yourpage">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">YouTube URL</label>
                    <input type="url" name="youtube_url" class="form-control" value="{{ isset($generalSettings['youtube_url']) ? $generalSettings['youtube_url'] : '' }}" placeholder="https://youtube.com/yourchannel">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Social Media Links</button>
    </form>
</div>

<!-- Bank Account Details -->
<div class="settings-section">
    <h4 class="section-title">Bank Account Details</h4>
    <p class="section-subtitle">Configure your company's bank account information for payment processing.</p>
    
    <form action="{{ route('admin.settings.save-general') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="{{ isset($generalSettings['bank_name']) ? $generalSettings['bank_name'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_number" class="form-control" value="{{ isset($generalSettings['bank_account_number']) ? $generalSettings['bank_account_number'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="bank_ifsc_code" class="form-control" value="{{ isset($generalSettings['bank_ifsc_code']) ? $generalSettings['bank_ifsc_code'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">SWIFT Code</label>
                    <input type="text" name="bank_swift_code" class="form-control" value="{{ isset($generalSettings['bank_swift_code']) ? $generalSettings['bank_swift_code'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="bank_branch" class="form-control" value="{{ isset($generalSettings['bank_branch']) ? $generalSettings['bank_branch'] : '' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Branch Address</label>
                    <input type="text" name="bank_address" class="form-control" value="{{ isset($generalSettings['bank_address']) ? $generalSettings['bank_address'] : '' }}">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Bank Details</button>
    </form>
</div>

<!-- Tax & Legal Information -->
<div class="settings-section">
    <h4 class="section-title">Tax & Legal Information</h4>
    <p class="section-subtitle">Configure your company's tax identification numbers and legal details.</p>
    
    <form action="{{ route('admin.settings.save-general') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" value="{{ isset($generalSettings['gst_number']) ? $generalSettings['gst_number'] : '' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" value="{{ isset($generalSettings['pan_number']) ? $generalSettings['pan_number'] : '' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Tax ID</label>
                    <input type="text" name="tax_id" class="form-control" value="{{ isset($generalSettings['tax_id']) ? $generalSettings['tax_id'] : '' }}">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Tax Information</button>
    </form>
</div>

<!-- Payment & System Settings -->
<div class="settings-section">
    <h4 class="section-title">Payment & System Settings</h4>
    <p class="section-subtitle">Configure payment terms and system preferences.</p>
    
    <form action="{{ route('admin.settings.save-general') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="INR" {{ isset($generalSettings['currency']) && $generalSettings['currency'] == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                        <option value="USD" {{ isset($generalSettings['currency']) && $generalSettings['currency'] == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ isset($generalSettings['currency']) && $generalSettings['currency'] == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="GBP" {{ isset($generalSettings['currency']) && $generalSettings['currency'] == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                        <option value="AED" {{ isset($generalSettings['currency']) && $generalSettings['currency'] == 'AED' ? 'selected' : '' }}>AED - UAE Dirham</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Timezone</label>
                    <select name="timezone" class="form-select">
                        <option value="Asia/Kolkata" {{ isset($generalSettings['timezone']) && $generalSettings['timezone'] == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                        <option value="UTC" {{ isset($generalSettings['timezone']) && $generalSettings['timezone'] == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ isset($generalSettings['timezone']) && $generalSettings['timezone'] == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                        <option value="Europe/London" {{ isset($generalSettings['timezone']) && $generalSettings['timezone'] == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        <option value="Asia/Dubai" {{ isset($generalSettings['timezone']) && $generalSettings['timezone'] == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Payment Terms</label>
                    <textarea name="payment_terms" class="form-control" rows="4" placeholder="Enter payment terms and conditions...">{{ isset($generalSettings['payment_terms']) ? $generalSettings['payment_terms'] : '' }}</textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn-save">Save Payment & System Settings</button>
    </form>
</div>

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
                    <label class="form-label">SMS Gateway</label>
                    <select name="sms_gateway" class="form-select">
                        <option value="AWS SNS" selected>AWS SNS</option>
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

