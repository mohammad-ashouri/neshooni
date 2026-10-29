<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * گزارش تخلف، پیام‌های تماس، نشان تأیید، پرداخت‌ها و خبرنامه.
     */
    public function up(): void
    {
        // گزارش تخلف برای پیج یا نظر (رابطه چندریختی)
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reportable');
            $table->string('reason');
            $table->text('body')->nullable();
            // open | resolved | dismissed
            $table->string('status', 10)->default('open')->index();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            // موبایل یا ایمیل
            $table->string('contact');
            $table->string('subject');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // individual | company
            $table->string('business_type', 10);
            $table->string('document');
            $table->string('note')->nullable();
            $table->string('status', 10)->default('pending')->index();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // featured | verify
            $table->string('type', 10);
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('verification_request_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('days')->nullable();
            $table->unsignedInteger('amount');
            $table->string('title');
            // pending | paid | canceled
            $table->string('status', 10)->default('pending')->index();
            // کد پیگیری درگاه بانکی
            $table->string('ref_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // اشتراک خبرنامه: category_id خالی یعنی خبرنامه عمومی
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            // برای مهمان‌ها: موبایل یا ایمیل
            $table->string('contact')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // پیام‌هایی که باید با پیامک/ایمیل برای مشترکان مهمان ارسال شوند
        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->string('contact');
            $table->text('body');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // جدول استاندارد اعلان‌های لاراول (Database Notifications)
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('reports');
    }
};
