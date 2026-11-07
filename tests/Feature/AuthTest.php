<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function registerForm(string $mobile = '09123456789')
{
    return Livewire::test(Register::class)
        ->set('name', 'فروشگاه نمونه')
        ->set('mobile', $mobile)
        ->set('password', 'secret123')
        ->set('password_confirmation', 'secret123');
}

test('ثبت‌نام با رقم فارسی و کد پیامکی انجام می‌شود و کاربر وارد می‌شود', function () {
    $lastCode = fakeSmsAndCapture();

    $form = registerForm('۰۹۱۲۳۴۵۶۷۸۹')->call('sendCode')->assertHasNoErrors()->assertSet('step', 2);
    expect(User::where('mobile', '09123456789')->exists())->toBeFalse();

    Http::assertSent(fn ($r) => $r['bodyId'] === 777 && $r['to'] === '09123456789');

    $form->set('code', fa_digits($lastCode()))
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.listings.create'));

    $user = User::where('mobile', '09123456789')->first();
    expect($user)->not->toBeNull()
        ->and($user->role->key)->toBe('user');
    $this->assertAuthenticatedAs($user);
});

test('کد اشتباه حساب نمی‌سازد و بعد از ۵ تلاش باطل می‌شود', function () {
    $lastCode = fakeSmsAndCapture();
    $form = registerForm()->call('sendCode');
    $wrong = $lastCode() === '11111' ? '22222' : '11111';

    foreach (range(1, 5) as $i) {
        $form->set('code', $wrong)->call('register')->assertHasErrors('code');
    }
    $form->set('code', $lastCode())->call('register')->assertHasErrors('code');

    expect(User::where('mobile', '09123456789')->exists())->toBeFalse();
    $this->assertGuest();
});

test('کد یک شماره برای شماره دیگر کار نمی‌کند', function () {
    $lastCode = fakeSmsAndCapture();
    $form = registerForm()->call('sendCode');

    $form->set('mobile', '09120000009')->set('code', $lastCode())
        ->call('register')
        ->assertHasErrors('code');

    expect(User::where('mobile', '09120000009')->exists())->toBeFalse();
});

test('شماره تکراری پذیرفته نمی‌شود و پیامکی نمی‌رود', function () {
    fakeSmsAndCapture();
    $existing = userWithRole();

    registerForm($existing->mobile)
        ->call('sendCode')
        ->assertHasErrors(['mobile' => 'unique'])
        ->assertSet('step', 1);

    Http::assertNothingSent();
});

test('ورود با موبایل و رمز درست', function () {
    $user = userWithRole();

    Livewire::test(Login::class)
        ->set('mobile', $user->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('رمز اشتباه وارد نمی‌کند', function () {
    $user = userWithRole();

    Livewire::test(Login::class)
        ->set('mobile', $user->mobile)->set('password', 'wrong')->set('captcha', '12345')
        ->call('login')
        ->assertHasErrors('mobile');

    $this->assertGuest();
});

test('کپچای اشتباه رد می‌شود و تصویر عوض می‌شود', function () {
    config(['captcha.disable' => false]);
    $user = userWithRole();

    Livewire::test(Login::class)
        ->set('mobile', $user->mobile)->set('password', 'password')->set('captcha', '00000')
        ->call('login')
        ->assertHasErrors('captcha')
        ->assertSet('captchaKey', 1)
        ->assertSet('captcha', '');

    $this->assertGuest();
});

test('صفحه‌های داشبورد برای مهمان بسته است', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/panel')->assertRedirect('/login');
});
