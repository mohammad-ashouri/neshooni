<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // نام درگاهی که کاربر انتخاب کرد (zarinpal، zibal، ...)
            $table->string('gateway', 30)->nullable()->after('status');
            // شناسه تراکنش که درگاه هنگام شروع پرداخت می‌دهد (authority، token و...)
            $table->string('transaction_id')->nullable()->after('gateway')->index();
            // شماره کارت پرداخت‌کننده (معمولاً ماسک‌شده) که درگاه برمی‌گرداند
            $table->string('card_number', 30)->nullable()->after('ref_id');
            // جزئیاتی که برای تأیید تراکنش لازم است (مثل uuid فاکتور)
            $table->json('gateway_meta')->nullable()->after('card_number');
            // پیام آخرین تلاش ناموفق برای نمایش به کاربر و مدیر
            $table->string('failure_reason')->nullable()->after('gateway_meta');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropColumn(['gateway', 'transaction_id', 'card_number', 'gateway_meta', 'failure_reason']);
        });
    }
};
