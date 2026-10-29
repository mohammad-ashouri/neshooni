<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * دسته‌بندی‌ها، پلتفرم‌ها، پیج‌ها و جدول‌های وابسته به پیج.
     */
    public function up(): void
    {
        // دسته‌بندی درختی تا سه سطح (اصلی › زیردسته › زیرِزیردسته)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 16)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('label');
            // رنگ یا گرادیان CSS برای آیکون
            $table->string('color')->default('#7c3aed');
            // آدرس پایه لینک؛ آیدی کاربر به انتهای آن اضافه می‌شود (مثل t.me/)
            $table->string('base_url');
            // نوع شناسه: نام کاربری یا شماره تلفن (مثل واتساپ)
            $table->string('handle_type', 10)->default('username');
            $table->string('letter', 2)->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained();
            // عمیق‌ترین دسته‌ای که کاربر انتخاب کرده (ممکن است سطح ۱، ۲ یا ۳ باشد)
            $table->foreignId('category_id')->constrained();
            $table->string('handle');
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->unsignedInteger('followers')->default(0);
            $table->string('status', 10)->default('pending')->index();
            $table->string('reject_reason')->nullable();
            // مکان و تماس هر پیج جداگانه است
            $table->string('country', 2)->default('IR');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->boolean('online_only')->default(true);
            $table->string('address')->nullable();
            $table->string('phone', 11)->nullable();
            // ساعات کاری: {"enabled": true, "days": [{"open": true, "from": "09:00", "to": "21:00"} × ۷]}
            $table->json('hours')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamp('featured_until')->nullable()->index();
            $table->timestamps();

            $table->unique(['platform_id', 'handle']);
        });

        // تصاویر گالری؛ هر تصویر جداگانه توسط مدیر تأیید می‌شود
        Schema::create('listing_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption', 60)->nullable();
            $table->string('status', 10)->default('pending')->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // آمار روزانه بازدید و کلیک هر پیج (برای نمودارها)
        Schema::create('listing_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('clicks')->default(0);

            $table->unique(['listing_id', 'date']);
        });

        Schema::create('follows', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'listing_id']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->string('status', 10)->default('pending')->index();
            // پاسخ صاحب پیج
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('follows');
        Schema::dropIfExists('listing_daily_stats');
        Schema::dropIfExists('listing_photos');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('platforms');
        Schema::dropIfExists('categories');
    }
};
