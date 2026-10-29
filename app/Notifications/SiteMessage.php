<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * اعلان ساده داخل سایت (زنگوله هدر).
 * فقط یک متن و یک لینک دارد؛ برای همه انواع اعلان از همین کلاس استفاده می‌شود.
 */
class SiteMessage extends Notification
{
    public function __construct(
        public string $text,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'text' => $this->text,
            'url' => $this->url,
        ];
    }
}
