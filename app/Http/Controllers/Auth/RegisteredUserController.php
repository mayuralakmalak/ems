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
        $countries = Country::active()->ordered()->get();
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
            'mobile_number' => ['required', 'string', 'max:20'],
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
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->mobile_number,
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
