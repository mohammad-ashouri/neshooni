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

    public function login()
    {
        $this->mobile = en_digits($this->mobile);
        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'password' => ['required'],
        ], ['mobile.regex' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.']);

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
