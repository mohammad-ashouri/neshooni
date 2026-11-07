<?php

/*
| اعتمادسازی پیش از راه‌اندازی عمومی:
| آگهی ویژه جدا از نتایج، مرتب‌سازی با امتیاز، تاریخچه اقدامات، ورود دومرحله‌ای پنل و لغو عضویت خبرنامه.
*/

use App\Enums\ModerationStatus;
use App\Livewire\Admin\Listings;
use App\Livewire\Admin\Photos;
use App\Livewire\Auth\Login;
use App\Livewire\Pages\Explore;
use App\Livewire\Pages\Unsubscribe;
use App\Models\ActivityLog;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\OutboxMessage;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionNotifier;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

// ---------- آگهی ویژه و رتبه‌بندی ----------

test('پیج ویژه در نتایج عادی جلو نمی‌افتد و جدا با برچسب آگهی نمایش داده می‌شود', function () {
    $old = makeListing(attributes: ['title' => 'پیج قدیمی ویژه', 'featured_until' => now()->addWeek()]);
    $old->forceFill(['created_at' => now()->subYear()])->save();
    makeListing(attributes: ['title' => 'پیج تازه']);

    Livewire::test(Explore::class)
        ->assertViewHas('ads', fn ($ads) => $ads->pluck('id')->all() === [$old->id])
        ->assertViewHas('listings', fn ($listings) => $listings->first()->title === 'پیج تازه')
        ->assertSee('آگهی ویژه')
        ->assertSee('نتایج زیر مستقل از پرداخت مرتب شده‌اند');
});

test('بدون پیج ویژه ردیف آگهی نمایش داده نمی‌شود', function () {
    makeListing();

    Livewire::test(Explore::class)->assertDontSee('جایگاه تبلیغاتی');
});

test('مرتب‌سازی بیشترین امتیاز، پیج بدون نظر را آخر می‌گذارد', function () {
    $none = makeListing(attributes: ['title' => 'بدون نظر']);
    $low = makeListing(attributes: ['title' => 'امتیاز کم']);
    $high = makeListing(attributes: ['title' => 'امتیاز بالا']);
    foreach ([[$low, 2], [$high, 5]] as [$listing, $rating]) {
        Review::create(['listing_id' => $listing->id, 'user_id' => userWithRole()->id, 'rating' => $rating, 'body' => 'نظر', 'status' => ModerationStatus::Approved]);
    }

    Livewire::test(Explore::class)->set('sort', 'rating')
        ->assertViewHas('listings', fn ($listings) => $listings->pluck('id')->all() === [$high->id, $low->id, $none->id]);
});

// ---------- تاریخچه اقدامات ----------

test('رد پیج توسط ناظر با نام او و دلیل رد ثبت می‌شود', function () {
    $moderator = userWithRole('moderator');
    $listing = makeListing(attributes: ['status' => ModerationStatus::Pending]);

    $this->actingAs($moderator);
    Livewire::test(Listings::class)
        ->call('startReject', $listing->id)
        ->set('reason', 'اطلاعات ناقص')
        ->call('reject');

    $log = ActivityLog::where('subject_type', Listing::class)->where('event', 'updated')->latest('id')->first();
    expect($log->user_id)->toBe($moderator->id)
        ->and($log->subject_id)->toBe((string) $listing->id)
        ->and($log->changes['status'])->toBe(['old' => 'pending', 'new' => 'rejected'])
        ->and($log->changes['reject_reason']['new'])->toBe('اطلاعات ناقص');
});

test('تغییر اطلاعات پیج توسط صاحبش ثبت می‌شود ولی بازدید و کلیک نه', function () {
    $listing = makeListing();
    $this->actingAs($listing->user);

    $listing->update(['phone' => '02112345678']);
    $listing->increment('views');
    $listing->increment('clicks');

    $logs = ActivityLog::where('subject_type', Listing::class)->where('event', 'updated')->get();
    expect($logs)->toHaveCount(1)
        ->and($logs[0]->changes)->toHaveKey('phone')
        ->and($logs[0]->user_id)->toBe($listing->user_id);
});

test('رمز عبور در تاریخچه ثبت نمی‌شود، فقط اینکه عوض شده', function () {
    $user = userWithRole();
    $user->update(['password' => 'new-secret-123', 'bio' => 'متن']);

    $log = ActivityLog::where('subject_type', User::class)->where('event', 'updated')->sole();
    expect($log->changes)->toBe(['password' => ['old' => '***', 'new' => '***']]);
});

