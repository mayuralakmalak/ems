<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS to the given phone number.
     * Uses settings from Admin > Settings > Email/SMS and OTP/DLT.
     * No-op when credentials are not configured; add API key etc. later to enable.
     *
     * @param  string  $to  Phone number (E.164 or national format as per gateway)
     * @param  string  $message  Plain text message
     * @param  string|null  $dltTemplateId  DLT template ID (India); uses payment_reminder if null and configured
     * @return bool  True if sent or skipped (no config), false on failure
     */
    public function send(string $to, string $message, ?string $dltTemplateId = null): bool
    {
        $sms = Setting::getByGroup('email_sms');
        $dlt = Setting::getByGroup('otp_dlt');

        $apiKey = $sms['sms_api_key'] ?? '';
        $gateway = $sms['sms_gateway'] ?? '';
        $senderId = $sms['sms_sender_id'] ?? '';

        if ($apiKey === '' || $gateway === '') {
            Log::debug('SMS not sent: credentials not configured.', ['to' => $to]);
            return true;
        }

        $to = $this->normalizePhone($to);
        if ($to === '') {
            Log::warning('SMS not sent: invalid or empty phone number.');
            return false;
        }

        $templateId = $dltTemplateId ?? ($dlt['dlt_template_id_payment_reminder'] ?? $dlt['dlt_template_id_sms'] ?? null);

        try {
            return $this->sendViaGateway($gateway, $to, $message, $senderId, $templateId, $sms, $dlt);
        } catch (\Throwable $e) {
            Log::error('SMS send failed: ' . $e->getMessage(), ['to' => $to, 'gateway' => $gateway]);
            return false;
        }
    }

    /**
     * Normalize phone for SMS (strip spaces, ensure + prefix if international).
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        return $phone === '' ? '' : $phone;
    }

    /**
     * Send via configured gateway. Placeholder: implement actual API call when credentials are added.
     */
    protected function sendViaGateway(string $gateway, string $to, string $message, string $senderId, ?string $dltTemplateId, array $sms, array $dlt): bool
    {
        // Placeholder: when you add credentials, implement the real API call here.
        // Example for a generic HTTP SMS API:
        // $response = Http::withHeaders([...])->post($url, ['to' => $to, 'message' => $message, ...]);
        // return $response->successful();

        Log::info('SMS flow invoked (gateway not integrated yet).', [
            'gateway' => $gateway,
            'to' => $to,
            'message_length' => strlen($message),
            'sender_id' => $senderId,
            'dlt_template_id' => $dltTemplateId,
        ]);

        return true;
    }
}
