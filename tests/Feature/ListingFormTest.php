<?php

use App\Enums\ModerationStatus;
use App\Livewire\Dashboard\ListingForm;
use App\Models\Listing;
use App\Models\Platform;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function fillListingForm($component, array $overrides = [])
{
    $category = categoryAt('fashion/women/manteau');
    $values = $overrides + [
        'platform_id' => Platform::where('key', 'instagram')->value('id'),
        'handle' => 'https://www.instagram.com/my.shop/?igsh=abc',
        'title' => 'فروشگاه من',
        'cat1' => $category->root()->id,
        'cat2' => $category->parent_id,
        'cat3' => $category->id,
        'description' => 'فروش پوشاک زنانه با ارسال رایگان',
        'followers' => '۱۲۵۰۰',
    ];

    foreach ($values as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

test('پیج جدید با آیدی تمیز و وضعیت در انتظار ساخته می‌شود', function () {
    $user = userWithRole();

    fillListingForm(Livewire::actingAs($user)->test(ListingForm::class))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $listing = Listing::first();
    expect($listing->handle)->toBe('my.shop')
        ->and($listing->followers)->toBe(12500)
        ->and($listing->status)->toBe(ModerationStatus::Pending)
        ->and($listing->category->slug)->toBe('manteau');
});

test('پیج تکراری پذیرفته نمی‌شود', function () {
    makeListing(attributes: ['handle' => 'my.shop']);

    fillListingForm(Livewire::actingAs(userWithRole())->test(ListingForm::class))
        ->call('save')
        ->assertHasErrors('handle');
});

test('ویرایش اطلاعات اصلی، پیج تأییدشده را دوباره به صف بررسی می‌برد', function () {
    $owner = userWithRole();
    $listing = makeListing($owner);

    Livewire::actingAs($owner)->test(ListingForm::class, ['listing' => $listing])
        ->set('title', 'نام جدید')
        ->call('save')
        ->assertHasNoErrors();

    expect($listing->fresh()->status)->toBe(ModerationStatus::Pending);
});

test('افزودن عکس گالری پیج را از نمایش خارج نمی‌کند', function () {
    Storage::fake('public');
    $owner = userWithRole();
    $listing = makeListing($owner);

    Livewire::actingAs($owner)->test(ListingForm::class, ['listing' => $listing])
        ->set('newPhotos', [UploadedFile::fake()->image('a.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    expect($listing->fresh()->status)->toBe(ModerationStatus::Approved)
        ->and($listing->photos()->first()->status)->toBe(ModerationStatus::Pending);
});

test('کاربر نمی‌تواند پیج دیگران را ویرایش کند', function () {
    $listing = makeListing();

    $this->actingAs(userWithRole())->get(route('dashboard.listings.edit', $listing))->assertForbidden();
});

test('سایت فروشگاهی با آدرس تمیز ثبت می‌شود', function () {
    $user = userWithRole();
    $category = categoryAt('fashion/women/manteau');

    $this->actingAs($user)->get(route('dashboard.websites.create'))
        ->assertOk()
        ->assertSee('ثبت سایت فروشگاهی')
        ->assertDontSee('تعداد دنبال‌کننده');

    Livewire::actingAs($user)->test(ListingForm::class, ['website' => true])
        ->set('handle', 'HTTPS://www.MyShop.ir/?utm_source=x')
        ->set('title', 'فروشگاه اینترنتی من')
        ->set('cat1', $category->root()->id)
        ->set('description', 'فروش آنلاین پوشاک زنانه با ارسال رایگان')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $listing = Listing::first();
    expect($listing->handle)->toBe('myshop.ir')
        ->and($listing->isWebsite())->toBeTrue()
        ->and($listing->externalUrl())->toBe('https://myshop.ir')
        ->and($listing->displayHandle())->toBe('myshop.ir')
        ->and($listing->status)->toBe(ModerationStatus::Pending);
});

test('سایت تکراری پذیرفته نمی‌شود و نوع فرم از مرورگر عوض نمی‌شود', function () {
    $website = Platform::where('key', 'website')->first();
    makeListing(attributes: ['platform_id' => $website->id, 'handle' => 'myshop.ir']);

    $form = Livewire::actingAs(userWithRole())->test(ListingForm::class, ['website' => true]);
    fillListingForm($form, ['platform_id' => $website->id, 'handle' => 'www.myshop.ir/'])
        ->call('save')
        ->assertHasErrors('handle');

    expect(fn () => $form->set('isWebsite', false))->toThrow(Exception::class);
});

test('صفحه ثبت سایت با پلتفرم غیرفعال ۴۰۴ می‌دهد', function () {
    Platform::where('key', 'website')->update(['is_active' => false]);

    $this->actingAs(userWithRole())->get(route('dashboard.websites.create'))->assertNotFound();
});

test('آدرس سایت نامعتبر پذیرفته نمی‌شود', function () {
    expect(Platform::cleanWebsite('my shop'))->toBe('')
        ->and(Platform::cleanWebsite('localhost'))->toBe('')
        ->and(Platform::cleanWebsite('https://Shop.example.com/fa/'))->toBe('shop.example.com/fa')
        ->and(Platform::cleanWebsite('فروشگاه.ایران'))->toBe('فروشگاه.ایران');
});

test('در فرم ثبت پیج، سایت فروشگاهی قابل انتخاب نیست', function () {
    fillListingForm(Livewire::actingAs(userWithRole())->test(ListingForm::class), [
        'platform_id' => Platform::where('key', 'website')->value('id'),
        'handle' => 'myshop.ir',
    ])->call('save')->assertHasErrors('platform_id');
});

test('روش‌های خرید قسطی با ترتیب ثابت ذخیره می‌شوند', function () {
    fillListingForm(Livewire::actingAs(userWithRole())->test(ListingForm::class), [
        'installment' => true,
        'installments' => ['cheque', 'snapppay'],
        'installment_note' => ' ۴ قسط بدون پیش‌پرداخت ',
    ])->call('save')->assertHasNoErrors();

    $listing = Listing::first();
    expect($listing->installments)->toBe(['snapppay', 'cheque'])
        ->and($listing->installment_note)->toBe('۴ قسط بدون پیش‌پرداخت')
        ->and(array_keys($listing->installmentMethods()))->toBe(['snapppay', 'cheque']);
});

test('خرید قسطی بدون انتخاب روش یا با روش نامعتبر پذیرفته نمی‌شود', function () {
    fillListingForm(Livewire::actingAs(userWithRole())->test(ListingForm::class), ['installment' => true])
        ->call('save')
        ->assertHasErrors('installments');

    fillListingForm(Livewire::actingAs(userWithRole())->test(ListingForm::class), [
        'installment' => true,
        'installments' => ['hacked'],
    ])->call('save')->assertHasErrors('installments.*');
});

test('برداشتن تیک خرید قسطی روش‌ها را پاک می‌کند', function () {
    $owner = userWithRole();
    $listing = makeListing($owner, attributes: ['installments' => ['digipay'], 'installment_note' => 'تا ۶ ماه']);

    Livewire::actingAs($owner)->test(ListingForm::class, ['listing' => $listing])
        ->assertSet('installment', true)
        ->set('installment', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($listing->fresh()->installments)->toBeNull()
        ->and($listing->fresh()->installment_note)->toBeNull();
});
