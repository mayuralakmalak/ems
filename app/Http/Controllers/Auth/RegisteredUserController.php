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
        $countries = Country::active()->ordered()->get(['id', 'name', 'code', 'phone_code', 'phonecode', 'emoji', 'is_active', 'sort_order']);
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
            
            // Terms
            'terms' => ['required', 'accepted'],
        ]);

        // Get country and state names from IDs
        $country = Country::find($request->country);
        $state = State::find($request->state);
        
        // Get phone codes from separate dropdowns
        $mobilePhoneCode = $request->mobile_phone_code ?? '';
        $phonePhoneCode = $request->phone_phone_code ?? '';
        
        // Format mobile number with country code prefix
        $mobileNumber = $request->mobile_number;
        if ($mobilePhoneCode && !empty($mobileNumber)) {
            // Remove any existing country code if present
            $mobileNumber = preg_replace('/^\+?' . preg_quote($mobilePhoneCode, '/') . '/', '', $mobileNumber);
            // Add country code prefix
            $mobileNumber = '+' . $mobilePhoneCode . $mobileNumber;
        } elseif (!empty($mobileNumber) && !str_starts_with($mobileNumber, '+')) {
            // If no country code but number doesn't start with +, keep as is
            $mobileNumber = $mobileNumber;
        }
        
        // Format phone number with country code prefix
        $phoneNumber = $request->phone_number;
        if ($phoneNumber && $phonePhoneCode) {
            // Remove any existing country code if present
            $phoneNumber = preg_replace('/^\+?' . preg_quote($phonePhoneCode, '/') . '/', '', $phoneNumber);
            // Add country code prefix
            $phoneNumber = '+' . $phonePhoneCode . $phoneNumber;
        } elseif ($phoneNumber && !str_starts_with($phoneNumber, '+')) {
            // If no country code but number doesn't start with +, keep as is
            $phoneNumber = $phoneNumber;
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $mobileNumber, // Keep for backward compatibility
            'mobile_number' => $mobileNumber,
            'phone_number' => $phoneNumber,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'website' => $request->company_website,
            'address' => $request->company_address,
            'city' => $request->city,
            'state' => $state ? $state->name : $request->state,
            'country' => $country ? $country->name : $request->country,
            'pincode' => $request->zip_code,
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
                ->get(['id', 'name']);
            
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