test('تأیید و رد گروهی تصاویر هم ثبت می‌شود', function () {
    $listing = makeListing();
    $a = ListingPhoto::create(['listing_id' => $listing->id, 'path' => 'a.jpg', 'status' => ModerationStatus::Pending]);
    $b = ListingPhoto::create(['listing_id' => $listing->id, 'path' => 'b.jpg', 'status' => ModerationStatus::Pending]);

    $this->actingAs(userWithRole('admin'));
    Livewire::test(Photos::class)->call('approve', [$a->id])->call('reject', [$b->id]);

    expect(ActivityLog::where('subject_type', ListingPhoto::class)->where('event', 'updated')->where('subject_id', $a->id)->exists())->toBeTrue()
        ->and(ActivityLog::where('subject_type', ListingPhoto::class)->where('event', 'deleted')->where('subject_id', $b->id)->exists())->toBeTrue();
});

test('صفحه تاریخچه فقط با دسترسی باز می‌شود و تاریخچه یک پیج را فیلتر می‌کند', function () {
    $listing = makeListing(attributes: ['title' => 'پیج هدف']);
    makeListing(attributes: ['title' => 'پیج دیگر']);

    $this->actingAs(userWithRole('moderator'))->get('/panel/activity')->assertForbidden();

    $this->actingAs(userWithRole('admin'))->get('/panel/activity?subject=listing&id='.$listing->id)
        ->assertOk()->assertSee('پیج هدف')->assertDontSee('پیج دیگر');
});

// ---------- ورود دومرحله‌ای پنل ----------

test('ناظر بعد از رمز درست باید کد پیامکی هم وارد کند', function () {
    $lastCode = fakeSmsAndCapture();
    $moderator = userWithRole('moderator');

    $form = Livewire::test(Login::class)
        ->set('mobile', $moderator->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login')
        ->assertHasNoErrors()
        ->assertSet('step', 2);

    $this->assertGuest();
    Http::assertSent(fn ($r) => $r['bodyId'] === 555 && $r['to'] === $moderator->mobile);

    $form->set('code', fa_digits($lastCode()))->call('verifyCode')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($moderator);
});

test('کاربر عادی کد پیامکی نمی‌خواهد', function () {
    fakeSmsAndCapture();
    $user = userWithRole();

    Livewire::test(Login::class)
        ->set('mobile', $user->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login')
        ->assertRedirect(route('dashboard'));

    Http::assertNothingSent();
});

test('کد پیامکی ورود بعد از ۵ اشتباه باطل می‌شود', function () {
    $lastCode = fakeSmsAndCapture();
    $admin = userWithRole('admin');

    $form = Livewire::test(Login::class)
        ->set('mobile', $admin->mobile)->set('password', 'password')->set('captcha', '12345')
        ->call('login');
    $code = $lastCode();
    $wrong = $code === '11111' ? '22222' : '11111';

    foreach (range(1, 5) as $i) {
        $form->set('code', $wrong)->call('verifyCode')->assertHasErrors('code');
    }
    $form->set('code', $code)->call('verifyCode')->assertSet('step', 1);

    $this->assertGuest();
});

test('بدون گذشتن از مرحله رمز، کد پیامکی کسی را وارد نمی‌کند', function () {
    Livewire::test(Login::class)->set('step', 2)->set('code', '12345')->call('verifyCode')->assertSet('step', 1);

    $this->assertGuest();
});

// ---------- لغو عضویت خبرنامه ----------

test('پیام خبرنامه مهمان لینک لغو عضویت دارد و با آن همه اشتراک‌هایش لغو می‌شود', function () {
    $listing = makeListing(attributes: ['status' => ModerationStatus::Pending]);
    $sub = Subscription::create(['contact' => '09120000001', 'category_id' => $listing->category_id]);
    Subscription::create(['contact' => '09120000001', 'category_id' => null]);
    $other = Subscription::create(['contact' => '09120000002', 'category_id' => null]);

    app(SubscriptionNotifier::class)->notify($listing);
    expect(OutboxMessage::first()->body)->toContain($sub->unsubscribeUrl())
        ->and($sub->unsubscribeUrl())->not->toContain('0912');

    // باز کردن لینک به‌تنهایی چیزی را لغو نمی‌کند
    $this->get($sub->unsubscribeUrl())->assertOk()->assertSee('لغو همه عضویت‌ها');
    expect(Subscription::count())->toBe(3);

    Livewire::test(Unsubscribe::class, ['token' => $sub->token])->call('unsubscribe')->assertSee('عضویت شما لغو شد');
    expect(Subscription::pluck('id')->all())->toBe([$other->id]);
});

test('لینک لغو نامعتبر خطا نمی‌دهد', function () {
    $this->get('/unsubscribe/nothing')->assertOk()->assertSee('اشتراکی پیدا نشد');
});
