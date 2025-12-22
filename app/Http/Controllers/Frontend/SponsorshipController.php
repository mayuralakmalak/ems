<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use App\Models\Exhibition;
use App\Models\Booking;
use App\Models\SponsorshipBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            ->orderBy('display_order')
            ->orderBy('price')
            ->get();
        
        // If no sponsorships exist, create default ones
        if ($sponsorships->isEmpty()) {
            $sponsorships = $this->createDefaultSponsorships($exhibitionId);
        }
        
        return view('frontend.sponsorships.index', compact('exhibition', 'sponsorships'));
    }
    
    public function show($id)
    {
        $sponsorship = Sponsorship::with('exhibition')->findOrFail($id);
        
        // Check if user has a booking for this exhibition
        $booking = Booking::where('user_id', auth()->id())
            ->where('exhibition_id', $sponsorship->exhibition_id)
            ->where('status', 'confirmed')
            ->first();
        
        return view('frontend.sponsorships.show', compact('sponsorship', 'booking'));
    }
    
    public function book(Request $request, $id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        
        // Check if sponsorship is available
        if (!$sponsorship->isAvailable()) {
            return back()->with('error', 'This sponsorship package is no longer available.');
        }
        
        // Get user's booking for this exhibition (optional - sponsorships can be standalone)
        $booking = Booking::where('user_id', auth()->id())
            ->where('exhibition_id', $sponsorship->exhibition_id)
            ->where('status', 'confirmed')
            ->first();
        
        return view('frontend.sponsorships.book', compact('sponsorship', 'booking'));
    }
    
    public function store(Request $request, $id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        
        // Check if sponsorship is available
        if (!$sponsorship->isAvailable()) {
            return back()->with('error', 'This sponsorship package is no longer available.');
        }
        
        // Handle textarea input for emails and numbers
        $contactEmails = [];
        if ($request->has('contact_emails_text')) {
            $emailsText = $request->input('contact_emails_text');
            $contactEmails = array_filter(array_map('trim', explode("\n", $emailsText)));
        } elseif ($request->has('contact_emails')) {
            $contactEmails = is_array($request->contact_emails) ? $request->contact_emails : json_decode($request->contact_emails, true) ?? [];
        }
        
        $contactNumbers = [];
        if ($request->has('contact_numbers_text')) {
            $numbersText = $request->input('contact_numbers_text');
            $contactNumbers = array_filter(array_map('trim', explode("\n", $numbersText)));
        } elseif ($request->has('contact_numbers')) {
            $contactNumbers = is_array($request->contact_numbers) ? $request->contact_numbers : json_decode($request->contact_numbers, true) ?? [];
        }
        
        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);
        
        // Validate emails
        if (empty($contactEmails) || count($contactEmails) > 5) {
            return back()->withErrors(['contact_emails' => 'Please provide 1-5 email addresses.'])->withInput();
        }
        foreach ($contactEmails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->withErrors(['contact_emails' => 'Invalid email address: ' . $email])->withInput();
            }
        }
        
        // Validate numbers
        if (empty($contactNumbers) || count($contactNumbers) > 5) {
            return back()->withErrors(['contact_numbers' => 'Please provide 1-5 phone numbers.'])->withInput();
        }
        
        $validated['contact_emails'] = array_values($contactEmails);
        $validated['contact_numbers'] = array_values($contactNumbers);
        
        DB::beginTransaction();
        try {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('sponsorship-logos', 'public');
            }
            
            // Generate unique booking number
            $bookingNumber = 'SP' . now()->format('YmdHis') . str_pad(SponsorshipBooking::count() + 1, 6, '0', STR_PAD_LEFT);
            
            // Create sponsorship booking
            $sponsorshipBooking = SponsorshipBooking::create([
                'sponsorship_id' => $sponsorship->id,
                'booking_id' => $validated['booking_id'] ?? null,
                'user_id' => auth()->id(),
                'exhibition_id' => $sponsorship->exhibition_id,
                'booking_number' => $bookingNumber,
                'amount' => $sponsorship->price,
                'paid_amount' => 0,
                'status' => 'pending',
                'payment_status' => 'pending',
                'approval_status' => 'pending',
                'contact_emails' => $validated['contact_emails'],
                'contact_numbers' => $validated['contact_numbers'],
                'logo' => $logoPath,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // Update sponsorship current count
            $sponsorship->increment('current_count');
            
            DB::commit();
            
            return redirect()->route('sponsorships.payment', $sponsorshipBooking->id)
                ->with('success', 'Sponsorship booking created successfully. Please proceed to payment.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create sponsorship booking: ' . $e->getMessage())->withInput();
        }
    }
    
    public function myBookings()
    {
        $bookings = SponsorshipBooking::where('user_id', auth()->id())
            ->with(['sponsorship', 'exhibition', 'payments'])
            ->latest()
            ->paginate(15);
        
        return view('frontend.sponsorships.my-bookings', compact('bookings'));
    }
    
    public function showBooking($id)
    {
        $booking = SponsorshipBooking::where('user_id', auth()->id())
            ->with(['sponsorship', 'exhibition', 'payments'])
            ->findOrFail($id);
        
        return view('frontend.sponsorships.booking-details', compact('booking'));
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

