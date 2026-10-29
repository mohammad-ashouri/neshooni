<?php

namespace App\Livewire\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('ثبت‌نام')]
class Register extends Component
{
    public string $name = '';

    public string $mobile = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register()
    {
        $this->mobile = en_digits($this->mobile);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'mobile.regex' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.',
            'mobile.unique' => 'با این شماره قبلاً ثبت‌نام شده است. وارد شوید.',
        ]);

        // TODO: در نسخه نهایی قبل از ساخت حساب، کد تأیید پیامکی به موبایل ارسال شود
        $user = User::create($data + ['role_id' => Role::where('key', 'user')->value('id')]);

        Auth::login($user, true);
        session()->regenerate();
        session()->flash('toast', ['message' => 'حساب شما ساخته شد. حالا اولین پیج خود را ثبت کنید.']);

        return $this->redirectRoute('dashboard.listings.create', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
