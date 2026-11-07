<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\HasCodeTimer;
use App\Models\Role;
use App\Models\User;
use App\Services\Sms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * ثبت‌نام دومرحله‌ای.
 * مرحله ۱: اطلاعات حساب → ارسال کد ۵ رقمی | مرحله ۲: کد → ساخت حساب
 *
 * کد هش‌شده و به شماره موبایل گره‌خورده در کش نگه داشته می‌شود و دو دقیقه و نیم اعتبار دارد.
 */
#[Title('ثبت‌نام')]
class Register extends Component
{
    use HasCodeTimer;

    private const MAX_CODE_ATTEMPTS = 5;

    public int $step = 1;

    public string $name = '';

    public string $mobile = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $code = '';

    public function sendCode(): void
    {
        $this->mobile = en_digits($this->mobile);
        $this->validateAccount();

        // کد قبلی همین شماره هنوز معتبر است؛ همان را وارد کند
        if ($wait = $this->codeWaitSeconds($this->cacheKey())) {
            $this->notifyCodeStillValid($wait);
            $this->step = 2;

            return;
        }

        if (RateLimiter::tooManyAttempts('register:'.$this->mobile, 3)) {
            $this->addError('mobile', 'چند دقیقه دیگر دوباره تلاش کنید.');

            return;
        }
        RateLimiter::hit('register:'.$this->mobile, 300);

        $code = (string) random_int(10000, 99999);
        Cache::put($this->cacheKey(), ['hash' => Hash::make($code), 'attempts' => 0], now()->addSeconds(self::CODE_SECONDS));
        $this->startCodeTimer($this->cacheKey());

        app(Sms::class)->sendPattern($this->mobile, config('services.melipayamak.register_body_id'), [$code]);

        if (app()->isLocal()) {
            $this->dispatch('toast', message: 'کد آزمایشی (فقط در محیط توسعه): '.$code, type: 'info');
        }

        $this->code = '';
        $this->step = 2;
    }

    public function register()
    {
        $this->code = en_digits($this->code);
        $this->validate(['code' => ['required', 'digits:5']]);

        $entry = Cache::get($this->cacheKey());

        if (! $entry || $entry['attempts'] >= self::MAX_CODE_ATTEMPTS) {
            Cache::forget($this->cacheKey());
            $this->addError('code', 'کد منقضی شده. دوباره کد بگیرید.');

            return;
        }

        if (! Hash::check($this->code, $entry['hash'])) {
            $entry['attempts']++;
            // تلاش اشتباه زمان اعتبار کد را تمدید نمی‌کند
            Cache::put($this->cacheKey(), $entry, max(1, $this->codeWaitSeconds($this->cacheKey())));
            $this->addError('code', 'کد اشتباه است.');

            return;
        }

        // ممکن است در این فاصله کسی با همین شماره ثبت‌نام کرده باشد؛ خطا در فرم مرحله ۱ نمایش داده می‌شود
        try {
            $data = $this->validateAccount();
        } catch (ValidationException $e) {
            $this->step = 1;

            throw $e;
        }
        Cache::forget($this->cacheKey());

        $user = User::create($data)->setRole(Role::USER);

        Auth::login($user, true);
        session()->regenerate();
        session()->flash('toast', ['message' => 'حساب شما ساخته شد. حالا اولین پیج خود را ثبت کنید.']);

        return $this->redirectRoute('dashboard.listings.create', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }

    private function validateAccount(): array
    {
        return $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'mobile.regex' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.',
            'mobile.unique' => 'با این شماره قبلاً ثبت‌نام شده است. وارد شوید.',
        ]);
    }

    private function cacheKey(): string
    {
        return 'register-code:'.$this->mobile;
    }
}
