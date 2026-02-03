<?php

namespace App\Console\Commands;

use App\Mail\PaymentDueIn3DaysMail;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentDueReminders3Days extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-3day-reminders
                            {--test : Send one sample 3-day reminder to asadm@alakmalak.com only (for testing email content)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment due reminder emails to exhibitors 3 days before part payment due date';

    /**
     * Test BCC address so you can verify the email content.
     *
     * @var string
     */
    protected const TEST_BCC_EMAIL = 'asadm@alakmalak.com';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('test')) {
            return $this->sendTestEmail();
        }

        $dueDate = Carbon::today()->addDays(3);

        $payments = Payment::where('status', 'pending')
            ->whereDate('due_date', $dueDate)
            ->with(['user', 'booking.exhibition'])
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No payments due in 3 days. No reminders to send.');
            return 0;
        }

        $this->info("Found {$payments->count()} payment(s) due on {$dueDate->format('Y-m-d')}. Sending 3-day reminders...");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($payments as $payment) {
            try {
                $allPaymentsForBooking = Payment::where('booking_id', $payment->booking_id)
                    ->orderBy('due_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $paymentNumber = 1;
                foreach ($allPaymentsForBooking as $index => $p) {
                    if ($p->id === $payment->id) {
                        $paymentNumber = $index + 1;
                        break;
                    }
                }

                $mailable = new PaymentDueIn3DaysMail($payment, $paymentNumber);
                Mail::to($payment->user->email)
                    ->bcc(self::TEST_BCC_EMAIL)
                    ->send($mailable);

                $phone = $payment->user->phone ?? $payment->user->mobile_number ?? $payment->user->phone_number ?? '';
                if ($phone !== '') {
                    $smsMessage = "Reminder: Your {$paymentNumber}" . $this->ordinalSuffix($paymentNumber) . " payment of ₹" . number_format($payment->amount, 0) . " is due in 3 days (" . ($payment->due_date ? $payment->due_date->format('d M Y') : '') . "). " . ($payment->booking->exhibition->name ?? '');
                    try {
                        app(SmsService::class)->send($phone, $smsMessage);
                    } catch (\Throwable $e) {
                        Log::warning('Payment 3-day reminder SMS failed: ' . $e->getMessage());
                    }
                }

                $waTemplate = Setting::get('whatsapp_template_reminder_3days', '');
                if ($waTemplate !== '') {
                    try {
                        app(WhatsAppService::class)->sendTemplate($phone ?: '0', $waTemplate, [
                            '1' => $payment->user->name,
                            '2' => $paymentNumber . $this->ordinalSuffix($paymentNumber),
                            '3' => '₹' . number_format($payment->amount, 2),
                            '4' => $payment->due_date ? $payment->due_date->format('d M Y') : '',
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Payment 3-day reminder WhatsApp failed: ' . $e->getMessage());
                    }
                }

                $sentCount++;
                $this->info("✓ Sent 3-day reminder for Payment #{$payment->payment_number} to {$payment->user->email} (Payment {$paymentNumber}) [BCC: " . self::TEST_BCC_EMAIL . "]");

            } catch (\Throwable $e) {
                $failedCount++;
                Log::error('Failed to send payment 3-day reminder: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'user_email' => $payment->user->email ?? 'N/A',
                    'error' => $e->getMessage(),
                ]);
                $this->error("✗ Failed to send 3-day reminder for Payment #{$payment->payment_number}: " . $e->getMessage());
            }
        }

        $this->info("\nCompleted: {$sentCount} 3-day reminder(s) sent, {$failedCount} failed.");
        return 0;
    }

    /**
     * Send one sample 3-day reminder to test address only (for verifying email content).
     */
    private function sendTestEmail(): int
    {
        $dueDate = Carbon::today()->addDays(3);
        $payment = Payment::where('status', 'pending')
            ->whereDate('due_date', $dueDate)
            ->with(['user', 'booking.exhibition'])
            ->first();

        if (! $payment) {
            $payment = Payment::where('status', 'pending')
                ->whereNotNull('due_date')
                ->with(['user', 'booking.exhibition'])
                ->orderBy('due_date', 'asc')
                ->first();
        }

        if (! $payment) {
            $this->warn('No pending payment found. Create a pending payment to test the 3-day reminder email.');
            return 1;
        }

        $allPaymentsForBooking = Payment::where('booking_id', $payment->booking_id)
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        $paymentNumber = 1;
        foreach ($allPaymentsForBooking as $index => $p) {
            if ($p->id === $payment->id) {
                $paymentNumber = $index + 1;
                break;
            }
        }

        try {
            Mail::to(self::TEST_BCC_EMAIL)
                ->send(new PaymentDueIn3DaysMail($payment, $paymentNumber));
            $this->info('Test 3-day reminder sent to ' . self::TEST_BCC_EMAIL . ' (using Payment #' . $payment->payment_number . ').');
            return 0;
        } catch (\Throwable $e) {
            Log::error('Failed to send test 3-day reminder: ' . $e->getMessage());
            $this->error('Failed to send test email: ' . $e->getMessage());
            return 1;
        }
    }

    private function ordinalSuffix(int $n): string
    {
        if ($n >= 11 && $n <= 13) {
            return 'th';
        }
        return match ($n % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
