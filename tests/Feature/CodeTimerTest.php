<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/** تایمر دو دقیقه و نیمه کدهای پیامکی */
function registerStep1(string $mobile = '09123456700')
{
    return Livewire::test(Register::class)
        ->set('name', 'فروشگاه نمونه')->set('mobile', $mobile)
        ->set('password', 'secret123')->set('password_confirmation', 'secret123');
}

test('تا پایان دو دقیقه و نیم کد تازه فرستاده نمی‌شود و بعدش می‌شود', function () {
    fakeSmsAndCapture();

    $form = registerStep1()->call('sendCode')
        ->assertSet('codeExpiresAt', now()->timestamp + 150)
        ->assertSeeHtml('code-timer-');
    Http::assertSentCount(1);

    $this->travel(149)->seconds();
    $form->call('sendCode')->assertDispatched('toast')->assertSet('step', 2);
    Http::assertSentCount(1);

    $this->travel(2)->seconds();
    $form->call('sendCode')->assertSet('codeExpiresAt', now()->timestamp + 150);
    Http::assertSentCount(2);
});

test('کد ثبت‌نام بعد از دو دقیقه و نیم منقضی می‌شود', function () {
    $lastCode = fakeSmsAndCapture();
    $form = registerStep1()->call('sendCode');

    $this->travel(151)->seconds();
    $form->set('code', $lastCode())->call('register')->assertHasErrors('code');

    expect(User::where('mobile', '09123456700')->exists())->toBeFalse();
});

test('کد بازیابی رمز بعد از دو دقیقه و نیم منقضی می‌شود', function () {
    $lastCode = fakeSmsAndCapture();
    User::factory()->create(['mobile' => '09120000070']);

    $form = Livewire::test(ForgotPassword::class)->set('mobile', '09120000070')->call('sendCode')
        ->assertSet('codeExpiresAt', now()->timestamp + 150)
        ->set('password', 'newpass123')->set('password_confirmation', 'newpass123');

    $this->travel(151)->seconds();
    $form->set('code', $lastCode())->call('resetPassword')->assertHasErrors('code');
    $this->assertGuest();
});

test('کد ورود پنل بعد از دو دقیقه و نیم منقضی می‌شود و ارسال دوباره تا آن موقع بسته است', function () {
    $lastCode = fakeSmsAndCapture();
    $admin = userWithRole('admin');

    $form = Livewire::test(Login::class)
        ->set('mobile', $admin->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login')
        ->assertSet('codeExpiresAt', now()->timestamp + 150);

    $form->call('resendCode');
    Http::assertSentCount(1);

    $this->travel(151)->seconds();
    $form->set('code', $lastCode())->call('verifyCode')->assertHasErrors('code');
    $this->assertGuest();

    $form->call('resendCode');
    Http::assertSentCount(2);
});

test('زمان پایان تایمر از مرورگر قابل تغییر نیست', function () {
    Livewire::test(Register::class)->set('codeExpiresAt', 0);
})->throws(CannotUpdateLockedPropertyException::class);
