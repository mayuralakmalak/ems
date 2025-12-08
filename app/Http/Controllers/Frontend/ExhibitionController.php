<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    public function index()
    {
        // Active exhibitions (currently ongoing)
        $activeExhibitions = Exhibition::with('booths')
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest()
            ->take(3)
            ->get();
        
        // Upcoming exhibitions (future dates)
        $upcomingExhibitions = Exhibition::with('booths')
            ->where('status', 'active')
            ->where('start_date', '>', now())
            ->latest()
            ->take(3)
            ->get();
        
        return view('frontend.exhibitions.index', compact('activeExhibitions', 'upcomingExhibitions'));
    }

    public function list()
    {
        $exhibitions = Exhibition::where('status', 'active')
            ->latest()
            ->paginate(9); // 3-card grid per page for front-end list
        return view('frontend.exhibitions.list', compact('exhibitions'));
    }

    public function show($id)
    {
        try {
            $exhibition = Exhibition::with('booths')->findOrFail($id);
            
            // If user is logged in, show booking page, otherwise show public view
            if (auth()->check()) {
                return view('frontend.bookings.create', compact('exhibition'));
            }
            
            return view('frontend.exhibitions.show', compact('exhibition'));
        } catch (\Exception $e) {
            return redirect()->route('home')
                ->with('error', 'Exhibition not found or error loading exhibition details.');
        }
    }
}
