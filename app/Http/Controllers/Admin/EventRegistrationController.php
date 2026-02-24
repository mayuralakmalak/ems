<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\EventRegistrationPayment;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EventRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = EventRegistration::with(['exhibition', 'payments']);

        if ($request->filled('exhibition_id')) {
            $query->where('exhibition_id', $request->exhibition_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('registration_number', 'like', '%' . $s . '%')
                    ->orWhere('email', 'like', '%' . $s . '%')
                    ->orWhere('first_name', 'like', '%' . $s . '%')
                    ->orWhere('last_name', 'like', '%' . $s . '%')
                    ->orWhere('phone', 'like', '%' . $s . '%');
            });
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();
        $exhibitions = Exhibition::where('status', 'active')->orderBy('start_date', 'desc')->get();

        return view('admin.event-registrations.index', compact('registrations', 'exhibitions'));
    }

    public function show($id)
    {
        $registration = EventRegistration::with(['exhibition', 'payments', 'approver'])->findOrFail($id);
        return view('admin.event-registrations.show', compact('registration'));
    }

    public function approve($id)
    {
        $registration = EventRegistration::findOrFail($id);
        $registration->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
        // Notify user about approval
        try {
            if ($registration->email) {
                \Illuminate\Support\Facades\Mail::raw(
                    'Your registration ' . $registration->registration_number . ' has been approved.',
                    function ($message) use ($registration) {
                        $message->to($registration->email)
                            ->subject('Registration Approved - ' . $registration->registration_number);
                    }
                );
            }
        } catch (\Exception $e) {
            report($e);
        }
        return back()->with('success', 'Registration approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $registration = EventRegistration::findOrFail($id);
        $registration->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);
        // Notify user about rejection
        try {
            if ($registration->email) {
                \Illuminate\Support\Facades\Mail::raw(
                    'Your registration ' . $registration->registration_number . ' has been rejected. Reason: ' . $request->rejection_reason,
                    function ($message) use ($registration) {
                        $message->to($registration->email)
                            ->subject('Registration Rejected - ' . $registration->registration_number);
                    }
                );
            }
        } catch (\Exception $e) {
            report($e);
        }
        return back()->with('success', 'Registration rejected.');
    }

    public function approvePayment($paymentId)
    {
        $payment = EventRegistrationPayment::with('eventRegistration')->findOrFail($paymentId);
        DB::beginTransaction();
        try {
            $payment->update([
                'approval_status' => 'approved',
                'status' => 'completed',
                'paid_at' => now(),
                'rejection_reason' => null,
            ]);
            $reg = $payment->eventRegistration;
            $reg->increment('paid_amount', $payment->amount);
            if ($reg->paid_amount >= $reg->fee_amount) {
                $reg->update(['payment_status' => 'paid']);
            } else {
                $reg->update(['payment_status' => 'partial']);
            }
            DB::commit();
            try {
                Mail::to($reg->email)->send(new \App\Mail\EventRegistrationPaymentSubmittedMail($reg, $payment, false));
                $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new \App\Mail\EventRegistrationPaymentSubmittedMail($reg, $payment, true));
                }
            } catch (\Exception $e) {
                report($e);
            }
            return back()->with('success', 'Payment approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function rejectPayment(Request $request, $paymentId)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $payment = EventRegistrationPayment::with('eventRegistration')->findOrFail($paymentId);
        $payment->update([
            'approval_status' => 'rejected',
            'status' => 'failed',
            'rejection_reason' => $request->rejection_reason,
        ]);
        // Notify user about payment rejection
        $reg = $payment->eventRegistration;
        if ($reg && $reg->email) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    'Your payment ' . $payment->payment_number . ' for registration ' . $reg->registration_number .
                    ' has been rejected. Reason: ' . $request->rejection_reason,
                    function ($message) use ($reg, $payment) {
                        $message->to($reg->email)
                            ->subject('Payment Rejected - ' . $payment->payment_number);
                    }
                );
            } catch (\Exception $e) {
                report($e);
            }
        }
        return back()->with('success', 'Payment rejected.');
    }

    public function downloadIdProof($id)
    {
        $registration = EventRegistration::findOrFail($id);
        if (!$registration->id_proof_file || !Storage::disk('public')->exists($registration->id_proof_file)) {
            abort(404);
        }
        return Storage::disk('public')->download(
            $registration->id_proof_file,
            'id-proof-' . $registration->registration_number . '.' . pathinfo($registration->id_proof_file, PATHINFO_EXTENSION)
        );
    }

    public function downloadPaymentProof($paymentId)
    {
        $payment = EventRegistrationPayment::findOrFail($paymentId);
        if (!$payment->payment_proof_file || !Storage::disk('public')->exists($payment->payment_proof_file)) {
            abort(404);
        }
        return Storage::disk('public')->download(
            $payment->payment_proof_file,
            'payment-proof-' . $payment->payment_number . '.' . pathinfo($payment->payment_proof_file, PATHINFO_EXTENSION)
        );
    }
}
