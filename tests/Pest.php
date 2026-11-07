<?php

use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Platform;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PlatformSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| تنظیمات تست
|--------------------------------------------------------------------------
| هر تست روی دیتابیس تازه (SQLite در حافظه) اجرا می‌شود و نقش‌ها، پلتفرم‌ها
| و دسته‌ها از seeder اصلی ساخته می‌شوند. کپچا در phpunit.xml غیرفعال است.
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => test()->seed([RoleSeeder::class, PlatformSeeder::class, CategorySeeder::class]))
    ->in('Feature');

/** پیامک‌ها را جعل می‌کند و آخرین کد فرستاده‌شده را برمی‌گرداند. */
function fakeSmsAndCapture(): Closure
{
    config(['services.melipayamak.key' => 'test-key', 'services.melipayamak.register_body_id' => 777, 'services.melipayamak.otp_body_id' => 555]);
    Http::fake(['console.melipayamak.com/*' => Http::response(['recId' => 123456789])]);

    return fn () => Http::recorded()->last()[0]['args'][0];
}

/** کاربر با نقش مشخص: 'user'، 'moderator' یا 'admin' */
function userWithRole(string $role = 'user'): User
{
    return User::factory()->create()->setRole($role);
}

/** دسته با مسیر نامک‌ها، مثل 'fashion/women/manteau' */
function categoryAt(string $path): Category
{
    $parentId = null;
    foreach (explode('/', $path) as $slug) {
        $category = Category::where('slug', $slug)->where('parent_id', $parentId)->firstOrFail();
        $parentId = $category->id;
    }

    return $category;
}

/** یک پیج تأییدشده اینستاگرامی برای تست */
function makeListing(?User $owner = null, array $attributes = []): Listing
{
    static $counter = 0;
    $counter++;

    return Listing::create($attributes + [
        'user_id' => ($owner ?? userWithRole())->id,
        'platform_id' => Platform::where('key', 'instagram')->value('id'),
        'category_id' => categoryAt('fashion/women/manteau')->id,
        'handle' => "shop.{$counter}",
        'title' => "فروشگاه {$counter}",
        'description' => 'توضیح آزمایشی برای پیج',
        'status' => ModerationStatus::Approved,
        'published_at' => now(),
        'country' => 'IR',
    ])->refresh(); // مثل برنامه واقعی، مقدارهای پیش‌فرض دیتابیس هم خوانده شوند
}
