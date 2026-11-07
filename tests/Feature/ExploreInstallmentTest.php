<?php

use App\Livewire\Pages\Explore;
use Livewire\Livewire;

test('فیلتر خرید قسطی پیج‌های دارای اقساط و روش انتخاب‌شده را نشان می‌دهد', function () {
    $snapp = makeListing(attributes: ['installments' => ['snapppay', 'cheque']]);
    $digi = makeListing(attributes: ['installments' => ['digipay']]);
    makeListing();

    $ids = fn (array $expected) => fn ($listings) => $listings->pluck('id')->sort()->values()->all() === collect($expected)->sort()->values()->all();

    Livewire::test(Explore::class)
        ->call('$set', 'installment', 'any')
        ->assertViewHas('listings', $ids([$snapp->id, $digi->id]))
        ->assertSee('ترب‌پی')
        ->call('$set', 'installment', 'cheque')
        ->assertViewHas('listings', $ids([$snapp->id]))
        ->call('resetFilters')
        ->assertSet('installment', '')
        ->assertViewHas('listings', fn ($listings) => $listings->count() === 3);
});

test('مقدار نامعتبر فیلتر خرید قسطی نادیده گرفته می‌شود', function () {
    makeListing();

    Livewire::withQueryParams(['installment' => 'hacked'])->test(Explore::class)
        ->assertSet('installment', '')
        ->assertViewHas('listings', fn ($listings) => $listings->count() === 1);
});
