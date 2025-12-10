<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
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
            'country' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            'state' => ['required', 'string', 'max:100'],
            
            // Terms
            'terms' => ['required', 'accepted'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'website' => $request->company_website,
            'address' => $request->company_address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->zip_code,
        ]);

        // Assign Exhibitor role by default
        if (!$user->hasRole('Exhibitor')) {
            $user->assignRole('Exhibitor');
        }

        event(new Registered($user));

        Auth::login($user);

        // Redirect exhibitors to frontend homepage instead of dashboard
        return redirect(route('home', absolute: false));
    }
}
