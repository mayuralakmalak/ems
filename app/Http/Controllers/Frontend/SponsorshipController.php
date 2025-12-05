<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use App\Models\Exhibition;
use App\Models\Booking;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    public function index(Request $request)
    {
        $exhibitionId = $request->query('exhibition');
        
        if (!$exhibitionId) {
            // Get user's confirmed bookings to select exhibition
            $bookings = Booking::where('user_id', auth()->id())
                ->where('status', 'confirmed')
                ->with('exhibition')
                ->get();
            
            if ($bookings->isEmpty()) {
                return redirect()->route('bookings.index')
                    ->with('error', 'Please book an exhibition first to access sponsorship opportunities.');
            }
            
            $exhibitionId = $bookings->first()->exhibition_id;
        }
        
        $exhibition = Exhibition::findOrFail($exhibitionId);
        
        // Get sponsorships grouped by tier
        $sponsorships = Sponsorship::where('exhibition_id', $exhibitionId)
            ->where('is_active', true)
            ->get();
        
        // If no sponsorships exist, create default ones
        if ($sponsorships->isEmpty()) {
            $sponsorships = $this->createDefaultSponsorships($exhibitionId);
        }
        
        return view('frontend.sponsorships.index', compact('exhibition', 'sponsorships'));
    }
    
    public function select(Request $request, $id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        
        // Get user's booking for this exhibition
        $booking = Booking::where('user_id', auth()->id())
            ->where('exhibition_id', $sponsorship->exhibition_id)
            ->where('status', 'confirmed')
            ->first();
        
        if (!$booking) {
            return back()->with('error', 'No confirmed booking found for this exhibition.');
        }
        
        // Create sponsorship booking
        \App\Models\SponsorshipBooking::create([
            'booking_id' => $booking->id,
            'sponsorship_id' => $sponsorship->id,
            'user_id' => auth()->id(),
            'exhibition_id' => $sponsorship->exhibition_id,
            'status' => 'pending',
            'amount' => $sponsorship->price,
        ]);
        
        return redirect()->route('payments.create', $booking->id)
            ->with('success', 'Sponsorship package selected. Proceed to payment.');
    }
    
    private function createDefaultSponsorships($exhibitionId)
    {
        $defaults = [
            [
                'name' => 'Bronze Tier Sponsorship',
                'tier' => 'Bronze',
                'price' => 500,
                'description' => 'Basic sponsorship package with essential benefits',
                'deliverables' => [
                    'Logo placement on website',
                    'Social media mention (1x)',
                    'Event mention in newsletter'
                ],
            ],
            [
                'name' => 'Silver Tier Sponsorship',
                'tier' => 'Silver',
                'price' => 1200,
                'description' => 'Enhanced sponsorship package with increased visibility',
                'deliverables' => [
                    'Prominent logo on website',
                    'Social media campaign (3x)',
                    'Dedicated newsletter section',
                    'Exhibitor booth access'
                ],
            ],
            [
                'name' => 'Gold Tier Sponsorship',
                'tier' => 'Gold',
                'price' => 2500,
                'description' => 'Premium sponsorship package with maximum exposure',
                'deliverables' => [
                    'Premier logo placement',
                    'Extensive social media campaign',
                    'Full-page newsletter feature',
                    'Keynote session mention',
                    'VIP networking event access'
                ],
            ],
        ];
        
        $created = [];
        foreach ($defaults as $data) {
            $created[] = Sponsorship::create([
                'exhibition_id' => $exhibitionId,
                'name' => $data['name'],
                'tier' => $data['tier'],
                'price' => $data['price'],
                'description' => $data['description'],
                'deliverables' => $data['deliverables'],
                'is_active' => true,
            ]);
        }
        
        return collect($created);
    }
}

