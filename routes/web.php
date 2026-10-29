<?php

use App\Http\Controllers\GuideController;
use App\Http\Controllers\LogoutController;
use App\Livewire\Auth;
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
