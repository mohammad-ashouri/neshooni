<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** کد تصادفی لینک لغو عضویت؛ تا موبایل یا ایمیل مشترک در آدرس نیاید */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('token', 40)->nullable()->unique();
        });

        DB::table('subscriptions')->whereNull('token')->orderBy('id')->each(
            fn ($row) => DB::table('subscriptions')->where('id', $row->id)->update(['token' => Str::random(40)])
        );
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
        });
    }
};
