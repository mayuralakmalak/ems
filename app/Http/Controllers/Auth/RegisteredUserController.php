<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $countries = Country::active()->ordered()->get(['id', 'name', 'code', 'phone_code', 'phonecode', 'emoji', 'is_active', 'sort_order'])->unique('id')->values();
        return view('auth.register', compact('countries'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|View
    {
        $request->validate([
            // Company Details
            'company_name' => ['required', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            
            // Contact Person
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'mobile_phone_code' => ['required', 'string', 'max:10'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'phone_phone_code' => ['nullable', 'string', 'max:10'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // Address
            'company_address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'exists:countries,id'],
            'zip_code' => ['required', 'string', 'max:20'],
            'state' => ['required', 'exists:states,id'],
            
            // Is Member
            'is_member' => ['required', 'in:yes,no'],
            
            // Tax
            'has_gst_number' => ['nullable', 'in:yes,no'],
            'gst_number' => [
                'nullable',
                'string',
                'max:15',
                // Indian GSTIN format: 15 chars, 2 digits + PAN + 1 entity code + Z + 1 checksum
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/',
            ],
            'gst_certificate' => ['nullable', 'file', 'max:5120'],
            
            // Terms
            'terms' => ['required', 'accepted'],
            'privacy_policy' => ['required', 'accepted'],
            'refund_cancellation_policy' => ['required', 'accepted'],
            'exhibitor_rules' => ['required', 'accepted'],
        ]);

        // Get country and state names from IDs
        $country = Country::find($request->country);
        $state = State::find($request->state);
        
        // Get phone codes from separate dropdowns
        $mobilePhoneCode = $request->mobile_phone_code ?? '';
        $phonePhoneCode = $request->phone_phone_code ?? '';
        
        // Process mobile number - store only the number part (without code)
        $mobileNumber = $request->mobile_number;
        if ($mobilePhoneCode && !empty($mobileNumber)) {
            // Remove any existing country code if present
            $mobileNumber = preg_replace('/^\+?' . preg_quote($mobilePhoneCode, '/') . '/', '', $mobileNumber);
            // Clean the number (remove any non-digit characters except leading +)
            $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
        } elseif (!empty($mobileNumber)) {
            // If no country code provided, try to extract it or keep as is
            $mobileNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
        }
        
        // Process phone number - store only the number part (without code)
        $phoneNumber = $request->phone_number;
        if ($phoneNumber && $phonePhoneCode) {
            // Remove any existing country code if present
            $phoneNumber = preg_replace('/^\+?' . preg_quote($phonePhoneCode, '/') . '/', '', $phoneNumber);
            // Clean the number (remove any non-digit characters)
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        } elseif ($phoneNumber) {
            // If no country code provided, clean the number
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        }
        
        // Format full phone for backward compatibility (with code)
        $fullMobileNumber = $mobilePhoneCode && $mobileNumber ? '+' . $mobilePhoneCode . $mobileNumber : ($mobileNumber ? $mobileNumber : null);
        $fullPhoneNumber = $phonePhoneCode && $phoneNumber ? '+' . $phonePhoneCode . $phoneNumber : ($phoneNumber ? $phoneNumber : null);

        // Handle GST certificate upload (optional)
        $gstCertificatePath = null;
        if ($request->hasFile('gst_certificate')) {
            $gstCertificatePath = $request->file('gst_certificate')->store('gst-certificates', 'public');
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $fullMobileNumber, // Keep for backward compatibility
            'mobile_number' => $mobileNumber, // Store only number part
            'mobile_number_phone_code' => $mobilePhoneCode ? '+' . $mobilePhoneCode : null, // Store code separately
            'phone_number' => $phoneNumber, // Store only number part
            'phone_number_phone_code' => $phonePhoneCode ? '+' . $phonePhoneCode : null, // Store code separately
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'website' => $request->company_website,
            'address' => $request->company_address,
            'city' => $request->city,
            'state' => $state ? $state->name : $request->state,
            'country' => $country ? $country->name : $request->country,
            'pincode' => $request->zip_code,
            'gst_number' => $request->gst_number,
            'has_gst_number' => $request->has_gst_number === 'yes',
            'gst_certificate' => $gstCertificatePath,
            'is_member' => $request->input('is_member', 'no') === 'yes',
        ]);

        // Assign Exhibitor role by default
        if (!$user->hasRole('Exhibitor')) {
            $user->assignRole('Exhibitor');
        }

        event(new Registered($user));

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        // Show thank you / next-steps page (user is NOT logged in)
        return view('auth.register-thank-you', [
            'email' => $user->email,
        ]);
    }

    /**
     * Get states by country ID (API endpoint)
     */
    public function getStates(Request $request)
    {
        try {
            $countryId = $request->get('country_id');
            if (!$countryId) {
                return response()->json(['states' => []]);
            }
            
            // Simple query - get all states for the country, ordered by name
            $states = State::where('country_id', $countryId)
                ->orderBy('name', 'asc')
                ->get(['id', 'name'])
                ->unique('id')
                ->values();
            
            return response()->json(['states' => $states]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error loading states: ' . $e->getMessage(), [
                'country_id' => $request->get('country_id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return error details for debugging
            return response()->json([
                'error' => 'Failed to load states',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
                'states' => []
            ], 500);
        }
    }
}
