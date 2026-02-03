<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserBookingPaymentSummaryMail;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\Setting;
use App\Models\User;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserSummaryEmailController extends Controller
{
    /**
     * Show the form: select exhibition and user, then send summary email.
     */
    public function index()
    {
        $exhibitions = Exhibition::orderBy('start_date', 'desc')->get(['id', 'name', 'start_date', 'end_date']);
        // Users are loaded via AJAX only after an exhibition is selected
        return view('admin.send-user-summary.index', compact('exhibitions'));
    }

    /**
     * Get users who have bookings (optionally filtered by exhibition) for dropdown.
     */
    public function getUsers(Request $request)
    {
        $exhibitionId = $request->input('exhibition_id');

        // Only return users when an exhibition is selected (users who have bookings in that exhibition)
        if (! $exhibitionId) {
            return response()->json(['users' => []]);
        }

        $users = User::whereHas('bookings', function ($q) use ($exhibitionId) {
            $q->where('exhibition_id', $exhibitionId);
        })
            ->withCount(['bookings' => function ($q) use ($exhibitionId) {
                $q->where('exhibition_id', $exhibitionId);
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bookings_count' => $user->bookings_count,
            ];
        });

        return response()->json(['users' => $users]);
    }

    /**
     * Send booking & payment summary email to the selected user.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $exhibition = Exhibition::findOrFail($validated['exhibition_id']);

        $bookings = Booking::where('user_id', $user->id)
            ->where('exhibition_id', $exhibition->id)
            ->with(['exhibition', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'No bookings found for this user in the selected exhibition.');
        }

        try {
            Mail::to($user->email)->send(new UserBookingPaymentSummaryMail($user, $bookings, $exhibition));

            $phone = $user->phone ?? $user->mobile_number ?? $user->phone_number ?? '';
            if ($phone !== '') {
                $smsMessage = 'Your booking & payment summary for ' . ($exhibition->name ?? 'exhibition') . ' has been sent to your email: ' . $user->email . '.';
                try {
                    app(SmsService::class)->send($phone, $smsMessage);
                } catch (\Throwable $e) {
                    Log::warning('User summary SMS failed: ' . $e->getMessage());
                }
            }

            $waTemplate = Setting::get('whatsapp_template_summary_sent', '');
            if ($waTemplate !== '') {
                try {
                    app(WhatsAppService::class)->sendTemplate($phone ?: '0', $waTemplate, [
                        '1' => $user->name,
                        '2' => $exhibition->name ?? 'Exhibition',
                        '3' => $user->email,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('User summary WhatsApp failed: ' . $e->getMessage());
                }
            }

            return back()->with('success', 'Summary email sent successfully to ' . $user->email . '.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
