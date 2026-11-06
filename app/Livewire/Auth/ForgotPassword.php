<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\Sms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * بازیابی رمز با کد یک‌بارمصرف.
 * مرحله ۱: موبایل → ساخت کد ۵ رقمی | مرحله ۲: کد + رمز جدید
 *
 * کد در جدول password_reset_tokens (هش‌شده) ذخیره و با الگوی خدماتی ملی پیامک فرستاده می‌شود؛
 * در محیط توسعه برای تست روی صفحه هم نمایش داده می‌شود.
 */
#[Title('بازیابی رمز عبور')]
class ForgotPassword extends Component
{
    public int $step = 1;

    public string $mobile = '';

    public string $code = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function sendCode(): void
    {
        $this->mobile = en_digits($this->mobile);
        $this->validate(['mobile' => ['required', 'regex:/^09\d{9}$/']], ['mobile.regex' => 'شماره موبایل معتبر نیست.']);

        if (RateLimiter::tooManyAttempts('reset:'.$this->mobile, 3)) {
            $this->addError('mobile', 'چند دقیقه دیگر دوباره تلاش کنید.');

            return;
        }
        RateLimiter::hit('reset:'.$this->mobile, 300);

        // برای اینکه معلوم نشود چه شماره‌ای عضو است، در هر حال به مرحله بعد می‌رویم
        if (User::where('mobile', $this->mobile)->exists()) {
            $code = (string) random_int(10000, 99999);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['mobile' => $this->mobile],
                ['token' => Hash::make($code), 'created_at' => now()],
            );

            // خطای ارسال فقط لاگ می‌شود تا از پیام خطا معلوم نشود این شماره عضو است
            app(Sms::class)->sendPattern($this->mobile, config('services.melipayamak.otp_body_id'), [$code]);

            if (app()->isLocal()) {
                $this->dispatch('toast', message: 'کد آزمایشی (فقط در محیط توسعه): '.$code, type: 'info');
            }
        }

        $this->step = 2;
    }

    public function resetPassword()
    {
        $this->code = en_digits($this->code);
        $this->validate([
            'code' => ['required', 'digits:5'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $row = DB::table('password_reset_tokens')->where('mobile', $this->mobile)->first();
        $valid = $row && now()->subMinutes(10)->lt($row->created_at) && Hash::check($this->code, $row->token);

        if (! $valid) {
            $this->addError('code', 'کد اشتباه است یا منقضی شده.');

            return;
        }

        $user = User::where('mobile', $this->mobile)->firstOrFail();
        $user->update(['password' => $this->password]);
        DB::table('password_reset_tokens')->where('mobile', $this->mobile)->delete();

        Auth::login($user);
        session()->regenerate();
        session()->flash('toast', ['message' => 'رمز عبور تغییر کرد.']);

        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
