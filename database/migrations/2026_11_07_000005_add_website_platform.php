<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * «سایت فروشگاهی» یک پلتفرم با نوع شناسه url است؛ آیدی آن خود آدرس سایت است (مثل myshop.ir).
     * روی دیتابیس‌هایی که قبلاً seed شده‌اند اضافه می‌شود؛ PlatformSeeder هم همین را با updateOrCreate دارد.
     */
    public function up(): void
    {
        if (DB::table('platforms')->where('key', 'website')->exists()) {
            return;
        }

        DB::table('platforms')->insert([
            'key' => 'website',
            'label' => 'سایت فروشگاهی',
            'color' => '#0f766e',
            'base_url' => '',
            'handle_type' => 'url',
            'letter' => '🌐',
            'is_active' => true,
            'sort' => (int) DB::table('platforms')->max('sort') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // پلتفرمی که سایت ثبت‌شده دارد حذف نمی‌شود (کلید خارجی listings)
        DB::table('platforms')->where('key', 'website')->whereNotExists(
            fn ($q) => $q->from('listings')->whereColumn('listings.platform_id', 'platforms.id')
        )->delete();
    }
};
