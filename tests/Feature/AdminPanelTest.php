<?php

use App\Enums\ModerationStatus;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Listings;
use App\Livewire\Admin\Users;
use App\Models\OutboxMessage;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Livewire;

test('مدیر کل همه بخش‌های پنل را می‌بیند', function (string $url) {
    $this->actingAs(userWithRole('admin'))->get($url)->assertOk();
})->with(['/panel', '/panel/listings', '/panel/photos', '/panel/reviews', '/panel/reports', '/panel/messages', '/panel/newsletter',
    '/panel/verifications', '/panel/payments', '/panel/categories', '/panel/platforms', '/panel/users', '/panel/roles', '/panel/help']);

test('ناظر فقط بخش‌های مجاز را می‌بیند', function () {
    $moderator = userWithRole('moderator');

    $this->actingAs($moderator)->get('/panel/listings')->assertOk();
    $this->actingAs($moderator)->get('/panel/users')->assertForbidden();
    $this->actingAs($moderator)->get('/panel/roles')->assertForbidden();
    $this->actingAs($moderator)->get('/panel/payments')->assertForbidden();
});

test('کاربر عادی به پنل راه ندارد', function () {
    $this->actingAs(userWithRole())->get('/panel')->assertForbidden();
});

test('ناظر نمی‌تواند پیج حذف کند حتی با درخواست مستقیم', function () {
    $listing = makeListing();

    Livewire::actingAs(userWithRole('moderator'))->test(Listings::class)
        ->call('delete', $listing->id)
        ->assertForbidden();

    expect($listing->fresh())->not->toBeNull();
});

test('اولین تأیید پیج به مشترکان دسته خبر می‌دهد، تأیید دوباره نه', function () {
    $subscriber = userWithRole();
    Subscription::create(['user_id' => $subscriber->id, 'category_id' => categoryAt('fashion')->id]);
    Subscription::create(['contact' => 'guest@example.com', 'category_id' => categoryAt('fashion/women')->id]);
    $listing = makeListing(attributes: ['status' => ModerationStatus::Pending, 'published_at' => null]);

    $panel = Livewire::actingAs(userWithRole('admin'))->test(Listings::class);
    $panel->call('approve', $listing->id);

    expect($listing->fresh()->published_at)->not->toBeNull()
        ->and($subscriber->notifications()->count())->toBe(1)
        ->and(OutboxMessage::count())->toBe(1);

    $listing->update(['status' => ModerationStatus::Pending]);
    $panel->call('approve', $listing->id);

    expect($subscriber->notifications()->count())->toBe(1);
});

test('رد پیج بدون دلیل ممکن نیست', function () {
    $listing = makeListing(attributes: ['status' => ModerationStatus::Pending]);

    Livewire::actingAs(userWithRole('admin'))->test(Listings::class)
        ->call('startReject', $listing->id)
        ->call('reject')->assertHasErrors('reason')
        ->set('reason', 'لینک نادرست')->call('reject')->assertHasNoErrors();

    expect($listing->fresh()->status)->toBe(ModerationStatus::Rejected);
});

test('دسته‌بندی بیشتر از سه سطح نمی‌شود', function () {
    $component = Livewire::actingAs(userWithRole('admin'))->test(Categories::class);

    // زیرمجموعه کردن یک دسته اصلی دوسطحی، عمق را به چهار می‌رساند
    $component->call('edit', categoryAt('fashion')->id)
        ->set('parent_id', categoryAt('digital')->id)
        ->call('save')
        ->assertHasErrors('parent_id');

    // زیر دسته سطح سوم هم نمی‌شود دسته ساخت
    $component->call('create', categoryAt('fashion/women/manteau')->id)
        ->set('name', 'سطح چهارم')->set('slug', 'level-four')
        ->call('save')
        ->assertHasErrors('parent_id');
});

test('کاربر با مدیریت کاربران نمی‌تواند نقش مدیر کل بدهد', function () {
    $manager = User::factory()->create([
        'role_id' => Role::create(['key' => 'support', 'label' => 'پشتیبان', 'permissions' => ['users.manage']])->id,
    ]);
    $target = userWithRole();

    Livewire::actingAs($manager)->test(Users::class)
        ->call('changeRole', $target->id, Role::where('key', 'admin')->value('id'));

    expect($target->fresh()->role->key)->toBe('user');
});
