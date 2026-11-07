<?php

namespace App\Support;

use App\Models\Setting;

/**
 * کد نماد اعتماد الکترونیکی (اینماد) که مدیر از پنل وارد می‌کند و در فوتر نمایش داده می‌شود.
 * همان کدی است که سایت enamad.ir برای جای‌گذاری می‌دهد؛ خالی یعنی باکس اینماد در فوتر نشان داده نمی‌شود.
 */
class Enamad
{
    private const KEY = 'enamad';

    public static function code(): ?string
    {
        return Setting::get(self::KEY)['code'] ?? null;
    }

    public static function save(?string $code): void
    {
        $code = trim((string) $code);

        Setting::put(self::KEY, $code === '' ? null : ['code' => $code]);
    }
}
