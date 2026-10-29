<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نقش‌ها و دسترسی‌هایشان.
     * دسترسی‌ها به‌صورت آرایه‌ای از کلیدها (مثل listings.moderate) ذخیره می‌شوند و
     * فهرست کامل آن‌ها در config/permissions.php است. '*' یعنی همه دسترسی‌ها.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->json('permissions');
            // نقش‌های سیستمی (مدیر و کاربر) قابل ویرایش و حذف نیستند
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
