<?php

use App\Http\Controllers\GuideController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Admin;
use App\Livewire\Auth;
use App\Livewire\Dashboard;
use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| صفحات عمومی
|--------------------------------------------------------------------------
*/

Route::get('/', Pages\Home::class)->name('home');
// مسیر دسته می‌تواند یک تا سه بخش داشته باشد: /c/fashion/women/manteau
Route::get('/c/{category?}', Pages\Explore::class)->where('category', '.*')->name('explore');
Route::get('/l/{listing}', Pages\ListingShow::class)->name('listings.show');
Route::get('/u/{user}', Pages\BusinessProfile::class)->name('business.show');
Route::get('/contact', Pages\Contact::class)->name('contact');
Route::get('/unsubscribe/{token}', Pages\Unsubscribe::class)->name('unsubscribe');
Route::view('/about', 'pages.about')->name('about');
Route::get('/help', [GuideController::class, 'site'])->name('help');

/*
|--------------------------------------------------------------------------
| ورود و ثبت‌نام
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', Auth\Login::class)->name('login');
    Route::get('/register', Auth\Register::class)->name('register');
    Route::get('/forgot-password', Auth\ForgotPassword::class)->name('password.request');
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| داشبورد کاربر (صاحب کسب‌وکار)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('dashboard')->name('dashboard')->group(function () {
    Route::get('/', Dashboard\MyListings::class);
    Route::get('/listings/create', Dashboard\ListingForm::class)->name('.listings.create');
    Route::get('/listings/{listing}/edit', Dashboard\ListingForm::class)->name('.listings.edit');
    Route::get('/profile', Dashboard\Profile::class)->name('.profile');
    Route::get('/following', Dashboard\Following::class)->name('.following');
    Route::get('/alerts', Dashboard\Alerts::class)->name('.alerts');
    Route::get('/stats', Dashboard\Stats::class)->name('.stats');
    Route::get('/reviews', Dashboard\Reviews::class)->name('.reviews');
    Route::get('/banner/{listing?}', Dashboard\Banner::class)->name('.banner');
    Route::get('/seo/{listing?}', Dashboard\SeoTools::class)->name('.seo');
    Route::get('/feature/{listing}', Dashboard\Feature::class)->name('.feature');
    Route::get('/verification', Dashboard\Verification::class)->name('.verification');
    Route::get('/payments', Dashboard\Payments::class)->name('.payments');
});

Route::middleware('auth')->group(function () {
    // صفحه پرداخت: انتخاب درگاه و انتقال به بانک (بازگشت از بانک در routes/gateway.php است)
    Route::get('/checkout/{payment}', Pages\Checkout::class)->name('checkout');
    Route::post('/payments/{payment}/start', [PaymentController::class, 'start'])->middleware('throttle:10,1')->name('payments.start');
    Route::get('/payments/{payment}/finish', [PaymentController::class, 'finish'])->name('payments.finish');
    Route::get('/receipts/{payment}', [ReceiptController::class, 'show'])->name('receipts.show');
});

// صفحه استعلام رسید (لینک QR روی رسید چاپی)
Route::get('/r/{ref}', [ReceiptController::class, 'verify'])->name('receipts.verify');

/*
|--------------------------------------------------------------------------
| پنل مدیریت
|--------------------------------------------------------------------------
| ورود به پنل با Gate «access-panel» و هر بخش با دسترسی خودش کنترل می‌شود.
| داشبورد خودش کسی را که آمار نمی‌بیند به اولین بخش مجازش می‌فرستد.
*/

Route::middleware(['auth', 'can:access-panel'])->prefix('panel')->name('admin.')->group(function () {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('/listings', Admin\Listings::class)->name('listings')->middleware('can:listings.moderate');
    Route::get('/photos', Admin\Photos::class)->name('photos')->middleware('can:images.moderate');
    Route::get('/reviews', Admin\Reviews::class)->name('reviews')->middleware('can:reviews.moderate');
    Route::get('/reports', Admin\Reports::class)->name('reports')->middleware('can:reports.handle');
    Route::get('/messages', Admin\Messages::class)->name('messages')->middleware('can:messages.read');
    Route::get('/newsletter', Admin\Newsletter::class)->name('newsletter')->middleware('can:newsletter.view');
    Route::get('/verifications', Admin\Verifications::class)->name('verifications')->middleware('can:verifications.manage');
    Route::get('/payments', Admin\Payments::class)->name('payments')->middleware('can:payments.view');
    Route::get('/categories', Admin\Categories::class)->name('categories')->middleware('can:categories.manage');
    Route::get('/platforms', Admin\Platforms::class)->name('platforms')->middleware('can:platforms.manage');
    Route::get('/users', Admin\Users::class)->name('users')->middleware('can:users.manage');
    Route::get('/maintenance', Admin\Maintenance::class)->name('maintenance')->middleware('can:site.maintenance');
    Route::get('/enamad', Admin\Enamad::class)->name('enamad')->middleware('can:site.enamad');
    Route::get('/roles', Admin\Roles::class)->name('roles')->middleware('can:roles.manage');
    Route::get('/activity', Admin\Activity::class)->name('activity')->middleware('can:activity.view');
    Route::get('/help', [GuideController::class, 'panel'])->name('help');
});

/*
|--------------------------------------------------------------------------
| سئو
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
