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
