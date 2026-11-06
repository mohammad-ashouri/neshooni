<?php

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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

test('کد بازیابی بعد از ۵ تلاش اشتباه باطل می‌شود', function () {
    Http::fake(['console.melipayamak.com/*' => Http::response(['recId' => 987654321])]);
    $user = User::factory()->create(['mobile' => '09120000004']);

    $form = Livewire::test(ForgotPassword::class)
        ->set('mobile', '09120000004')
        ->call('sendCode')
        ->set('password', 'newpass123')
        ->set('password_confirmation', 'newpass123');
    $code = Http::recorded()->last()[0]['args'][0];
    $wrong = $code === '11111' ? '22222' : '11111';

    foreach (range(1, 5) as $i) {
        $form->set('code', $wrong)->call('resetPassword')->assertHasErrors('code');
    }
    $form->set('code', $code)->call('resetPassword')->assertHasErrors('code');

    $this->assertGuest();
    expect(Hash::check('newpass123', $user->fresh()->password))->toBeFalse();
});

test('کد درست بعد از چند تلاش اشتباه پذیرفته می‌شود', function () {
    Http::fake(['console.melipayamak.com/*' => Http::response(['recId' => 987654321])]);
    User::factory()->create(['mobile' => '09120000005']);

    $form = Livewire::test(ForgotPassword::class)
        ->set('mobile', '09120000005')
        ->call('sendCode')
        ->set('password', 'newpass123')
        ->set('password_confirmation', 'newpass123');
    $code = Http::recorded()->last()[0]['args'][0];
    $wrong = $code === '11111' ? '22222' : '11111';

    $form->set('code', $wrong)->call('resetPassword')->assertHasErrors('code');
    $form->set('code', $code)->call('resetPassword')->assertHasNoErrors()->assertRedirect(route('dashboard'));
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
