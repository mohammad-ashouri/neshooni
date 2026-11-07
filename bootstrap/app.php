<?php

use App\Http\Middleware\CheckMaintenance;
use App\Http\Middleware\CountGuestPages;
use App\Http\Middleware\NoIndex;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // بازگشت از درگاه پرداخت بدون session و CSRF (توضیح در همان فایل)
        then: fn () => Route::group([], base_path('routes/gateway.php')),
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(NoIndex::class);
        $middleware->web(append: [CheckMaintenance::class, CountGuestPages::class]);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
