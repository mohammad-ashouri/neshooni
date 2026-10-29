<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            ['instagram', 'اینستاگرام', 'linear-gradient(135deg,#feda75,#fa7e1e 25%,#d62976 50%,#962fbf 75%,#4f5bd5)', 'instagram.com/', 'username', 'I'],
            ['telegram', 'تلگرام', '#229ED9', 't.me/', 'username', 'T'],
            ['eitaa', 'ایتا', '#EF7F1A', 'eitaa.com/', 'username', 'ا'],
            ['rubika', 'روبیکا', 'linear-gradient(135deg,#8B3DFF,#E0359B)', 'rubika.ir/', 'username', 'ر'],
            ['bale', 'بله', '#2BB673', 'ble.ir/', 'username', 'ب'],
            ['soroush', 'سروش', '#1E6FD9', 'splus.ir/', 'username', 'س'],
            ['whatsapp', 'واتساپ', '#25D366', 'wa.me/', 'phone', 'W'],
            ['youtube', 'یوتیوب', '#FF0000', 'youtube.com/@', 'username', 'Y'],
            ['aparat', 'آپارات', '#ED145B', 'aparat.com/', 'username', 'آ'],
        ];

        foreach ($platforms as $sort => [$key, $label, $color, $baseUrl, $handleType, $letter]) {
            Platform::create([
                'key' => $key,
                'label' => $label,
                'color' => $color,
                'base_url' => $baseUrl,
                'handle_type' => $handleType,
                'letter' => $letter,
                'sort' => $sort,
            ]);
        }
    }
}
