<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Exhibition;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ServiceController extends Controller
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
                    ->with('error', 'Please book an exhibition first to access additional services.');
            }
            
            $exhibitionId = $bookings->first()->exhibition_id;
        }
        
        $exhibition = Exhibition::findOrFail($exhibitionId);
        
        // Get services grouped by category/type
        $services = Service::where('exhibition_id', $exhibitionId)
            ->where('is_active', true)
            ->get()
            ->groupBy('type');
        
        // Get cart from session
        $cart = Session::get('service_cart', []);
        $cartItems = [];
        $cartTotal = 0;
        
        foreach ($cart as $serviceId => $quantity) {
            $service = Service::find($serviceId);
            if ($service && $service->exhibition_id == $exhibitionId) {
                $cartItems[] = [
                    'service' => $service,
                    'quantity' => $quantity,
                    'total' => $service->price * $quantity
                ];
                $cartTotal += $service->price * $quantity;
            }
        }
        
        return view('frontend.services.index', compact('exhibition', 'services', 'cartItems', 'cartTotal'));
    }
    
    public function addToCart(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $service = Service::findOrFail($request->service_id);
        
        // Check if service is active
        if (!$service->is_active) {
            return response()->json(['success' => false, 'message' => 'Service is not available']);
        }
        
        // Check cutoff date
        if ($service->exhibition->addon_services_cutoff_date && now() > $service->exhibition->addon_services_cutoff_date) {
            return response()->json(['success' => false, 'message' => 'Service booking cutoff date has passed']);
        }
        
        $cart = Session::get('service_cart', []);
        $cart[$request->service_id] = ($cart[$request->service_id] ?? 0) + $request->quantity;
        Session::put('service_cart', $cart);
        
        // Calculate cart total
        $cartTotal = 0;
        foreach ($cart as $serviceId => $quantity) {
            $s = Service::find($serviceId);
            if ($s) {
                $cartTotal += $s->price * $quantity;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Service added to cart',
            'cart_count' => array_sum($cart),
            'cart_total' => $cartTotal
        ]);
    }
    
    public function updateCart(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:0',
        ]);
        
        $cart = Session::get('service_cart', []);
        
        if ($request->quantity == 0) {
            unset($cart[$request->service_id]);
        } else {
            $cart[$request->service_id] = $request->quantity;
        }
        
        Session::put('service_cart', $cart);
        
        // Calculate cart total
        $cartTotal = 0;
        foreach ($cart as $serviceId => $quantity) {
            $service = Service::find($serviceId);
            if ($service) {
                $cartTotal += $service->price * $quantity;
            }
        }
        
        return response()->json([
            'success' => true,
            'cart_total' => $cartTotal,
            'cart_count' => array_sum($cart)
        ]);
    }
    
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);
        
        $cart = Session::get('service_cart', []);
        unset($cart[$request->service_id]);
        Session::put('service_cart', $cart);
        
        return response()->json(['success' => true, 'message' => 'Service removed from cart']);
    }
    
    public function checkout(Request $request)
    {
        $cart = Session::get('service_cart', []);
        
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }
        
        $exhibitionId = $request->exhibition_id;
        $booking = Booking::where('user_id', auth()->id())
            ->where('exhibition_id', $exhibitionId)
            ->where('status', 'confirmed')
            ->first();
        
        if (!$booking) {
            return back()->with('error', 'No confirmed booking found for this exhibition.');
        }
        
        // Add services to booking
        $totalAmount = 0;
        foreach ($cart as $serviceId => $quantity) {
            $service = Service::find($serviceId);
            if ($service && $service->exhibition_id == $exhibitionId) {
                \App\Models\BookingService::updateOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'unit_price' => $service->price,
                        'total_price' => $service->price * $quantity,
                    ]
                );
                $totalAmount += $service->price * $quantity;
            }
        }
        
        // Update booking total
        $booking->total_amount += $totalAmount;
        $booking->save();
        
        // Clear cart
        Session::forget('service_cart');
        
        return redirect()->route('payments.create', $booking->id)
            ->with('success', 'Services added to booking. Proceed to payment.');
    }
}

