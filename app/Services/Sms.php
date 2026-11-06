<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ارسال پیامک از طریق کنسول ملی پیامک (console.melipayamak.com).
 *
 * اگر کلید API تنظیم نشده باشد (مثلاً در محیط توسعه)، پیامک فرستاده نمی‌شود و فقط در لاگ ثبت می‌شود.
 */
class Sms
{
    private const BASE_URL = 'https://console.melipayamak.com/api/send';

    /**
     * ارسال با الگوی خدماتی (مناسب کد تأیید؛ به شماره‌های بلاک‌شده تبلیغاتی هم می‌رسد).
     *
     * @param  array<int, string|int>  $args  مقادیر {0}، {1}، ... در متن الگو
     */
    public function sendPattern(string $to, int $bodyId, array $args): bool
    {
        return $this->post('shared', [
            'bodyId' => $bodyId,
            'to' => $to,
            'args' => array_map('strval', array_values($args)),
        ]);
    }

    /** ارسال متن آزاد از خط اختصاصی پنل. */
    public function send(string $to, string $text): bool
    {
        return $this->post('simple', [
            'from' => (string) config('services.melipayamak.from'),
            'to' => $to,
            'text' => $text,
        ]);
    }

    private function post(string $type, array $payload): bool
    {
        $key = config('services.melipayamak.key');

        if (blank($key)) {
            Log::info('SMS (not sent, no API key)', ['type' => $type] + $payload);

            return true;
        }

        try {
            $response = Http::timeout(10)->acceptJson()->post(self::BASE_URL."/{$type}/{$key}", $payload);
        } catch (Throwable $e) {
            Log::error('SMS request failed', ['type' => $type, 'to' => $payload['to'], 'error' => $e->getMessage()]);

            return false;
        }

        if ($response->successful() && (int) $response->json('recId') > 0) {
            return true;
        }

        Log::error('SMS rejected', ['type' => $type, 'to' => $payload['to'], 'status' => $response->status(), 'body' => $response->body()]);

        return false;
    }
}
