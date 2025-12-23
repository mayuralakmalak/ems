<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Badge;
use App\Models\Booth;
use App\Models\Exhibition;
use App\Models\Payment;
use App\Models\Service;
use App\Models\BookingService;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Core booking stats
        $bookingCount = $bookings->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();

        // Financial stats (completed payments)
        $financialTotal = $payments->where('status', 'completed')->sum('amount');

        // Service usage (how many times each additional service was booked)
        $serviceUsage = Service::selectRaw('services.name, COUNT(booking_services.id) as usage_count')
            ->join('booking_services', 'services.id', '=', 'booking_services.service_id')
            ->groupBy('services.name')
            ->get();

        // --- Space utilization: total booths vs booked booths ---
        $boothBaseQuery = Booth::query();
        if ($selectedExhibitionId) {
            $boothBaseQuery->where('exhibition_id', $selectedExhibitionId);
        }

        $totalBooths = (clone $boothBaseQuery)->count();
        $bookedBooths = (clone $boothBaseQuery)->where('is_booked', true)->count();
        $spaceUtilization = $totalBooths > 0
            ? round(($bookedBooths / $totalBooths) * 100, 1)
            : 0;

        // --- Additional services booked (BookingService) ---
        $bookingServiceBaseQuery = BookingService::query();
        if ($selectedExhibitionId) {
            $bookingServiceBaseQuery->whereHas('booking', function ($q) use ($selectedExhibitionId) {
                $q->where('exhibition_id', $selectedExhibitionId);
            });
        }

        $totalAdditionalServices = (clone $bookingServiceBaseQuery)->count();
        $additionalServicesRevenue = (clone $bookingServiceBaseQuery)->sum('total_price');

        // --- Extra items bundled with booth (included_item_extras JSON on Booking) ---
        $extraItemsCount = 0;
        $extraItemsRevenue = 0.0;

        $bookings->each(function (Booking $booking) use (&$extraItemsCount, &$extraItemsRevenue) {
            $extras = $booking->included_item_extras ?? [];
            if (!is_array($extras)) {
                return;
            }

            foreach ($extras as $extra) {
                $quantity = isset($extra['quantity']) ? (int) $extra['quantity'] : 1;
                $totalPrice = isset($extra['total_price']) ? (float) $extra['total_price'] : 0.0;

                $extraItemsCount += $quantity;
                $extraItemsRevenue += $totalPrice;
            }
        });

        // --- Document verification ratio ---
        $documentBaseQuery = Document::query();
        if ($selectedExhibitionId) {
            $documentBaseQuery->whereHas('booking', function ($q) use ($selectedExhibitionId) {
                $q->where('exhibition_id', $selectedExhibitionId);
            });
        }

        $totalDocuments = (clone $documentBaseQuery)->count();
        $approvedDocuments = (clone $documentBaseQuery)->where('status', 'approved')->count();
        $rejectedDocuments = (clone $documentBaseQuery)->where('status', 'rejected')->count();
        $pendingDocuments = (clone $documentBaseQuery)->where('status', 'pending')->count();

        $documentVerificationRatio = $totalDocuments > 0
            ? round(($approvedDocuments / $totalDocuments) * 100, 1)
            : 0;

        // --- Badges (including additional badges) ---
        $badgeBaseQuery = Badge::query();
        if ($selectedExhibitionId) {
            $badgeBaseQuery->where('exhibition_id', $selectedExhibitionId);
        }

        $totalBadges = (clone $badgeBaseQuery)->count();
        $paidBadges = (clone $badgeBaseQuery)->where('is_paid', true)->count();
        $unpaidBadges = $totalBadges - $paidBadges;
        $avgBadgesPerBooking = $bookingCount > 0
            ? round($totalBadges / $bookingCount, 1)
            : 0;

        // --- Popular booth sizes (sqft) by bookings ---
        $popularSizesQuery = Booth::select(
                'booths.size_sqft',
                DB::raw('COUNT(bookings.id) as bookings_count')
            )
            ->join('bookings', 'bookings.booth_id', '=', 'booths.id')
            ->where('bookings.status', 'confirmed');

        if ($selectedExhibitionId) {
            $popularSizesQuery->where('booths.exhibition_id', $selectedExhibitionId);
        }

        $popularSizes = $popularSizesQuery
            ->groupBy('booths.size_sqft')
            ->orderByDesc('bookings_count')
            ->get();

        // --- Popular booth categories by bookings ---
        $popularCategoriesQuery = Booth::select(
                'booths.category',
                DB::raw('COUNT(bookings.id) as bookings_count')
            )
            ->join('bookings', 'bookings.booth_id', '=', 'booths.id')
            ->where('bookings.status', 'confirmed');

        if ($selectedExhibitionId) {
            $popularCategoriesQuery->where('booths.exhibition_id', $selectedExhibitionId);
        }

        $popularCategories = $popularCategoriesQuery
            ->groupBy('booths.category')
            ->orderByDesc('bookings_count')
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
            'serviceUsage',
            'totalBooths',
            'bookedBooths',
            'spaceUtilization',
            'totalAdditionalServices',
            'additionalServicesRevenue',
            'extraItemsCount',
            'extraItemsRevenue',
            'totalDocuments',
            'approvedDocuments',
            'rejectedDocuments',
            'pendingDocuments',
            'documentVerificationRatio',
            'totalBadges',
            'paidBadges',
            'unpaidBadges',
            'avgBadgesPerBooking',
            'popularSizes',
            'popularCategories'
        ));
    }
}
