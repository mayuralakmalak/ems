<?php

namespace App\Console\Commands;

use App\Mail\PaymentDueReminderMail;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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
}
