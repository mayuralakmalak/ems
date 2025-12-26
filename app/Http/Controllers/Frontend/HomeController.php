<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get featured/upcoming exhibition for hero section
        $featuredExhibition = Exhibition::where('status', 'active')
            ->where('start_date', '>', now())
            ->latest()
            ->first();
        
        // If no upcoming, get the most recent active one
        if (!$featuredExhibition) {
            $featuredExhibition = Exhibition::where('status', 'active')
                ->latest()
                ->first();
        }
        
        // Get latest exhibition for the overlap section (only one)
        $latestExhibition = Exhibition::where('status', 'active')
            ->where('id', '!=', $featuredExhibition?->id)
            ->latest()
            ->first();
        
        // Get stats (you can make these dynamic later)
        $stats = [
            'exhibitors' => 500,
            'events' => 400,
            'visitors' => 15000,
        ];
        
        return view('frontend.home', compact('featuredExhibition', 'latestExhibition', 'stats'));
    }
}

