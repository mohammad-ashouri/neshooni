<?php

use App\Livewire\Admin\Maintenance as MaintenancePanel;
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

test('در حالت تعمیر کارکنان پنل سایت و پنل را عادی می‌بینند', function () {
    Maintenance::enable(null, null, null);
    $moderator = userWithRole('moderator');

    $this->actingAs($moderator)->get('/')->assertOk()->assertSee('سایت در حالت تعمیر است');
    $this->actingAs($moderator)->get('/panel/listings')->assertOk();
});

test('در حالت تعمیر صفحه ورود باز می‌ماند', function () {
    Maintenance::enable(null, null, null);

    $this->get('/login')->assertOk();
    $this->get('/register')->assertStatus(503);
});

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
