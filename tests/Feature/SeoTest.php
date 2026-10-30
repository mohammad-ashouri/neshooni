<?php

use App\Enums\ModerationStatus;
use App\Livewire\Dashboard\SeoTools;
use App\Services\ListingSeoAudit;
use Livewire\Livewire;

test('صفحه اول عنوان، توضیح، canonical و schema دارد', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('<title>'.config('seo.title.homepage_title').'</title>')
        ->toContain('<meta name="description"')
        ->toContain('<link rel="canonical"')
        ->toContain('og:image')
        ->toContain('"@type":"WebSite"')
        ->toContain('search_term_string');
});

test('آدرس بدون نامک با ۳۰۱ به آدرس اصلی می‌رود', function () {
    $listing = makeListing(attributes: ['title' => 'گالری ماهور']);

    $this->get("/l/{$listing->id}")->assertMovedPermanently()->assertRedirect(route('listings.show', $listing));
    $this->get("/l/{$listing->id}-نام-قدیمی")->assertMovedPermanently();

    expect(route('listings.show', $listing))->toEndWith('/l/'.$listing->id.'-'.rawurlencode('گالری').'-'.rawurlencode('ماهور'));
});

test('صفحه پیج داده ساختاریافته و توضیح خودکار دارد', function () {
    $listing = makeListing(attributes: ['title' => 'کافه دارچین', 'description' => 'قهوه تخصصی و کیک خانگی', 'city' => 'شیراز', 'address' => 'خیابان زند', 'online_only' => false]);

    $html = $this->get(route('listings.show', $listing))->assertOk()->getContent();

    expect($html)
        ->toContain('"@type":"LocalBusiness"')
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('<link rel="canonical" href="'.route('listings.show', $listing).'"')
        ->toContain('قهوه تخصصی و کیک خانگی')
        ->toContain('index, follow');
});

test('عنوان اختصاصی صاحب پیج در صفحه استفاده می‌شود', function () {
    $listing = makeListing();
    $listing->seo->update(['title' => 'عنوان دلخواه من برای گوگل']);

    $this->get(route('listings.show', $listing))->assertSee('<title>عنوان دلخواه من برای گوگل | نشونی</title>', false);
});

test('پیج منتشرنشده و صفحه‌های خصوصی noindex هستند', function () {
    $owner = userWithRole();
    $listing = makeListing($owner, ['status' => ModerationStatus::Pending]);

    $this->get('/login')->assertSee('noindex, nofollow', false);
    $this->actingAs($owner)->get(route('listings.show', $listing))->assertSee('noindex, nofollow', false);
    $this->actingAs($owner)->get('/dashboard')->assertSee('noindex, nofollow', false);
});

test('جستجو noindex است ولی صفحه دسته ایندکس می‌شود', function () {
    makeListing();

    $this->get('/c/fashion')->assertSee('index, follow', false)->assertSee('<link rel="canonical" href="'.url('/c/fashion').'"', false);
    $this->get('/c?q=مانتو')->assertSee('noindex, follow', false);
    $this->get('/c?page=50')->assertNotFound();
});

test('صفحه‌بندی لینک واقعی دارد', function () {
    foreach (range(1, 13) as $i) {
        makeListing();
    }

    $this->get('/c')->assertSee('href="'.url('/c?page=2').'"', false)->assertSee('rel="next"', false);
    $this->get('/c?page=2')->assertOk()->assertSee('صفحه ۲', false);
});

test('نقشه سایت پیج‌های منتشرشده را دارد', function () {
    $approved = makeListing(attributes: ['title' => 'پیج منتشرشده']);
    $pending = makeListing(attributes: ['title' => 'پیج در انتظار', 'status' => ModerationStatus::Pending]);

    $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

    expect(simplexml_load_string($xml))->not->toBeFalse()
        ->and($xml)->toContain(e(route('listings.show', $approved)))
        ->and($xml)->not->toContain(e(route('listings.show', $pending)))
        ->and($xml)->toContain(e(categoryAt('fashion/women')->url()));
});

test('robots.txt خارج از production همه‌چیز را می‌بندد', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
});

test('صفحه ۴۰۴ فارسی و noindex است', function () {
    $this->get('/not-a-page')->assertNotFound()->assertSee('صفحه پیدا نشد')->assertSee('noindex', false);
});

test('صاحب پیج عنوان و توضیح گوگل را ذخیره می‌کند و امتیاز بالا می‌رود', function () {
    $owner = userWithRole();
    $listing = makeListing($owner);
    $before = app(ListingSeoAudit::class)->run($listing)['score'];

    Livewire::actingAs($owner)->test(SeoTools::class, ['listing' => $listing])
        ->set('seoTitle', 'مانتو زنانه با ارسال رایگان')
        ->set('seoDescription', 'خرید آنلاین مانتو و شومیز زنانه')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('مانتو زنانه با ارسال رایگان | نشونی');

    $listing->refresh();
    expect($listing->seo->title)->toBe('مانتو زنانه با ارسال رایگان')
        ->and(app(ListingSeoAudit::class)->run($listing)['score'])->toBeGreaterThan($before);
});

test('ابزار سئو برای پیج دیگران بسته است', function () {
    $listing = makeListing();

    $this->actingAs(userWithRole())->get(route('dashboard.seo', $listing))->assertForbidden();
});
