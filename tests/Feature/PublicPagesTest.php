<?php

use App\Enums\ModerationStatus;
use App\Livewire\Pages\ListingShow;
use App\Models\Platform;
use App\Models\Report;
use Livewire\Livewire;

test('صفحات عمومی باز می‌شوند', function (string $url) {
    makeListing();

    $this->get($url)->assertOk();
})->with(['/', '/c', '/c/fashion', '/c/fashion/women/manteau', '/contact', '/about', '/help', '/login', '/register']);

test('مسیر دسته نامعتبر ۴۰۴ می‌دهد', function () {
    $this->get('/c/fashion/not-a-category')->assertNotFound();
});

test('فیلتر دسته پیج‌های زیردسته‌ها را هم نشان می‌دهد', function () {
    makeListing(attributes: ['title' => 'مانتو فروشی']);
    makeListing(attributes: ['title' => 'گوشی فروشی', 'category_id' => categoryAt('digital')->id]);

    $this->get('/c/fashion')->assertSee('مانتو فروشی')->assertDontSee('گوشی فروشی');
});

test('پیج تأییدنشده فقط برای صاحبش دیده می‌شود', function () {
    $owner = userWithRole();
    $listing = makeListing($owner, ['status' => ModerationStatus::Pending]);

    $this->get(route('listings.show', $listing))->assertNotFound();
    $this->actingAs(userWithRole())->get(route('listings.show', $listing))->assertNotFound();
    $this->actingAs($owner)->get(route('listings.show', $listing))->assertOk();
});

test('بازدید پیج در هر نشست یک‌بار شمرده می‌شود', function () {
    $listing = makeListing();

    $this->get(route('listings.show', $listing));
    $this->get(route('listings.show', $listing));

    expect($listing->fresh()->views)->toBe(1)
        ->and($listing->dailyStats()->sum('views'))->toBe(1);
});

test('مهمان برای دنبال کردن و گزارش به صفحه ورود می‌رود', function () {
    $listing = makeListing();

    Livewire::test(ListingShow::class, ['listing' => $listing])
        ->call('toggleFollow')->assertRedirect(route('login'));

    Livewire::test(ListingShow::class, ['listing' => $listing])
        ->call('openReport', 'listing')->assertRedirect(route('login'));
});

test('کاربر پیج را دنبال می‌کند، نظر می‌دهد و گزارش ثبت می‌کند', function () {
    $listing = makeListing();
    $user = userWithRole();

    Livewire::actingAs($user)->test(ListingShow::class, ['listing' => $listing])
        ->call('toggleFollow')
        ->set('rating', 4)->set('body', 'خرید خوبی بود، ممنون')->call('submitReview')
        ->assertHasNoErrors()
        ->call('openReport', 'listing')->set('reportReason', Report::REASONS['listing'][0])->call('submitReport')
        ->assertHasNoErrors();

    expect($listing->fans()->whereKey($user->id)->exists())->toBeTrue()
        ->and($listing->reviews()->first()->status)->toBe(ModerationStatus::Pending)
        ->and($listing->reports()->count())->toBe(1);
});

test('صاحب پیج نمی‌تواند برای پیج خودش نظر بدهد', function () {
    $owner = userWithRole();
    $listing = makeListing($owner);

    Livewire::actingAs($owner)->test(ListingShow::class, ['listing' => $listing])
        ->set('rating', 5)->set('body', 'بهترین پیج دنیا')->call('submitReview')
        ->assertForbidden();
});

test('تبلیغ ثبت سایت فروشگاهی در صفحه‌های عمومی دیده می‌شود و با غیرفعال شدن پلتفرم پنهان می‌شود', function () {
    $this->get(route('home'))->assertOk()->assertSee('سایت فروشگاهی دارید؟')->assertSee(route('register', ['intent' => 'website']), false);
    $this->get(route('about'))->assertOk()->assertSee('ثبت سایت فروشگاهی');
    $this->get(route('listings.show', makeListing()))->assertOk()->assertSee('سایت فروشگاهی دارید؟');

    Platform::where('key', 'website')->update(['is_active' => false]);
    $this->get(route('home'))->assertOk()->assertDontSee('سایت فروشگاهی دارید؟');
});
