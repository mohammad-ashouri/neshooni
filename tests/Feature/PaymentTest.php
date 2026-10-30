<?php

use App\Livewire\Dashboard\Feature;
use App\Livewire\Pages\Checkout;
use App\Models\Payment;
use Livewire\Livewire;

test('خرید آگهی ویژه، روزها را به انتهای ویژه فعلی اضافه می‌کند', function () {
    $owner = userWithRole();
    $listing = makeListing($owner, ['featured_until' => now()->addDays(5)]);

    Livewire::actingAs($owner)->test(Feature::class, ['listing' => $listing])->call('buy', 30);
    $payment = Payment::firstOrFail();
    expect($payment->status)->toBe('pending')->and($payment->amount)->toBe(290000);

    Livewire::actingAs($owner)->test(Checkout::class, ['payment' => $payment])->call('pay');

    expect($payment->fresh()->isPaid())->toBeTrue()
        ->and((int) round(now()->diffInDays($listing->fresh()->featured_until)))->toBe(35)
        ->and($owner->notifications()->count())->toBe(1);
});

test('رسید فقط برای صاحب پرداخت باز می‌شود و استعلام دارد', function () {
    $owner = userWithRole();
    $payment = $owner->payments()->create([
        'type' => 'verify', 'amount' => 150000, 'title' => 'نشان تأیید', 'status' => 'paid', 'ref_id' => '12345678', 'paid_at' => now(),
    ]);

    $this->actingAs($owner)->get(route('receipts.show', $payment))->assertOk()->assertSee('صد و پنجاه هزار تومان');
    $this->actingAs(userWithRole())->get(route('receipts.show', $payment))->assertForbidden();
    $this->get('/r/۱۲۳۴۵۶۷۸')->assertOk()->assertSee('این رسید معتبر است');
});

test('پرداخت دیگران قابل پرداخت نیست', function () {
    $payment = userWithRole()->payments()->create(['type' => 'verify', 'amount' => 150000, 'title' => 'x', 'status' => 'pending']);

    $this->actingAs(userWithRole())->get(route('checkout', $payment))->assertForbidden();
});
