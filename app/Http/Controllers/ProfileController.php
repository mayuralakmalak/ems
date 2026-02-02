<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Check if user is exhibitor and use exhibitor layout
        if ($request->user()->hasRole('Exhibitor') || !$request->user()->hasAnyRole(['Admin', 'Sub Admin'])) {
            return view('frontend.profile.edit', [
                'user' => $request->user(),
                'countries' => Country::active()->ordered()->get(),
            ]);
        }
        
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            $user->photo = $photoPath;
        }
        
        // Handle GST certificate upload (optional)
        if ($request->hasFile('gst_certificate')) {
            $gstCertificatePath = $request->file('gst_certificate')->store('gst-certificates', 'public');
            $user->gst_certificate = $gstCertificatePath;
        }
        
        // Update other fields
        $data = $request->only([
            'name',
            'email',
            'phone',
            'company_name',
            'address',
            'city',
            'state',
            'country',
            'pincode',
            'gst_number',
            'has_gst_number',
            'pan_number',
            'website',
            'company_description',
        ]);
        $data['is_member'] = $request->input('is_member', 'no') === 'yes';

        // If country/state come as IDs (from dropdown), convert to names before saving
        if (!empty($data['country']) && ctype_digit((string) $data['country'])) {
            $countryModel = Country::find($data['country']);
            if ($countryModel) {
                $data['country'] = $countryModel->name;
            }
        }

        if (!empty($data['state']) && ctype_digit((string) $data['state'])) {
            // State model is not imported here on purpose; we only convert if needed in registration.
            // For profile updates we generally expect the state name, so leave as-is for non-numeric values.
            // Numeric values will be treated as-is if no matching state is found.
            $stateModel = \App\Models\State::find($data['state']);
            if ($stateModel) {
                $data['state'] = $stateModel->name;
            }
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $redirectRoute = ($user->hasRole('Exhibitor') || !$user->hasAnyRole(['Admin', 'Sub Admin'])) 
            ? 'profile.edit' 
            : 'profile.edit';

        return Redirect::route($redirectRoute)->with('success', 'Profile updated successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
