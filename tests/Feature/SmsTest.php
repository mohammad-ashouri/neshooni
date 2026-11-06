<?php

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config([
        'services.melipayamak.key' => 'test-key',
        'services.melipayamak.otp_body_id' => 12345,
    ]);
});

test('کد بازیابی با الگوی ملی پیامک فرستاده می‌شود', function () {
    Http::fake(['console.melipayamak.com/*' => Http::response(['recId' => 987654321, 'status' => ''])]);
    User::factory()->create(['mobile' => '09120000001']);

    Livewire::test(ForgotPassword::class)
        ->set('mobile', '09120000001')
        ->call('sendCode')
        ->assertSet('step', 2);

    Http::assertSent(fn ($request) => $request->url() === 'https://console.melipayamak.com/api/send/shared/test-key'
        && $request['bodyId'] === 12345
        && $request['to'] === '09120000001'
        && preg_match('/^\d{5}$/', $request['args'][0]) === 1);
});

test('برای شماره غیرعضو پیامکی فرستاده نمی‌شود', function () {
    Http::fake();

    Livewire::test(ForgotPassword::class)
        ->set('mobile', '09120000002')
        ->call('sendCode')
        ->assertSet('step', 2);

    Http::assertNothingSent();
});

test('خطای پنل پیامک فرم را خراب نمی‌کند', function () {
    Http::fake(['console.melipayamak.com/*' => Http::response('error', 500)]);
    User::factory()->create(['mobile' => '09120000003']);

    Livewire::test(ForgotPassword::class)
        ->set('mobile', '09120000003')
        ->call('sendCode')
        ->assertHasNoErrors()
        ->assertSet('step', 2);
});
