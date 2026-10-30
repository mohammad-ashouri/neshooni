<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * زمان اولین انتشار پیج.
 * فقط در اولین تأیید به مشترکان خبرنامه خبر داده می‌شود؛ تأییدهای بعدی (بعد از ویرایش) خبر ندارند.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('status');
        });

        // پیج‌های تأییدشده قبلی منتشرشده حساب می‌شوند
        DB::table('listings')->where('status', 'approved')->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
