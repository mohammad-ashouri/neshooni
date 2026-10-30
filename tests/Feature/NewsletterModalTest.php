<?php

use App\Livewire\NewsletterModal;
use App\Services\InterestTracker;
use Livewire\Livewire;

const MODAL_TEXT = 'عضویت در خبرنامه';

test('مودال بعد از چند بازدید از یک دسته، با دقیق‌ترین دسته نشان داده می‌شود', function () {
    $listing = makeListing();
    $url = route('listings.show', $listing);

    foreach (range(1, 4) as $i) {
        $this->get($url)->assertDontSee(MODAL_TEXT);
    }

    $this->get($url)->assertSee(MODAL_TEXT)->assertSee('به «'.categoryAt('fashion/women/manteau')->name.'» علاقه دارید؟');
});

test('مودال در هر session فقط یک بار نشان داده می‌شود', function () {
    $listing = makeListing();
    $url = route('listings.show', $listing);

    foreach (range(1, 5) as $i) {
        $this->get($url);
    }

    $this->get($url)->assertDontSee(MODAL_TEXT);
    $this->get('/')->assertDontSee(MODAL_TEXT);
});

test('بعد از «نه، ممنون» دسته‌های بالاتر هم پیشنهاد نمی‌شوند', function () {
    $tracker = app(InterestTracker::class);
    foreach (range(1, 10) as $i) {
        $tracker->add(categoryAt('fashion/women/manteau')->id, 'view');
    }

    $tracker->dismiss(categoryAt('fashion/women/manteau')->id);

    expect($tracker->suggestion())->toBeNull();
});

test('بین دو پیشنهاد حداقل یک هفته فاصله است', function () {
    $tracker = app(InterestTracker::class);
    $tracker->markOffered();
    session()->forget('newsletter_offered'); // مثل یک بازدید تازه

    expect($tracker->canOffer())->toBeFalse();

    $this->travel(config('site.newsletter_gap_days') + 1)->days();
    expect($tracker->canOffer())->toBeTrue();
});

test('مودال در صفحه ورود و داشبورد نشان داده نمی‌شود', function () {
    session(['guest_pages' => 50]);

    $this->get('/login')->assertDontSee(MODAL_TEXT);

    Livewire::test(NewsletterModal::class)->assertSet('enabled', false)->assertSet('visible', false);
});
