<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $exhibitions = Exhibition::orderBy('start_date', 'desc')->get();
        $selectedExhibitionId = $request->get('exhibition_id');

        $bookingQuery = Booking::with(['exhibition', 'booth', 'user']);
        $paymentQuery = Payment::with(['booking', 'user']);

        if ($selectedExhibitionId) {
            $bookingQuery->where('exhibition_id', $selectedExhibitionId);
            $paymentQuery->whereHas('booking', function ($q) use ($selectedExhibitionId) {
                $q->where('exhibition_id', $selectedExhibitionId);
            });
        }

        $bookings = $bookingQuery->get();
        $payments = $paymentQuery->get();

        $bookingCount = $bookings->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();

        $financialTotal = $payments->where('status', 'completed')->sum('amount');

        $serviceUsage = Service::selectRaw('services.name, COUNT(booking_services.id) as usage_count')
            ->join('booking_services', 'services.id', '=', 'booking_services.service_id')
            ->groupBy('services.name')
            ->get();

        return view('admin.reports.index', compact(
            'exhibitions',
            'selectedExhibitionId',
            'bookings',
            'payments',
            'bookingCount',
            'confirmedBookings',
            'cancelledBookings',
            'financialTotal',
            'serviceUsage'
        ));
    }
}
