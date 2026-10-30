<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('ورود')]
class Login extends Component
{
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

        if (! Auth::attempt(['mobile' => $this->mobile, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key);
            $this->addError('mobile', 'شماره موبایل یا رمز عبور اشتباه است.');

            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();
        session()->flash('toast', ['message' => 'خوش آمدید '.Auth::user()->name]);

        return $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
