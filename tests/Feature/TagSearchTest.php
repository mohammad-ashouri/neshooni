<?php

use App\Enums\ModerationStatus;
use App\Livewire\Dashboard\ListingForm;
use App\Livewire\Pages\Explore;
use App\Models\Tag;
use Livewire\Livewire;

$ids = fn (array $expected) => fn ($listings) => $listings->pluck('id')->sort()->values()->all() === collect($expected)->sort()->values()->all();

test('برچسب‌ها یکدست می‌شوند', function () {
    expect(Tag::normalize('  #كافه_دنج  '))->toBe('کافه دنج')
        ->and(Tag::parse('کافه، تهرانسر,#ارسال رایگان, x, کافه'))->toBe(['کافه', 'تهرانسر', 'ارسال رایگان']);
});

test('جستجوی «کافه توی تهرانسر» کافه‌های تهرانسر را پیدا می‌کند', function () use ($ids) {
    $byTags = makeListing(attributes: ['title' => 'کافه نارنج']);
    $byTags->syncTags(['کافه', 'تهرانسر']);

    $byAddress = makeListing(attributes: ['title' => 'بوتیک قهوه', 'address' => 'تهرانسر، بلوار اصلی']);
    $byAddress->syncTags(['کافه']);

    $otherArea = makeListing(attributes: ['title' => 'کافه ونک', 'address' => 'ونک']);
    $notCafe = makeListing(attributes: ['title' => 'مانتو سرا', 'address' => 'تهرانسر']);

    Livewire::test(Explore::class)
        ->set('q', 'کافه توی تهرانسر')
        ->assertViewHas('listings', $ids([$byTags->id, $byAddress->id]))
        ->set('q', 'كافه')
        ->assertViewHas('listings', $ids([$byTags->id, $byAddress->id, $otherArea->id]));
});

test('جستجو نام دسته را هم می‌گردد', function () use ($ids) {
    $listing = makeListing(); // دسته مانتو
    makeListing(attributes: ['category_id' => categoryAt('fashion')->id]);

    Livewire::test(Explore::class)
        ->set('q', 'مانتو')
        ->assertViewHas('listings', $ids([$listing->id]));
});

test('فیلتر برچسب و برچسب‌های پرطرفدار', function () use ($ids) {
    $cafe = makeListing();
    $cafe->syncTags(['کافه']);
    makeListing()->syncTags(['گل']);
    makeListing(attributes: ['status' => ModerationStatus::Pending])->syncTags(['پنهان']);

    Livewire::withQueryParams(['tag' => '#كافه'])->test(Explore::class)
        ->assertSet('tag', 'کافه')
        ->assertViewHas('listings', $ids([$cafe->id]))
        ->assertViewHas('popularTags', fn ($tags) => $tags->sort()->values()->all() === ['کافه', 'گل'])
        ->call('resetFilters')
        ->assertSet('tag', '');
});

test('برچسب‌ها در فرم ذخیره می‌شوند و تغییرشان پیج را به صف بررسی می‌برد', function () {
    $user = userWithRole();
    $listing = makeListing($user);
    $listing->syncTags(['کافه']);

    Livewire::actingAs($user)->test(ListingForm::class, ['listing' => $listing])
        ->assertSet('tags', ['کافه'])
        ->set('tagInput', 'تهرانسر، #دنج')
        ->call('addTag')
        ->assertSet('tags', ['کافه', 'تهرانسر', 'دنج'])
        ->call('removeTag', 2)
        ->set('tagInput', 'ارسال رایگان') // بدون Enter هم ذخیره می‌شود
        ->call('save')
        ->assertHasNoErrors();

    $listing->refresh();
    expect(collect($listing->tagNames())->sort()->values()->all())->toBe(['ارسال رایگان', 'تهرانسر', 'کافه'])
        ->and($listing->status)->toBe(ModerationStatus::Pending);
});

test('ذخیره بدون تغییر برچسب پیج تأییدشده را از نمایش خارج نمی‌کند', function () {
    $user = userWithRole();
    $listing = makeListing($user);
    $listing->syncTags(['کافه', 'تهرانسر']);

    Livewire::actingAs($user)->test(ListingForm::class, ['listing' => $listing])
        ->call('save')
        ->assertHasNoErrors();

    expect($listing->refresh()->status)->toBe(ModerationStatus::Approved);
});

test('بیشتر از سقف برچسب پذیرفته نمی‌شود', function () {
    $names = collect(range(1, Tag::MAX_PER_LISTING + 3))->map(fn ($i) => "برچسب{$i}")->implode('،');

    Livewire::actingAs(userWithRole())->test(ListingForm::class)
        ->set('tagInput', $names)
        ->call('addTag')
        ->assertCount('tags', Tag::MAX_PER_LISTING);
});

test('برچسب‌های فارسی اسلاگ جدا می‌گیرند و دوباره ساخته نمی‌شوند', function () {
    makeListing()->syncTags(['کافه', 'کافه دنج']);
    makeListing()->syncTags(['کافه']);

    expect(Tag::count())->toBe(2)
        ->and(Tag::findFromString('کافه دنج')->slug)->toBe('کافه-دنج');
});
