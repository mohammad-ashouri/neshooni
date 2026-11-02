<?php

namespace App\Http\Middleware;

use App\Support\Maintenance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * وقتی حالت تعمیر روشن است، به همه به جز کارکنان پنل صفحه ۵۰۳ نشان می‌دهد.
 * صفحه ورود (و درخواست‌های Livewire و کپچای آن) باز می‌ماند تا مدیر بتواند وارد شود و حالت تعمیر را خاموش کند.
 */
class CheckMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Maintenance::isActive() || $request->user()?->isStaff() || $this->isAllowed($request)) {
            return $next($request);
        }

        $endsAt = Maintenance::endsAt();
        $endsAt = $endsAt?->isFuture() ? $endsAt : null;

        return response()->view('errors.503', [
            'maintenanceMessage' => Maintenance::message(),
            'endsAt' => $endsAt,
        ], 503, $endsAt ? ['Retry-After' => max(60, (int) now()->diffInSeconds($endsAt))] : ['Retry-After' => 3600]);
    }

    private function isAllowed(Request $request): bool
    {
        return $request->routeIs('login', 'logout')
            || $request->hasHeader('X-Livewire')
            || $request->is('livewire*', 'captcha*');
    }
}
