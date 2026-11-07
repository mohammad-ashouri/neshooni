{{--
    قالب پنل مدیریت: همان قالب سایت + منوی کناری پنل.
    هر بخش فقط به کسی نشان داده می‌شود که دسترسی آن را دارد (config/permissions.php).
--}}
@use('App\Enums\ModerationStatus')
@use('App\Models')

@php
    $menu = [
        ['admin.dashboard', '📊', 'داشبورد', 'stats.view', null],
        ['admin.listings', '📋', 'پیج‌ها', 'listings.moderate', fn () => Models\Listing::where('status', ModerationStatus::Pending)->count()],
        ['admin.photos', '🖼️', 'تصاویر', 'images.moderate', fn () => Models\ListingPhoto::where('status', ModerationStatus::Pending)->count()],
        ['admin.reviews', '💬', 'نظرات', 'reviews.moderate', fn () => Models\Review::where('status', ModerationStatus::Pending)->count()],
        ['admin.reports', '🚩', 'گزارش تخلف', 'reports.handle', fn () => Models\Report::where('status', 'open')->count()],
        ['admin.messages', '✉️', 'پیام‌ها', 'messages.read', fn () => Models\ContactMessage::whereNull('read_at')->count()],
        ['admin.newsletter', '📬', 'خبرنامه', 'newsletter.view', null],
        ['admin.verifications', '✅', 'نشان تأیید', 'verifications.manage', fn () => Models\VerificationRequest::where('status', ModerationStatus::Pending)->count()],
        ['admin.payments', '💳', 'پرداخت‌ها', 'payments.view', null],
        ['admin.categories', '🗂️', 'دسته‌بندی‌ها', 'categories.manage', null],
        ['admin.platforms', '🌐', 'پلتفرم‌ها', 'platforms.manage', null],
        ['admin.users', '👥', 'کاربران', 'users.manage', null],
        ['admin.maintenance', '🛠️', 'حالت تعمیر', 'site.maintenance', null],
        ['admin.enamad', '🛡️', 'نماد اینماد', 'site.enamad', null],
        ['admin.activity', '🗂️', 'تاریخچه اقدامات', 'activity.view', null],
        ['admin.roles', '🔐', 'نقش‌ها', 'roles.manage', null],
    ];
@endphp

<x-layouts::app :title="$title ?? 'پنل مدیریت'">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[14rem_1fr]">
        <aside class="h-fit min-w-0 lg:sticky lg:top-20">
            <div class="mb-3 rounded-2xl bg-linear-to-l from-violet-600 to-fuchsia-600 px-4 py-3 text-white">
                <div class="text-xs text-white/60">پنل {{ auth()->user()->isAdmin() ? 'مدیریت' : auth()->user()->roleLabel() }}</div>
                <div class="truncate font-bold">{{ auth()->user()->name }}</div>
            </div>
            <nav class="card flex gap-1 overflow-x-auto p-2 lg:flex-col">
                @foreach ($menu as [$route, $icon, $label, $permission, $counter])
                    @can($permission)
                        @php($count = $counter ? $counter() : 0)
                        <a href="{{ route($route) }}" wire:navigate @class(['cursor-pointer side-link shrink-0', 'side-link-active' => request()->routeIs($route)])>
                            <span>{{ $icon }}</span>
                            <span class="flex-1">{{ $label }}</span>
                            @if ($count)
                                <span class="rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white">{{ fa_number($count) }}</span>
                            @endif
                        </a>
                    @endcan
                @endforeach
                <a href="{{ route('admin.help') }}" wire:navigate @class(['cursor-pointer side-link shrink-0', 'side-link-active' => request()->routeIs('admin.help')])><span>❓</span> راهنمای پنل</a>
            </nav>
        </aside>

        <section class="min-w-0">
            {{ $slot }}
        </section>
    </div>
</x-layouts::app>
