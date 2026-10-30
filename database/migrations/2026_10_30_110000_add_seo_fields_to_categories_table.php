<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فیلدهای سئو برای صفحه هر دسته:
 * - intro: متن معرفی که بالای لیست پیج‌ها نمایش داده می‌شود (محتوای متنی برای موتور جستجو)
 * - seo_title / seo_description: عنوان و توضیح دلخواه در نتایج گوگل (اگر خالی باشد خودکار ساخته می‌شود)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->text('intro')->nullable()->after('icon');
            $table->string('seo_title')->nullable()->after('intro');
            $table->string('seo_description', 300)->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['intro', 'seo_title', 'seo_description']);
        });
    }
};
