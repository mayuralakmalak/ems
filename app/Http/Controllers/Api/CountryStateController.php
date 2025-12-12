<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class CountryStateController extends Controller
{
    /**
     * Get all active countries
     */
    public function countries()
    {
        $countries = Country::active()->ordered()->get(['id', 'name', 'code', 'phone_code']);
        
        return response()->json($countries);
    }
    
    /**
     * Get states for a specific country
     */
    public function states($countryId)
    {
        $states = State::where('country_id', $countryId)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'code']);
        
        return response()->json($states);
    }
    
    /**
     * Get states by country code
     */
    public function statesByCode($countryCode)
    {
        $country = Country::where('code', $countryCode)->first();
        
        if (!$country) {
            return response()->json([]);
        }
        
        $states = State::where('country_id', $country->id)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'code']);
        
        return response()->json($states);
    }
}
