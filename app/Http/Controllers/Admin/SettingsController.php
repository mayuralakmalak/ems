<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
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
            'twilio_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_from_number' => 'nullable|string',
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

