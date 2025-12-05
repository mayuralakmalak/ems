<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\BoothRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FloorplanController extends Controller
{
    public function show($id)
    {
        $exhibition = Exhibition::with('booths')->findOrFail($id);
        
        // If user is authenticated, use exhibitor layout, otherwise use public layout
        if (auth()->check()) {
            return view('frontend.floorplan.show', compact('exhibition'));
        } else {
            return view('frontend.floorplan.public', compact('exhibition'));
        }
    }

    public function requestMerge(Request $request, $exhibitionId)
    {
        $request->validate([
            'booth_ids' => 'required|array|min:2',
            'booth_ids.*' => 'exists:booths,id',
            'new_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Verify booths belong to exhibition and are available
        $booths = Booth::whereIn('id', $request->booth_ids)
            ->where('exhibition_id', $exhibitionId)
            ->where('is_booked', false)
            ->get();

        if ($booths->count() < 2) {
            return back()->with('error', 'At least 2 available booths required for merging');
        }

        // Create merge request
        BoothRequest::create([
            'exhibition_id' => $exhibitionId,
            'user_id' => Auth::id(),
            'request_type' => 'merge',
            'booth_ids' => $request->booth_ids,
            'description' => $request->description,
            'request_data' => [
                'new_name' => $request->new_name,
            ],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Merge request submitted. Waiting for admin approval.');
    }

    public function requestSplit(Request $request, $exhibitionId, $boothId)
    {
        $booth = Booth::where('exhibition_id', $exhibitionId)
            ->where('is_booked', false)
            ->findOrFail($boothId);

        $request->validate([
            'split_count' => 'required|integer|min:2|max:4',
            'new_names' => 'required|array|size:' . $request->split_count,
            'new_names.*' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create split request
        BoothRequest::create([
            'exhibition_id' => $exhibitionId,
            'user_id' => Auth::id(),
            'request_type' => 'split',
            'booth_ids' => [$boothId],
            'description' => $request->description,
            'request_data' => [
                'split_count' => $request->split_count,
                'new_names' => $request->new_names,
            ],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Split request submitted. Waiting for admin approval.');
    }
}
