<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\HasCodeTimer;
use App\Models\User;
use App\Services\Sms;
use App\Support\Maintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * ورود با موبایل و رمز.
 *
 * ورود دومرحله‌ای: اعضای پنل (مدیر، ناظر و هر نقشی که به پنل دسترسی دارد) بعد از رمز درست
 * یک کد پیامکی هم وارد می‌کنند. شناسه کاربر در انتظار کد در session است، نه در ویژگی عمومی
 * کامپوننت، تا از مرورگر قابل دست‌کاری نباشد.
 */
#[Title('ورود')]
class Login extends Component
{
    use HasCodeTimer;

    public string $mobile = '';

    public string $password = '';

    public bool $remember = true;

    /** کد امنیتی (کپچا) که کاربر از روی تصویر می‌خواند */
    public string $captcha = '';

    /**
     * با هر تغییر این عدد، آدرس تصویر کپچا عوض می‌شود و مرورگر تصویر و کد تازه می‌گیرد.
     * کد کپچا در session است و بعد از هر بار بررسی موفق مصرف می‌شود؛ پس بعد از هر تلاش ورود باید عوض شود.
     */
    public int $captchaKey = 0;

    /** ۱: موبایل و رمز | ۲: کد پیامکی (فقط اعضای پنل) */
    public int $step = 1;

    public string $code = '';

    public function refreshCaptcha(): void
    {
        $this->captcha = '';
        $this->captchaKey++;
    }

    public function login()
    {
        $this->mobile = en_digits($this->mobile);
        $this->captcha = en_digits(trim($this->captcha));
        $input = $this->only(['mobile', 'password', 'captcha']);

        // تصویر از همین حالا عوض می‌شود؛ چه ورود موفق باشد چه خطا بدهد
        $this->refreshCaptcha();

        validator($input, [
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'password' => ['required'],
            'captcha' => ['required', 'captcha'],
        ], [
            'mobile.regex' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.',
            'captcha.captcha' => 'کد امنیتی اشتباه است؛ کد تصویر جدید را وارد کنید.',
        ])->validate();

        // جلوگیری از حدس زدن رمز: حداکثر ۵ تلاش در دقیقه برای هر شماره
        $key = 'login:'.$this->mobile.'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('mobile', 'تلاش زیاد بود. '.fa_number(RateLimiter::availableIn($key)).' ثانیه دیگر دوباره امتحان کنید.');

            return;
        }

        $user = User::where('mobile', $this->mobile)->first();
        if (! $user || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key);
            $this->addError('mobile', 'شماره موبایل یا رمز عبور اشتباه است.');

            return;
        }

        RateLimiter::clear($key);

        // در حالت تعمیر فقط مدیر وارد می‌شود تا حالت تعمیر را مدیریت کند
        if (Maintenance::isActive() && ! $user->isAdmin()) {
            $this->addError('mobile', 'سایت در حالت تعمیر است و فعلاً فقط مدیر می‌تواند وارد شود.');

            return;
        }

        if ($user->isStaff() && config('auth.staff_two_factor')) {
            session()->put('login.2fa', ['user_id' => $user->id, 'remember' => $this->remember]);
            $this->password = '';
            $this->sendCode($user);
            $this->step = 2;

            return;
        }

        return $this->finish($user, $this->remember);
    }

    /** مرحله دوم ورود اعضای پنل */
    public function verifyCode()
    {
        $this->code = en_digits(trim($this->code));
        $this->validate(['code' => ['required', 'digits:5']], ['code.digits' => 'کد ۵ رقمی است.']);

        $pending = session('login.2fa');
        $user = $pending ? User::find($pending['user_id']) : null;
        if (! $user) {
            return $this->restart('زمان ورود تمام شد. دوباره وارد شوید.');
        }

        // بعد از ۵ کد اشتباه، کد باطل می‌شود تا نشود همه ترکیب‌ها را امتحان کرد
        $attemptsKey = '2fa-verify:'.$user->id;
        if (RateLimiter::tooManyAttempts($attemptsKey, 5)) {
            Cache::forget('2fa:'.$user->id);

            return $this->restart('کد بیش از حد اشتباه وارد شد. دوباره وارد شوید.');
        }

        $hash = Cache::get('2fa:'.$user->id);
        if (! $hash || ! Hash::check($this->code, $hash)) {
            RateLimiter::hit($attemptsKey, 600);
            $this->addError('code', 'کد اشتباه است یا منقضی شده.');

            return;
        }

        RateLimiter::clear($attemptsKey);
        Cache::forget('2fa:'.$user->id);
        session()->forget('login.2fa');

        return $this->finish($user, (bool) $pending['remember']);
    }

    public function resendCode(): void
    {
        $pending = session('login.2fa');
        $user = $pending ? User::find($pending['user_id']) : null;
        if (! $user) {
            $this->restart('زمان ورود تمام شد. دوباره وارد شوید.');

            return;
        }

        $this->sendCode($user);
    }

    /** برگشت به مرحله اول ورود */
    public function restart(?string $message = null): void
    {
        session()->forget('login.2fa');
        $this->reset('step', 'code', 'password', 'codeExpiresAt');
        if ($message) {
            $this->addError('mobile', $message);
        }
    }

    private function sendCode(User $user): void
    {
        // کد قبلی هنوز معتبر است؛ تا پایان تایمر کد تازه فرستاده نمی‌شود
        if ($wait = $this->codeWaitSeconds('2fa:'.$user->id)) {
            $this->notifyCodeStillValid($wait);

            return;
        }

        // حداکثر ۳ پیامک در ۵ دقیقه برای هر حساب
        $limitKey = '2fa-send:'.$user->id;
        if (RateLimiter::tooManyAttempts($limitKey, 3)) {
            $this->addError('code', 'پیامک زیادی فرستاده شد. '.fa_number(RateLimiter::availableIn($limitKey)).' ثانیه دیگر دوباره امتحان کنید.');

            return;
        }
        RateLimiter::hit($limitKey, 300);

        $code = (string) random_int(10000, 99999);
        Cache::put('2fa:'.$user->id, Hash::make($code), now()->addSeconds(self::CODE_SECONDS));
        RateLimiter::clear('2fa-verify:'.$user->id);
        $this->startCodeTimer('2fa:'.$user->id);

        app(Sms::class)->sendPattern($user->mobile, config('services.melipayamak.otp_body_id'), [$code]);

        if (app()->isLocal()) {
            $this->dispatch('toast', message: 'کد آزمایشی (فقط در محیط توسعه): '.$code, type: 'info');
        }
    }

    private function finish(User $user, bool $remember)
    {
        Auth::login($user, $remember);
        session()->regenerate();
        session()->flash('toast', ['message' => 'خوش آمدید '.$user->name]);

        if (Maintenance::isActive()) {
            return $this->redirectRoute('admin.maintenance', navigate: true);
        }

        return $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login', [
            'maskedMobile' => substr($this->mobile, 0, 4).'***'.substr($this->mobile, -4),
        ]);
    }
}
