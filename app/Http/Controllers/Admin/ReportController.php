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
use App\Models\AdminException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    /**
     * Show exception report page with exhibition and client selection
     */
    public function exceptionReport(Request $request)
    {
        // Get exhibitions that have ended
        $exhibitions = Exhibition::where('end_date', '<', Carbon::now())
            ->orderBy('end_date', 'desc')
            ->get();

        $selectedExhibitionId = $request->get('exhibition_id');
        $selectedClientIds = $request->get('client_ids', []);

        // Handle array format from form submission
        if (is_string($selectedClientIds)) {
            $selectedClientIds = [$selectedClientIds];
        } elseif (!is_array($selectedClientIds)) {
            $selectedClientIds = [];
        }

        $exceptions = collect();
        $clients = collect();

        if ($selectedExhibitionId) {
            $exhibition = Exhibition::findOrFail($selectedExhibitionId);

            // Validate that exhibition has ended
            if ($exhibition->end_date >= Carbon::now()) {
                return redirect()->route('admin.reports.exception')
                    ->with('error', 'Exception report can only be generated after the exhibition end date.');
            }

            // Get all clients (users) who have bookings for this exhibition
            $clients = User::whereHas('bookings', function ($query) use ($selectedExhibitionId) {
                $query->where('exhibition_id', $selectedExhibitionId);
            })
            ->with(['bookings' => function ($query) use ($selectedExhibitionId) {
                $query->where('exhibition_id', $selectedExhibitionId);
            }])
            ->get();

            // Get exceptions for selected clients or all clients if none selected
            $exceptionQuery = AdminException::with(['user', 'booking', 'exhibition', 'createdBy'])
                ->where('exhibition_id', $selectedExhibitionId);

            if (!empty($selectedClientIds)) {
                $exceptionQuery->whereIn('user_id', $selectedClientIds);
            }

            $exceptions = $exceptionQuery->orderBy('created_at', 'desc')->get();
        }

        return view('admin.reports.exception', compact(
            'exhibitions',
            'selectedExhibitionId',
            'clients',
            'selectedClientIds',
            'exceptions'
        ));
    }

    /**
     * Generate and download exception report
     */
    public function generateExceptionReport(Request $request)
    {
        $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'client_ids' => 'nullable|array',
            'client_ids.*' => 'exists:users,id',
            'format' => 'nullable|in:pdf,excel',
        ]);

        $exhibition = Exhibition::findOrFail($request->exhibition_id);

        // Validate that exhibition has ended
        if ($exhibition->end_date >= Carbon::now()) {
            return back()->with('error', 'Exception report can only be generated after the exhibition end date.');
        }

        $exceptionQuery = AdminException::with(['user', 'booking', 'exhibition', 'createdBy'])
            ->where('exhibition_id', $request->exhibition_id);

        if (!empty($request->client_ids)) {
            $exceptionQuery->whereIn('user_id', $request->client_ids);
        }

        $exceptions = $exceptionQuery->orderBy('user_id')->orderBy('created_at', 'desc')->get();

        // Group exceptions by client
        $groupedExceptions = $exceptions->groupBy('user_id');

        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            // TODO: Implement Excel export if needed
            return back()->with('info', 'Excel export coming soon. Please use PDF format.');
        }

        // Generate PDF report
        return $this->generatePdfReport($exhibition, $groupedExceptions, $exceptions);
    }

    /**
     * Generate PDF report for exceptions
     */
    private function generatePdfReport($exhibition, $groupedExceptions, $exceptions)
    {
        // For now, return a view that can be printed as PDF
        // In production, you might want to use a package like dompdf or barryvdh/laravel-dompdf
        return view('admin.reports.exception-pdf', compact('exhibition', 'groupedExceptions', 'exceptions'));
    }
}
