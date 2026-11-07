<?php

use App\Livewire\Dashboard\Feature;
use App\Livewire\Pages\Checkout;
use App\Models\Payment;
use App\Services\PaymentGateways;
use Livewire\Livewire;
use Shetabit\Multipay\Constants\IranCurrency;

/** شروع پرداخت با درگاه تست و برگشتن از آن، مثل کاری که بانک می‌کند */
function payWithTestGateway($test, Payment $payment, bool $success = true)
{
    $test->actingAs($payment->user)->post(route('payments.start', $payment), ['gateway' => 'local'])
        ->assertOk()->assertSee('درگاه پرداخت تست');

    $payment->refresh();
    $query = ['transactionId' => $payment->transaction_id] + ($success ? [] : ['cancel' => 'true']);

    return $test->post(route('payments.callback', $payment).'?'.http_build_query($query));
}

test('خرید آگهی ویژه از درگاه، روزها را به انتهای ویژه فعلی اضافه می‌کند', function () {
    $owner = userWithRole();
    $listing = makeListing($owner, ['featured_until' => now()->addDays(5)]);

    Livewire::actingAs($owner)->test(Feature::class, ['listing' => $listing])->call('buy', 30);
    $payment = Payment::firstOrFail();
    expect($payment->status)->toBe('pending')->and($payment->amount)->toBe(290000);

    Livewire::actingAs($owner)->test(Checkout::class, ['payment' => $payment])->assertSee('درگاه تست');

    payWithTestGateway($this, $payment)->assertRedirect(route('payments.finish', $payment));

    $payment->refresh();
    expect($payment->isPaid())->toBeTrue()
        ->and($payment->gateway)->toBe('local')
        ->and($payment->ref_id)->toBe($payment->transaction_id)
        ->and((int) round(now()->diffInDays($listing->fresh()->featured_until)))->toBe(35)
        ->and($owner->notifications()->count())->toBe(1);

    $this->actingAs($owner)->get(route('payments.finish', $payment))
        ->assertRedirect(route('dashboard'))->assertSessionHas('toast');

    // برگشت تکراری از درگاه، خدمت را دو بار اعمال نمی‌کند
    $this->post(route('payments.callback', $payment).'?transactionId='.$payment->transaction_id);
    expect((int) round(now()->diffInDays($listing->fresh()->featured_until)))->toBe(35)
        ->and($owner->notifications()->count())->toBe(1);
});

test('پرداخت ناموفق یا شناسه جعلی، پرداخت را در انتظار نگه می‌دارد', function () {
    $payment = userWithRole()->payments()->create(['type' => 'verify', 'amount' => 150000, 'title' => 'نشان تأیید', 'status' => 'pending']);

    payWithTestGateway($this, $payment, success: false);
    expect($payment->fresh()->status)->toBe('pending')->and($payment->fresh()->failure_reason)->not->toBeNull();

    $this->post(route('payments.callback', $payment).'?transactionId=123');
    expect($payment->fresh()->isPaid())->toBeFalse();

    $this->actingAs($payment->user)->get(route('payments.finish', $payment))
        ->assertRedirect(route('checkout', $payment))->assertSessionHasErrors('gateway');
});

test('فقط درگاه‌های فعال قابل انتخاب‌اند و درگاه تست در production پنهان است', function () {
    $payment = userWithRole()->payments()->create(['type' => 'verify', 'amount' => 150000, 'title' => 'x', 'status' => 'pending']);

    $this->actingAs($payment->user)->post(route('payments.start', $payment), ['gateway' => 'zarinpal'])
        ->assertRedirect(route('checkout', $payment))->assertSessionHasErrors('gateway');
    expect($payment->fresh()->gateway)->toBeNull();

    config(['payment.enabled' => ['zarinpal', 'paypal', 'nope', 'local']]);
    expect(array_keys(app(PaymentGateways::class)->available()))->toBe(['zarinpal', 'local']);

    config(['payment.rates.paypal' => 100000]);
    app()->detectEnvironment(fn () => 'production');
    expect(array_keys(app(PaymentGateways::class)->available()))->toBe(['zarinpal', 'paypal']);
});

test('مبلغ تومانی برای هر درگاه درست تبدیل می‌شود', function () {
    config(['payment.rates.paypal' => 100000]);
    $gateways = app(PaymentGateways::class);

    expect($gateways->amountFor('zarinpal', 150000))->toBe(150000)
        ->and($gateways->amountFor('digify', 150000))->toBe(1500000)
        ->and($gateways->amountFor('paypal', 150000))->toBe(1.5)
        ->and(config('payment.drivers.paystar.currency'))->toBe(IranCurrency::TOMAN);
});

test('بازگشت از درگاه بدون session و CSRF کار می‌کند', function () {
    $middleware = app('router')->getRoutes()->getByName('payments.callback')->gatherMiddleware();

    expect($middleware)->not->toContain('web');
});

test('رسید فقط برای صاحب پرداخت باز می‌شود و استعلام دارد', function () {
    $owner = userWithRole();
    $payment = $owner->payments()->create([
        'type' => 'verify', 'amount' => 150000, 'title' => 'نشان تأیید', 'status' => 'paid', 'ref_id' => '12345678', 'paid_at' => now(),
        'gateway' => 'zarinpal', 'card_number' => '6037****1234',
    ]);

    $this->actingAs($owner)->get(route('receipts.show', $payment))->assertOk()->assertSee('صد و پنجاه هزار تومان')->assertSee('زرین‌پال');
    $this->actingAs(userWithRole())->get(route('receipts.show', $payment))->assertForbidden();
    $this->get('/r/۱۲۳۴۵۶۷۸')->assertOk()->assertSee('این رسید معتبر است');
});

test('پرداخت دیگران قابل پرداخت نیست', function () {
    $payment = userWithRole()->payments()->create(['type' => 'verify', 'amount' => 150000, 'title' => 'x', 'status' => 'pending']);
    $stranger = userWithRole();

    $this->actingAs($stranger)->get(route('checkout', $payment))->assertForbidden();
    $this->actingAs($stranger)->post(route('payments.start', $payment), ['gateway' => 'local'])->assertForbidden();
    $this->actingAs($stranger)->get(route('payments.finish', $payment))->assertForbidden();
});
