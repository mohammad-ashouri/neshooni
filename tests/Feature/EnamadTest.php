<?php

use App\Livewire\Admin\Enamad as EnamadPanel;
use App\Support\Enamad;
use Livewire\Livewire;

$code = "<a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=123&Code=abc'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=123&Code=abc' alt='' style='cursor:pointer' code='abc'></a>";

test('تا کد اینماد ثبت نشده باکس آن در فوتر نیست', function () {
    $this->get('/')->assertOk()->assertDontSee('trustseal.enamad.ir', false);
});

test('مدیر کد اینماد را ثبت می‌کند و در فوتر نمایش داده می‌شود', function () use ($code) {
    Livewire::actingAs(userWithRole('admin'))->test(EnamadPanel::class)
        ->set('code', $code)
        ->call('save')
        ->assertHasNoErrors();

    auth()->logout();

    $this->get('/')->assertOk()->assertSee($code, false);
});

test('خالی کردن کد، اینماد را از فوتر برمی‌دارد', function () use ($code) {
    Enamad::save($code);

    Livewire::actingAs(userWithRole('admin'))->test(EnamadPanel::class)
        ->set('code', '')
        ->call('save');

    expect(Enamad::code())->toBeNull();
});

test('کدی که از اینماد نیست پذیرفته نمی‌شود', function () {
    Livewire::actingAs(userWithRole('admin'))->test(EnamadPanel::class)
        ->set('code', '<script>alert(1)</script>')
        ->call('save')
        ->assertHasErrors('code');
});

test('فقط مدیر کل به بخش اینماد دسترسی دارد', function () {
    $this->actingAs(userWithRole('admin'))->get('/panel/enamad')->assertOk();
    $this->actingAs(userWithRole('moderator'))->get('/panel/enamad')->assertForbidden();
});
