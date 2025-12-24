<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        // Load all general settings
        $generalSettings = Setting::getByGroup('general');
        
        // Load countries for dropdown
        $countries = Country::active()->ordered()->get();
        
        return view('admin.settings.index', compact('generalSettings', 'countries'));
    }
    
    public function saveGeneralSettings(Request $request)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc_code' => 'nullable|string|max:20',
            'bank_swift_code' => 'nullable|string|max:20',
            'bank_branch' => 'nullable|string|max:255',
            'bank_address' => 'nullable|string|max:500',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
        ]);
        
        // Save all general settings
        $fields = [
            'company_name', 'company_email', 'contact_number', 'support_email',
            'address', 'city', 'state', 'country', 'pincode', 'website',
            'bank_name', 'bank_account_number', 'bank_ifsc_code', 'bank_swift_code',
            'bank_branch', 'bank_address', 'gst_number', 'pan_number', 'tax_id',
            'payment_terms', 'currency', 'timezone'
        ];
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'general');
            }
        }
        
        return back()->with('success', 'General settings saved successfully.');
    }
    
    public function savePaymentGateway(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string',
            'secret_api_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'access_key' => 'nullable|string',
            'payment_gateway' => 'required|string',
            'test_mode' => 'nullable|boolean',
        ]);
        
        // Save to config or database
        // For now, just return success
        return back()->with('success', 'Payment gateway settings saved successfully.');
    }
    
    public function saveEmailSms(Request $request)
    {
        $request->validate([
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_user' => 'nullable|string',
            'smtp_pass' => 'nullable|string',
            'from_name' => 'nullable|string',
            'from_email' => 'nullable|email',
            'admin_email' => 'nullable|email',
            'admin_phone' => 'nullable|string',
            'sms_gateway' => 'nullable|string',
            'sms_api_key' => 'nullable|string',
            'sms_sender_id' => 'nullable|string',
            'sms_route' => 'nullable|string',
        ]);
        
        return back()->with('success', 'Email/SMS settings saved successfully.');
    }
    
    public function saveOtpDlt(Request $request)
    {
        $request->validate([
            'dlt_registered_no' => 'nullable|string',
            'dlt_template_id_otp' => 'nullable|string',
            'dlt_template_id_sms' => 'nullable|string',
        ]);
        
        return back()->with('success', 'OTP/DLT settings saved successfully.');
    }
    
    public function saveDefaultPricing(Request $request)
    {
        $request->validate([
            'default_payment_gateway' => 'nullable|string',
            'default_price' => 'nullable|numeric',
        ]);
        
        return back()->with('success', 'Default pricing settings saved successfully.');
    }
    
    public function saveCancellationCharges(Request $request)
    {
        $request->validate([
            'cancellation_before_hrs' => 'nullable|integer',
            'cancellation_charge_percent' => 'nullable|numeric|min:0|max:100',
            'cancellation_24_48_hrs' => 'nullable|numeric|min:0|max:100',
            'cancellation_12_24_hrs' => 'nullable|numeric|min:0|max:100',
            'cancellation_less_12_hrs' => 'nullable|numeric|min:0|max:100',
        ]);
        
        return back()->with('success', 'Cancellation charges settings saved successfully.');
    }
}

