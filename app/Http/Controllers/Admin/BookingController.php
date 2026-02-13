<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Document;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Badge;
use App\Mail\DocumentStatusMail;
use App\Mail\CancellationProcessedMail;
use App\Mail\PossessionLetterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


class BookingController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $query = Booking::with(['exhibition', 'booth', 'user', 'payments']);

        // Exhibition filter
        if ($request->filled('exhibition_id')) {
            $query->where('exhibition_id', $request->get('exhibition_id'));
        }

        // Status filter (blank or "all" means no filter)
        $status = $request->get('status');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // User filter (by name or email, partial match)
        if ($request->filled('user_name')) {
            $userSearch = $request->get('user_name');
            $query->whereHas('user', function ($q) use ($userSearch) {
                $q->where('name', 'like', '%' . $userSearch . '%')
                    ->orWhere('email', 'like', '%' . $userSearch . '%');
            });
        }

        // Booth number filter (by booth name / number)
        if ($request->filled('booth_number')) {
            $boothNumber = $request->get('booth_number');

            $boothIds = Booth::where('name', 'like', '%' . $boothNumber . '%')
                ->pluck('id')
                ->all();

            if (!empty($boothIds)) {
                $query->where(function ($q) use ($boothIds) {
                    // Match primary booth_id
                    $q->whereIn('booth_id', $boothIds);

                    // Match any ID inside selected_booth_ids JSON (supports both simple and object formats)
                    foreach ($boothIds as $boothId) {
                        $q->orWhereJsonContains('selected_booth_ids', $boothId)
                          ->orWhereJsonContains('selected_booth_ids->id', $boothId);
                    }
                });
            } else {
                // No booths matched the search term, force empty result
                $query->whereRaw('1 = 0');
            }
        }

        $exhibitions = Exhibition::orderBy('name')->get();
        $availableStatuses = ['pending', 'confirmed', 'cancelled', 'replaced'];

        // Export branch: when export=1, return CSV for current filters
        if ($request->get('export') === '1') {
            abort_unless(auth()->user()->can('Booking Management - Download'), 403);
            $bookings = $query->latest()->get();
            return $this->exportBookings($bookings);
        }

        $bookings = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.bookings.index', compact('bookings', 'exhibitions', 'availableStatuses'));
    }

    /**
     * Export filtered (or all) bookings as CSV.
     */
    private function exportBookings($bookings)
    {
        $fileName = 'bookings-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'Booking #',
                'Exhibition',
                'User',
                'User Email',
                'Booths',
                'Status',
                'Approval Status',
                'Total Amount',
                'Paid Amount',
                'Created At',
            ]);

            foreach ($bookings as $booking) {
                // Build booth names list (supports multi-booth bookings)
                $boothEntries = collect($booking->selected_booth_ids ?? []);

                if ($boothEntries->isEmpty() && $booking->booth_id) {
                    // Fallback to primary booth if no selected_booth_ids
                    $boothEntries = collect([[
                        'id' => $booking->booth_id,
                        'name' => optional($booking->booth)->name,
                    ]]);
                }

                $boothIds = $boothEntries->map(function ($entry) {
                    return is_array($entry) ? ($entry['id'] ?? null) : $entry;
                })->filter()->values();

                $booths = Booth::whereIn('id', $boothIds)->get()->keyBy('id');

                $boothNames = $boothEntries->map(function ($entry) use ($booths) {
                    $isArray = is_array($entry);
                    $id = $isArray ? ($entry['id'] ?? null) : $entry;
                    $model = $id ? ($booths[$id] ?? null) : null;
                    return $isArray
                        ? ($entry['name'] ?? optional($model)->name ?? 'N/A')
                        : (optional($model)->name ?? 'N/A');
                })->filter(function ($name) {
                    return $name !== 'N/A';
                })->implode(', ');

                fputcsv($handle, [
                    $booking->booking_number,
                    optional($booking->exhibition)->name,
                    optional($booking->user)->name,
                    optional($booking->user)->email,
                    $boothNames,
                    ucfirst($booking->status),
                    $booking->approval_status ? ucfirst($booking->approval_status) : '',
                    $booking->total_amount,
                    $booking->paid_amount,
                    optional($booking->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user'])->findOrFail($id);
        $statuses = ['pending', 'confirmed', 'cancelled', 'replaced'];

        return view('admin.bookings.edit', compact('booking', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with('booth')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,replaced',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0|max:' . $request->total_amount,
        ]);

        $oldStatus = $booking->status;
        $booking->update([
            'status' => $request->status,
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
        ]);

        // Notify exhibitor if status changed to confirmed or rejected
        if ($oldStatus !== $request->status) {
            if ($request->status === 'confirmed') {
                \App\Models\Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking',
                    'title' => 'Booking Approved',
                    'message' => 'Your booking request #' . $booking->booking_number . ' has been approved.',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);
            } elseif ($request->status === 'cancelled') {
                \App\Models\Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking',
                    'title' => 'Booking Cancelled',
                    'message' => 'Your booking #' . $booking->booking_number . ' has been cancelled.',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);
            }
        }

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully.');
    }

    public function bookedByExhibition($exhibitionId)
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $bookings = Booking::with(['exhibition', 'booth', 'user'])
            ->where('exhibition_id', $exhibitionId)
            ->latest()
            ->paginate(20);

        return view('admin.bookings.booked-booths', compact('bookings', 'exhibitionId'));
    }
    
    public function cancellations()
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $cancellationRequests = Booking::with(['exhibition', 'booth', 'user', 'payments'])
            ->where('status', 'cancelled')
            ->orWhereNotNull('cancellation_reason')
            ->latest()
            ->get();
        
        // Calculate statistics
        $totalBookings = Booking::count();
        $pendingCancellations = Booking::where('status', 'cancelled')
            ->whereNull('cancellation_type')
            ->count();
        $approvedRefunds = Booking::where('cancellation_type', 'refund')
            ->sum('cancellation_amount');
        $cancellationCharges = Booking::whereNotNull('cancellation_amount')
            ->sum('cancellation_amount');
        
        return view('admin.bookings.cancellations', compact(
            'cancellationRequests',
            'totalBookings',
            'pendingCancellations',
            'approvedRefunds',
            'cancellationCharges'
        ));
    }
    
    public function manageCancellation($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user', 'payments', 'documents'])
            ->findOrFail($id);
        
        // Calculate cancellation charges (15% of total)
        $cancellationCharge = ($booking->total_amount * 15) / 100;
        $refundAmount = $booking->total_amount - $cancellationCharge;
        
        return view('admin.bookings.manage-cancellation', compact('booking', 'cancellationCharge', 'refundAmount'));
    }
    
    public function approveCancellation(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['user', 'booth'])->findOrFail($id);
        
        $request->validate([
            'cancellation_type' => 'required|in:refund,wallet_credit',
            'account_details' => 'required_if:cancellation_type,refund|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            $cancellationCharge = ($booking->total_amount * 15) / 100;
            $refundAmount = $booking->total_amount - $cancellationCharge;
            
            $booking->update([
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $cancellationCharge,
                'account_details' => $request->account_details,
                'status' => 'cancelled',
            ]);
            
            // Process refund or wallet credit
            if ($request->cancellation_type === 'wallet_credit') {
                Wallet::create([
                    'user_id' => $booking->user_id,
                    'balance' => ($booking->user->wallet_balance ?? 0) + $refundAmount,
                    'transaction_type' => 'credit',
                    'amount' => $refundAmount,
                    'reference_type' => 'booking_cancellation',
                    'reference_id' => $booking->id,
                    'description' => 'Cancellation credit for booking #' . $booking->booking_number,
                ]);
            }
            
            // Free up the booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.bookings.cancellations')
                ->with('success', 'Cancellation approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve cancellation: ' . $e->getMessage());
        }
    }
    
    public function rejectCancellation(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $booking->update([
            'status' => 'confirmed',
            'cancellation_reason' => null,
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        return redirect()->route('admin.bookings.cancellations')
            ->with('success', 'Cancellation rejected.');
    }

    public function show($id)
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user', 'payments', 'documents', 'badges', 'bookingServices.service', 'additionalServiceRequests.service', 'additionalServiceRequests.approver'])
            ->findOrFail($id);
        
        return view('admin.bookings.show', compact('booking'));
    }

    public function processCancellation(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['user', 'booth', 'exhibition'])->findOrFail($id);
        
        $request->validate([
            'cancellation_type' => 'required|in:refund,wallet_credit',
            'cancellation_amount' => 'required|numeric|min:0|max:' . $booking->total_amount,
            'account_details' => 'required_if:cancellation_type,refund|nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update booking
            $booking->update([
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $request->cancellation_amount, // amount refunded/credited
                'account_details' => $request->account_details,
                'status' => 'cancelled',
            ]);

            // Process refund or wallet credit
            if ($request->cancellation_type === 'wallet_credit') {
                // Credit to wallet
                Wallet::create([
                    'user_id' => $booking->user_id,
                    'balance' => ($booking->user->wallet_balance ?? 0) + $request->cancellation_amount,
                    'transaction_type' => 'credit',
                    'amount' => $request->cancellation_amount,
                    'reference_type' => 'booking_cancellation',
                    'reference_id' => $booking->id,
                    'description' => 'Cancellation credit for booking #' . $booking->booking_number,
                ]);
            }

            // Free up ALL booths associated with this booking
            // 1. Free primary booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            // 2. Free all booths from selected_booth_ids (for merged/multiple booth bookings)
            $rawSelectedBoothIds = $booking->selected_booth_ids;
            if ($rawSelectedBoothIds) {
                $selectedBoothIds = [];

                // Work on a local array copy to avoid \"Indirect modification\" on casted attributes
                if (is_array($rawSelectedBoothIds)) {
                    // Handle array format: [{'id': 1, 'name': 'B001'}, ...] OR [1,2,3]
                    $firstItem = reset($rawSelectedBoothIds);
                    if (is_array($firstItem) && isset($firstItem['id'])) {
                        // Array of objects format - extract IDs
                        $selectedBoothIds = collect($rawSelectedBoothIds)
                            ->pluck('id')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                    } else {
                        // Simple array format: [1, 2, 3] - use directly
                        $selectedBoothIds = collect($rawSelectedBoothIds)
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                    }
                }
                
                // Free all selected booths
                if (!empty($selectedBoothIds)) {
                    \App\Models\Booth::whereIn('id', $selectedBoothIds)
                        ->where('exhibition_id', $booking->exhibition_id)
                        ->update([
                            'is_available' => true,
                            'is_booked' => false,
                        ]);
                }
            }

            DB::commit();

            // Send cancellation processed email to exhibitor
            try {
                if ($booking->user && $booking->user->email) {
                    Mail::to($booking->user->email)->send(new CancellationProcessedMail($booking));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send cancellation processed email: ' . $e->getMessage());
            }

            // Log successful cancellation
            \Log::info('Cancellation processed successfully', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $request->cancellation_amount,
            ]);

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Cancellation processed successfully. Booth(s) have been freed and made available for new bookings.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to process cancellation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to process cancellation: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Delete'), 403);
        $booking = Booking::with('booth')->findOrFail($id);

        DB::transaction(function () use ($booking) {
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }
            $booking->delete();
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function approveDocument($documentId)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $document = Document::with(['booking', 'user', 'booking.exhibition', 'requiredDocument'])->findOrFail($documentId);
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Approved',
            'message' => 'Your document "' . $document->name . '" has been approved.',
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);

        // Send approval email to exhibitor
        try {
            if ($document->user && $document->user->email) {
                Mail::to($document->user->email)->send(
                    new DocumentStatusMail($document, 'approved', null)
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document approval email: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'user_email' => $document->user->email ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Document approved.');
    }

    public function rejectDocument(Request $request, $documentId)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $document = Document::with(['booking', 'user', 'booking.exhibition', 'requiredDocument'])->findOrFail($documentId);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Rejected',
            'message' => 'Your document "' . $document->name . '" has been rejected. Reason: ' . $request->rejection_reason,
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);

        // Send rejection email to exhibitor
        try {
            if ($document->user && $document->user->email) {
                Mail::to($document->user->email)->send(
                    new DocumentStatusMail($document, 'rejected', $request->rejection_reason)
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document rejection email: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'user_email' => $document->user->email ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Document rejected with reason recorded.');
    }

    /**
     * Approve a badge generated by an exhibitor.
     */
    public function approveBadge($badgeId)
    {
        abort_unless(auth()->user()->can('Badge Management - Modify'), 403);
        $badge = Badge::with(['booking', 'user', 'exhibition'])->findOrFail($badgeId);

        $badge->update([
            'status' => 'approved',
        ]);

        // Notify exhibitor that their badge has been approved
        if ($badge->user_id) {
            Notification::create([
                'user_id' => $badge->user_id,
                'type' => 'badge',
                'title' => 'Badge Approved',
                'message' => 'Your badge "' . ($badge->name ?? 'Staff') . '" for booking #' . ($badge->booking->booking_number ?? '') . ' has been approved.',
                'notifiable_type' => Badge::class,
                'notifiable_id' => $badge->id,
            ]);
        }

        return back()->with('success', 'Badge approved successfully.');
    }

    /**
     * Generate and send possession letter to exhibitor
     */
    public function generatePossessionLetter($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Download'), 403);
        $booking = Booking::with([
            'exhibition', 
            'booth', 
            'user', 
            'payments',
            'bookingServices.service'
        ])->findOrFail($id);

        // Check if booking is fully paid
        if (!$booking->isFullyPaid() || !$booking->areAllPaymentsCompleted()) {
            return back()->with('error', 'Cannot generate possession letter. All payments must be completed and approved.');
        }

        // Check if booking is approved
        if ($booking->approval_status !== 'approved' || $booking->status !== 'confirmed') {
            return back()->with('error', 'Cannot generate possession letter. Booking must be approved and confirmed.');
        }

        DB::beginTransaction();
        try {
            // Generate PDF
            $pdfPath = $this->generatePossessionLetterPDF($booking);

            // Mark possession letter as issued
            $booking->update([
                'possession_letter_issued' => true,
            ]);

            // Store the PDF path in documents table
            Document::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'name' => 'Possession Letter - ' . $booking->booking_number,
                'type' => 'Possession Letter',
                'file_path' => $pdfPath,
                'file_size' => Storage::disk('public')->size($pdfPath),
                'status' => 'approved',
            ]);

            // Send email to exhibitor
            try {
                Mail::to($booking->user->email)->send(new PossessionLetterMail($booking, $pdfPath));
                
                // Also send to contact emails if provided
                if ($booking->contact_emails && is_array($booking->contact_emails)) {
                    foreach ($booking->contact_emails as $email) {
                        if ($email && $email !== $booking->user->email) {
                            try {
                                Mail::to($email)->send(new PossessionLetterMail($booking, $pdfPath));
                            } catch (\Exception $e) {
                                Log::error('Failed to send possession letter to contact email: ' . $email, [
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send possession letter email: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'user_email' => $booking->user->email,
                ]);
            }

            // Notify exhibitor
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking',
                'title' => 'Possession Letter Generated',
                'message' => 'Your possession letter for booking #' . $booking->booking_number . ' has been generated and sent to your email.',
                'notifiable_type' => Booking::class,
                'notifiable_id' => $booking->id,
            ]);

            DB::commit();

            return back()->with('success', 'Possession letter generated and sent to exhibitor successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate possession letter: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to generate possession letter: ' . $e->getMessage());
        }
    }

    /**
     * Generate possession letter PDF
     */
    private function generatePossessionLetterPDF(Booking $booking)
    {
        $html = view('admin.bookings.possession-letter-pdf', compact('booking'))->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf-temp'),
            'format' => 'A4',
            'margin_top' => 20,
            'margin_right' => 15,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'fontDir' => array_merge($fontDirs, [
                resource_path('fonts'),
            ]),
            'fontdata' => $fontData,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->SetTitle('Possession Letter - ' . $booking->booking_number);
        $mpdf->WriteHTML($html);

        // Save PDF to storage
        $filename = 'possession-letters/booking_' . $booking->id . '_' . $booking->booking_number . '_' . now()->format('YmdHis') . '.pdf';
        $pdfContent = $mpdf->Output('', 'S');
        
        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }

    /**
     * Download possession letter (Admin)
     */
    public function downloadPossessionLetter($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Download'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user'])->findOrFail($id);

        if (!$booking->possession_letter_issued) {
            return back()->with('error', 'Possession letter has not been generated yet.');
        }

        // Find the possession letter document
        $document = Document::where('booking_id', $booking->id)
            ->where('type', 'Possession Letter')
            ->latest()
            ->first();

        if (!$document || !Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Possession letter file not found.');
        }

        return Storage::disk('public')->download($document->file_path, 'Possession_Letter_' . $booking->booking_number . '.pdf');
    }
}

