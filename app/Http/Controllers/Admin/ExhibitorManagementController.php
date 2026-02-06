<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Message;
use App\Models\Booth;
use App\Models\Exhibition;
use App\Models\Discount;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ExhibitorManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('Exhibitor')->with(['bookings', 'bookings.exhibition', 'bookings.booth']);

        // General search (name, email, company)
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%');
            });
        }

        // 'industry' column does not exist on users table in current schema,
        // so skip industry-based filtering to avoid SQL errors.

        // Filter by payment status (has any completed payment or not)
        if ($request->has('payment_status') && $request->payment_status) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('bookings.payments', function($q) {
                    $q->where('status', 'completed');
                });
            } else {
                $query->whereDoesntHave('bookings.payments', function($q) {
                    $q->where('status', 'completed');
                });
            }
        }

        // Filter by minimum booth area across bookings
        if ($request->has('booth_area') && $request->booth_area) {
            $query->whereHas('bookings.booth', function($q) use ($request) {
                $q->where('size_sqft', '>=', $request->booth_area);
            });
        }

        // Filter by exhibition (any booking in that exhibition)
        if ($request->filled('exhibition_id')) {
            $exhibitionId = $request->get('exhibition_id');
            $query->whereHas('bookings', function ($q) use ($exhibitionId) {
                $q->where('exhibition_id', $exhibitionId);
            });
        }

        // Export branch: when export=1, return CSV for current filters
        if ($request->get('export') === '1') {
            $exhibitors = $query->latest()->get();
            return $this->exportExhibitors($exhibitors);
        }

        $exhibitors = $query->latest()->paginate(20)->appends($request->query());
        $exhibitions = Exhibition::all();

        return view('admin.exhibitors.index', compact('exhibitors', 'exhibitions'));
    }

    /**
     * Export filtered (or all) exhibitors as CSV.
     */
    private function exportExhibitors($exhibitors)
    {
        $fileName = 'exhibitors-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($exhibitors) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'Name',
                'Company',
                'Email',
                'Phone',
                'Total Bookings',
                'Completed Payments (Count)',
                'Total Paid Amount',
            ]);

            foreach ($exhibitors as $exhibitor) {
                $bookings = $exhibitor->bookings ?? collect();
                $allPayments = $bookings->flatMap(function ($booking) {
                    return $booking->payments ?? collect();
                });

                $completedPayments = $allPayments->where('status', 'completed');
                $completedCount = $completedPayments->count();
                $totalPaid = $completedPayments->sum('amount');

                fputcsv($handle, [
                    $exhibitor->name,
                    $exhibitor->company_name,
                    $exhibitor->email,
                    $exhibitor->phone,
                    $bookings->count(),
                    $completedCount,
                    $totalPaid,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function show($id)
    {
        $exhibitor = User::role('Exhibitor')->with(['bookings.exhibition', 'bookings.booth', 'bookings.payments'])->findOrFail($id);
        $bookings = $exhibitor->bookings()->with(['exhibition', 'booth', 'payments'])->latest()->get();
        $exhibitions = Exhibition::all();
        $booths = Booth::where('is_available', true)->get();
        $messages = Message::where(function ($query) use ($exhibitor) {
                $query->where('sender_id', $exhibitor->id)
                    ->orWhere('receiver_id', $exhibitor->id);
            })
            ->with(['sender', 'receiver', 'exhibition'])
            ->orderBy('created_at')
            ->get();
        // Discounts table no longer has date columns after migration,
        // so simply fetch currently active discounts.
        $discounts = Discount::where('status', 'active')->get();

        return view('admin.exhibitors.show', compact('exhibitor', 'bookings', 'exhibitions', 'booths', 'discounts', 'messages'));
    }

    public function updateContact(Request $request, $id)
    {
        $exhibitor = User::role('Exhibitor')->findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
        ]);

        $exhibitor->update($validated);
        return back()->with('success', 'Exhibitor contact updated successfully.');
    }

    public function updateBooth(Request $request, $id)
    {
        $exhibitor = User::role('Exhibitor')->findOrFail($id);
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'exhibition_id' => 'required|exists:exhibitions,id',
            'booth_id' => 'required|exists:booths,id',
            'price' => 'required|numeric|min:0',
            'discount_id' => 'nullable|exists:discounts,id',
        ]);

        $booking = Booking::where('id', $validated['booking_id'])
            ->where('user_id', $id)
            ->firstOrFail();

        $discountPercent = 0;
        $discountedPrice = $validated['price'];

        if ($validated['discount_id']) {
            $discount = Discount::find($validated['discount_id']);

            if ($discount && $discount->status === 'active') {
                $discountPercent = $discount->type === 'percentage'
                    ? (float) $discount->amount
                    : ($validated['price'] > 0 ? ($discount->amount / $validated['price']) * 100 : 0);

                $discountValue = $discount->type === 'percentage'
                    ? $validated['price'] * ($discount->amount / 100)
                    : $discount->amount;

                $discountedPrice = max(0, $validated['price'] - $discountValue);
            }
        }

        $booking->update([
            'exhibition_id' => $validated['exhibition_id'],
            'booth_id' => $validated['booth_id'],
            'total_amount' => $discountedPrice,
            'discount_percent' => $discountPercent,
        ]);

        return back()->with('success', 'Booth assignment updated successfully.');
    }

    /**
     * Send a message from admin to a specific exhibitor.
     */
    public function sendMessage(Request $request, $id)
    {
        $exhibitor = User::role('Exhibitor')->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
        ]);

        // Create a new thread_id for this message
        $threadId = uniqid('thread_', true) . '_' . time();
        
        Message::create([
            'thread_id' => $threadId,
            'sender_id' => auth()->id(),
            'receiver_id' => $exhibitor->id,
            'exhibition_id' => $validated['exhibition_id'] ?? null,
            'message' => $validated['message'],
            'status' => 'inbox',
            'is_closed' => false,
        ]);

        return back()->with('success', 'Message sent to exhibitor successfully.');
    }

    /**
     * Close the chat with a specific exhibitor and move it to archive.
     */
    public function closeChat($id)
    {
        $exhibitor = User::role('Exhibitor')->findOrFail($id);

        Message::where(function ($query) use ($exhibitor) {
                $query->where('sender_id', $exhibitor->id)
                    ->orWhere('receiver_id', $exhibitor->id);
            })
            ->where('status', '!=', 'archived')
            ->update([
                'status' => 'archived',
                'is_closed' => true,
            ]);

        return back()->with('success', 'Chat archived successfully for this exhibitor.');
    }
}
