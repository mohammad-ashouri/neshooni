<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تاریخچه اقدامات: چه کسی، کی، روی چه چیزی، چه تغییری داد.
     * subject_id رشته است چون کلید بعضی مدل‌ها (مثل تنظیمات) عدد نیست.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // created | updated | deleted
            $table->string('event', 20);
            $table->string('subject_type');
            $table->string('subject_id');
            // نام مورد در لحظه ثبت؛ تا بعد از حذف هم معلوم باشد چه چیزی بوده
            $table->string('subject_label')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
