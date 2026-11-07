<?php

use App\Livewire\Admin\Maintenance as MaintenancePanel;
use App\Livewire\Auth\Login;
use App\Support\Maintenance;
use Livewire\Livewire;

test('مدیر حالت تعمیر را روشن می‌کند و بازدیدکننده صفحه ۵۰۳ با پیام او می‌بیند', function () {
    Livewire::actingAs(userWithRole('admin'))->test(MaintenancePanel::class)
        ->set('message', 'تا یک ساعت دیگر برمی‌گردیم')
        ->set('duration', '60')
        ->call('enable')
        ->assertHasNoErrors();

    auth()->logout();

    $this->get('/')
        ->assertStatus(503)
        ->assertHeader('Retry-After')
        ->assertSee('تا یک ساعت دیگر برمی‌گردیم');
});

test('در حالت تعمیر کاربر عادی هم سایت را نمی‌بیند', function () {
    Maintenance::enable(null, null, null);

    $this->actingAs(userWithRole())->get('/dashboard')->assertStatus(503);
});

test('در حالت تعمیر فقط مدیر سایت و پنل را عادی می‌بیند', function () {
    Maintenance::enable(null, null, null);
    $admin = userWithRole('admin');

    $this->actingAs($admin)->get('/')->assertOk()->assertSee('سایت در حالت تعمیر است');
    $this->actingAs($admin)->get('/panel/listings')->assertOk();
    $this->actingAs(userWithRole('moderator'))->get('/panel/listings')->assertStatus(503);
});

test('در حالت تعمیر صفحه ورود باز می‌ماند و صفحه تعمیر به آن لینک دارد', function () {
    Maintenance::enable(null, null, null);

    $this->get('/')->assertStatus(503)->assertSee('ورود به پنل')->assertSee('href="/login"', false);
    $this->get('/login')->assertOk();
    $this->get('/register')->assertStatus(503);
});

test('در حالت تعمیر مدیر وارد می‌شود و به صفحه حالت تعمیر می‌رود', function () {
    Maintenance::enable(null, null, null);
    $admin = userWithRole('admin');
    $lastCode = fakeSmsAndCapture();

    Livewire::test(Login::class)
        ->set('mobile', $admin->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login')
        ->assertSet('step', 2)
        ->set('code', $lastCode())
        ->call('verifyCode')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.maintenance'));

    $this->assertAuthenticatedAs($admin);
});

test('در حالت تعمیر غیر مدیر حتی با رمز درست وارد نمی‌شود', function (string $role) {
    Maintenance::enable(null, null, null);

    Livewire::test(Login::class)
        ->set('mobile', userWithRole($role)->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login')
        ->assertHasErrors('mobile')
        ->assertSee('فقط مدیر می‌تواند وارد شود');

    $this->assertGuest();
})->with(['user', 'moderator']);

test('خاموش کردن حالت تعمیر سایت را باز می‌کند', function () {
    Maintenance::enable(null, null, null);

    Livewire::actingAs(userWithRole('admin'))->test(MaintenancePanel::class)->call('disable');
    auth()->logout();

    expect(Maintenance::isActive())->toBeFalse();
    $this->get('/')->assertOk();
});

test('فقط کسی که دسترسی حالت تعمیر دارد آن را عوض می‌کند', function () {
    $this->actingAs(userWithRole('moderator'))->get('/panel/maintenance')->assertForbidden();
    $this->actingAs(userWithRole('admin'))->get('/panel/maintenance')->assertOk();
});
