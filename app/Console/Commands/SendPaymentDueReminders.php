<?php

namespace App\Console\Commands;

use App\Mail\PaymentDueReminderMail;
use App\Models\Payment;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-due-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment due reminder emails to exhibitors 1 day before payment due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get tomorrow's date
        $tomorrow = Carbon::tomorrow();

        // Find all pending payments due tomorrow
        $payments = Payment::where('status', 'pending')
            ->whereDate('due_date', $tomorrow)
            ->with(['user', 'booking.exhibition'])
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No payments due tomorrow. No reminders to send.');
            return 0;
        }

        $this->info("Found {$payments->count()} payment(s) due tomorrow. Sending reminders...");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($payments as $payment) {
            try {
                // Determine payment number (1st, 2nd, 3rd, etc.) by ordering payments for this booking by due_date
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

                // Send reminder email
                Mail::to($payment->user->email)->send(new PaymentDueReminderMail($payment, $paymentNumber));

                // SMS: send short reminder if phone and config available
                $phone = $payment->user->phone ?? $payment->user->mobile_number ?? $payment->user->phone_number ?? '';
                if ($phone !== '') {
                    $smsMessage = "Reminder: Your {$paymentNumber}" . $this->ordinalSuffix($paymentNumber) . " payment of ₹" . number_format($payment->amount, 0) . " is due tomorrow. " . ($payment->booking->exhibition->name ?? '');
                    try {
                        app(SmsService::class)->send($phone, $smsMessage);
                    } catch (\Throwable $e) {
                        Log::warning('Payment reminder SMS failed: ' . $e->getMessage());
                    }
                }

                // WhatsApp: send template if config available
                $waTemplate = \App\Models\Setting::get('whatsapp_template_reminder_1day', '');
                $phoneForWa = $phone ?: ($payment->user->phone ?? $payment->user->mobile_number ?? $payment->user->phone_number ?? '0');
                if ($waTemplate !== '') {
                    try {
                        app(WhatsAppService::class)->sendTemplate($phoneForWa, $waTemplate, [
                            '1' => $payment->user->name,
                            '2' => $paymentNumber . $this->ordinalSuffix($paymentNumber),
                            '3' => '₹' . number_format($payment->amount, 2),
                            '4' => $payment->due_date ? $payment->due_date->format('d M Y') : '',
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Payment reminder WhatsApp failed: ' . $e->getMessage());
                    }
                }

                $sentCount++;
                $this->info("✓ Sent reminder for Payment #{$payment->payment_number} to {$payment->user->email} (Payment {$paymentNumber})");
                
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to send payment due reminder: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'user_email' => $payment->user->email ?? 'N/A',
                    'error' => $e->getMessage(),
                ]);
                $this->error("✗ Failed to send reminder for Payment #{$payment->payment_number}: " . $e->getMessage());
            }
        }

        $this->info("\nCompleted: {$sentCount} reminder(s) sent, {$failedCount} failed.");
        return 0;
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
