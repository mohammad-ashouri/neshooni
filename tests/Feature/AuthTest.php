<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

test('ثبت‌نام با رقم فارسی انجام می‌شود و کاربر وارد می‌شود', function () {
    Livewire::test(Register::class)
        ->set('name', 'فروشگاه نمونه')
        ->set('mobile', '۰۹۱۲۳۴۵۶۷۸۹')
        ->set('password', 'secret123')
        ->set('password_confirmation', 'secret123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.listings.create'));

    $user = User::where('mobile', '09123456789')->first();
    expect($user)->not->toBeNull()
        ->and($user->role->key)->toBe('user');
    $this->assertAuthenticatedAs($user);
});

test('شماره تکراری پذیرفته نمی‌شود', function () {
    $existing = userWithRole();

    Livewire::test(Register::class)
        ->set('name', 'تست')->set('mobile', $existing->mobile)
        ->set('password', 'secret123')->set('password_confirmation', 'secret123')
        ->call('register')
        ->assertHasErrors(['mobile' => 'unique']);
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
