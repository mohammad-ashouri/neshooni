<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * خرید قسطی: installments فهرست کلید روش‌هاست (Listing::INSTALLMENT_METHODS)؛ null یعنی فروش قسطی ندارد.
     * installment_note شرایط دلخواه فروشنده است؛ مثلاً «۴ قسط بدون پیش‌پرداخت».
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->json('installments')->nullable()->after('hours');
            $table->string('installment_note', 150)->nullable()->after('installments');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['installments', 'installment_note']);
        });
    }
};
