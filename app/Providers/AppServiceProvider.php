<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Seo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // داده سئوی صفحه فعلی؛ برای هر درخواست یک نمونه تازه
        $this->app->scoped(Seo::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configurePermissions();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(8)->letters()->numbers()
            : null,
        );
    }

    /**
     * دسترسی‌ها با spatie/laravel-permission: هر کلید دسترسی (مثل listings.moderate) یک Gate است.
     * پس در Blade می‌توان نوشت @can('listings.moderate') و در route ‌middleware('can:...').
     */
    protected function configurePermissions(): void
    {
        Gate::define('access-panel', fn (User $user) => $user->isStaff());

        Gate::before(function (User $user, string $ability) {
            // فقط کلیدهای «بخش.کار» را اینجا بررسی می‌کنیم؛ بقیه به Policyها می‌رسند.
            // مدیر کل همه را دارد (از جمله roles.manage که به هیچ نقش دیگری داده نمی‌شود).
            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability);
            }

            return null;
        });
    }
}
