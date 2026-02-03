<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp template message to the given phone number.
     * Uses settings from Admin > Settings > WhatsApp.
     * No-op when credentials are not configured; add API key etc. later to enable.
     *
     * @param  string  $to  Phone number (E.164, e.g. 919876543210)
     * @param  string  $templateName  Approved template name (e.g. payment_reminder_1day)
     * @param  array<string, string>  $params  Template parameters (e.g. ['1' => 'John', '2' => '₹5000'])
     * @return bool  True if sent or skipped (no config), false on failure
     */
    public function sendTemplate(string $to, string $templateName, array $params = []): bool
    {
        $wa = Setting::getByGroup('whatsapp');

        $enabled = ($wa['whatsapp_enabled'] ?? '0') === '1';
        $apiKey = $wa['whatsapp_api_key'] ?? '';
        $phoneNumberId = $wa['whatsapp_phone_number_id'] ?? '';

        if (! $enabled || $apiKey === '' || $phoneNumberId === '') {
            Log::debug('WhatsApp not sent: not enabled or credentials not configured.', ['to' => $to, 'template' => $templateName]);
            return true;
        }

        $to = $this->normalizePhoneForWhatsApp($to);
        if ($to === '') {
            Log::warning('WhatsApp not sent: invalid or empty phone number.');
            return false;
        }

        try {
            return $this->sendViaProvider($to, $templateName, $params, $wa);
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed: ' . $e->getMessage(), ['to' => $to, 'template' => $templateName]);
            return false;
        }
    }

    /**
     * Normalize phone for WhatsApp (E.164: digits only, with country code).
     */
    protected function normalizePhoneForWhatsApp(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) < 10) {
            return '';
        }
        if (strlen($phone) === 10 && preg_match('/^[6-9]\d{9}$/', $phone)) {
            $phone = '91' . $phone;
        }
        return $phone;
    }

    /**
     * Send via configured provider. Placeholder: implement actual API call when credentials are added.
     */
    protected function sendViaProvider(string $to, string $templateName, array $params, array $wa): bool
    {
        // Placeholder: when you add credentials, implement the real API call here.
        // e.g. Meta Cloud API: POST /v1/{phone_number_id}/messages with template object
        // or Twilio: MessageResource::create([...])

        Log::info('WhatsApp flow invoked (provider not integrated yet).', [
            'to' => $to,
            'template' => $templateName,
            'params' => $params,
            'provider' => $wa['whatsapp_provider'] ?? '',
        ]);

        return true;
    }
}
