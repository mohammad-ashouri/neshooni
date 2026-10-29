<?php

use App\Http\Controllers\GuideController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ReceiptController;
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
    Route::get('/feature/{listing}', Dashboard\Feature::class)->name('.feature');
    Route::get('/verification', Dashboard\Verification::class)->name('.verification');
    Route::get('/payments', Dashboard\Payments::class)->name('.payments');
});

Route::middleware('auth')->group(function () {
    // درگاه پرداخت نمایشی؛ در نسخه واقعی به درگاه بانک منتقل می‌شود
    Route::get('/checkout/{payment}', Pages\Checkout::class)->name('checkout');
    Route::get('/receipts/{payment}', [ReceiptController::class, 'show'])->name('receipts.show');
});

// صفحه استعلام رسید (لینک QR روی رسید چاپی)
Route::get('/r/{ref}', [ReceiptController::class, 'verify'])->name('receipts.verify');
