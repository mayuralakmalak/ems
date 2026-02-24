<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\EventRegistration;
use App\Models\EventRegistrationPayment;
use App\Mail\EventRegistrationSubmittedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EventRegistrationController extends Controller
{
    private const TYPES = ['visitor', 'member', 'delegate', 'vip'];

    public function showForm(string $type)
    {
        if (!in_array($type, self::TYPES)) {
            abort(404);
        }
        $exhibitions = Exhibition::where('status', 'active')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->get();
        $typeLabel = $this->getTypeLabel($type);
        return view('frontend.registration.form', compact('type', 'exhibitions', 'typeLabel'));
    }

    public function getFee(Request $request)
    {
        $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'type' => ['required', Rule::in(self::TYPES)],
        ]);
        $exhibition = Exhibition::findOrFail($request->exhibition_id);
        $type = $request->type;
        $fee = 0;
        $tier = null;
        if ($type === 'visitor') {
            $result = $exhibition->getVisitorFeeForDate();
            $fee = $result['fee'];
            $tier = $result['tier'];
        } else {
            $fee = $exhibition->getRegistrationFeeByType($type);
        }
        return response()->json([
            'fee' => round($fee, 2),
            'tier' => $tier,
            'tier_label' => $tier ? ucfirst(str_replace('_', ' ', $tier)) : null,
        ]);
    }

    public function store(Request $request, string $type)
    {
        if (!in_array($type, self::TYPES)) {
            abort(404);
        }

        $validated = $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'id_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'company' => 'nullable|string|max:200',
            'designation' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $exhibition = Exhibition::findOrFail($validated['exhibition_id']);
        $feeAmount = $type === 'visitor'
            ? $exhibition->getVisitorFeeForDate()['fee']
            : $exhibition->getRegistrationFeeByType($type);
        $feeTier = null;
        if ($type === 'visitor') {
            $result = $exhibition->getVisitorFeeForDate();
            $feeTier = $result['tier'];
        }

        $idProofPath = $request->file('id_proof')->store('event-registration-id-proofs', 'public');

        DB::beginTransaction();
        try {
            $reg = EventRegistration::create([
                'exhibition_id' => $exhibition->id,
                'type' => $type,
                'registration_number' => EventRegistration::generateRegistrationNumber(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'id_proof_file' => $idProofPath,
                'company' => $validated['company'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'fee_amount' => $feeAmount,
                'fee_tier' => $feeTier,
                'paid_amount' => 0,
                'approval_status' => 'pending',
                'payment_status' => 'pending',
                'token' => EventRegistration::generateToken(),
            ]);

            DB::commit();

            // Send emails to registrant and admin
            try {
                Mail::to($reg->email)->send(new EventRegistrationSubmittedMail($reg, false));
                $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new EventRegistrationSubmittedMail($reg, true));
                }
            } catch (\Exception $e) {
                report($e);
            }

            if ($feeAmount <= 0) {
                $reg->update(['payment_status' => 'paid', 'approval_status' => 'approved']);
                return redirect()->route('register.confirmation', ['token' => $reg->token])
                    ->with('success', 'Registration submitted successfully. No payment required.');
            }

            return redirect()->route('register.payment', ['token' => $reg->token])
                ->with('success', 'Registration submitted. Please complete payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Registration failed: ' . $e->getMessage())->withInput();
        }
    }

    public function paymentPage(string $token)
    {
        $registration = EventRegistration::where('token', $token)->with('exhibition')->firstOrFail();
        if ($registration->isFullyPaid()) {
            return redirect()->route('register.confirmation', ['token' => $token])
                ->with('info', 'Registration is already fully paid.');
        }
        $outstanding = $registration->fee_amount - $registration->paid_amount;
        return view('frontend.registration.payment', compact('registration', 'outstanding'));
    }

    public function storePayment(Request $request, string $token)
    {
        $registration = EventRegistration::where('token', $token)->with('exhibition')->firstOrFail();
        if ($registration->isFullyPaid()) {
            return redirect()->route('register.confirmation', ['token' => $token]);
        }

        $outstanding = $registration->fee_amount - $registration->paid_amount;
        $validated = $request->validate([
            'payment_method' => 'required|in:online,neft,rtgs,offline',
            'amount' => 'required|numeric|min:0.01|max:' . $outstanding,
            'payment_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('event-registration-payment-proofs', 'public');
        }

        DB::beginTransaction();
        try {
            $payment = EventRegistrationPayment::create([
                'event_registration_id' => $registration->id,
                'payment_number' => EventRegistrationPayment::generatePaymentNumber(),
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'status' => 'pending',
                'approval_status' => 'pending',
                'payment_proof_file' => $paymentProofPath,
                'transaction_id' => $validated['transaction_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'token' => EventRegistrationPayment::generateToken(),
            ]);

            DB::commit();

            try {
                Mail::to($registration->email)->send(new \App\Mail\EventRegistrationPaymentSubmittedMail($registration, $payment, false));
                $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new \App\Mail\EventRegistrationPaymentSubmittedMail($registration, $payment, true));
                }
            } catch (\Exception $e) {
                report($e);
            }

            return redirect()->route('register.payment.confirmation', ['token' => $payment->token])
                ->with('success', 'Payment submitted. It will be processed after admin approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment submission failed: ' . $e->getMessage())->withInput();
        }
    }

    public function confirmation(Request $request)
    {
        $token = $request->query('token');
        if ($token) {
            $registration = EventRegistration::where('token', $token)->with(['exhibition', 'payments'])->first();
            if ($registration) {
                return view('frontend.registration.confirmation', compact('registration'));
            }
        }
        $paymentToken = $request->query('payment_token');
        if ($paymentToken) {
            $payment = EventRegistrationPayment::where('token', $paymentToken)->with('eventRegistration.exhibition')->first();
            if ($payment) {
                $registration = $payment->eventRegistration;
                return view('frontend.registration.confirmation', compact('registration', 'payment'));
            }
        }
        abort(404);
    }

    public function paymentConfirmation(string $token)
    {
        $payment = EventRegistrationPayment::where('token', $token)->with('eventRegistration.exhibition')->firstOrFail();
        $registration = $payment->eventRegistration;
        return view('frontend.registration.confirmation', compact('registration', 'payment'));
    }

    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'visitor' => 'Visitor',
            'member' => 'Member',
            'delegate' => 'Delegate',
            'vip' => 'VIP',
            default => ucfirst($type),
        };
    }
}
