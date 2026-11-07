<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| بازگشت از درگاه پرداخت
|--------------------------------------------------------------------------
| این مسیر عمداً بیرون از گروه web است: بانک‌ها معمولاً با POST از سایت خودشان برمی‌گردند
| و مرورگر کوکی session را در POST بین‌سایتی نمی‌فرستد. اگر session اینجا شروع شود،
| کوکی تازه جای کوکی کاربر را می‌گیرد و او از حساب خارج می‌شود؛ توکن CSRF هم وجود ندارد.
| امنیت این مسیر از استعلام تراکنش با خود درگاه است، نه از session.
*/

Route::match(['get', 'post'], '/payments/{payment}/callback', [PaymentController::class, 'callback'])
    ->middleware(SubstituteBindings::class)
    ->name('payments.callback');
