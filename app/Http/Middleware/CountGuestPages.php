<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تعداد صفحه‌هایی که مهمان دیده را در session می‌شمارد
 * تا بعد از چند صفحه خبرنامه عمومی به او پیشنهاد شود (InterestTracker).
 */
class CountGuestPages
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->user() && ! $request->hasHeader('X-Livewire')) {
            $request->session()->increment('guest_pages');
        }

        return $next($request);
    }
}
