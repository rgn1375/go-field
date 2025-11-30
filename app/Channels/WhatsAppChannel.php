<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Get WhatsApp message from notification
        $message = $notification->toWhatsApp($notifiable);

        if (!$message) {
            return;
        }

        // Get phone number - prioritize nomor_telepon, fallback to phone
        $phoneNumber = $this->formatPhoneNumber(
            $notifiable->nomor_telepon ?? $notifiable->phone ?? null
        );

        if (!$phoneNumber) {
            Log::warning('WhatsApp notification skipped: No phone number', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
            ]);
            return;
        }

        try {
            // Send via Fonnte API
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.api_key'),
            ])->post(config('services.fonnte.url'), [
                'target' => $phoneNumber,
                'message' => $message['message'],
                'countryCode' => '62', // Indonesia
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp notification sent successfully', [
                    'phone' => $phoneNumber,
                    'response' => $response->json(),
                ]);
            } else {
                Log::error('WhatsApp notification failed', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp notification exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format phone number to Indonesian format (62xxx)
     */
    protected function formatPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
