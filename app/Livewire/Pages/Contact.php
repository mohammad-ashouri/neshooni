<?php

namespace App\Livewire\Pages;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('تماس با ما')]
class Contact extends Component
{
    public string $name = '';

    /** موبایل یا ایمیل برای پاسخ */
    public string $contact = '';

    public string $subject = 'پشتیبانی';

    public string $body = '';

    public bool $sent = false;

    public function mount(): void
    {
        if ($user = Auth::user()) {
            $this->name = $user->name;
            $this->contact = $user->mobile;
        }
    }

    public function send(): void
    {
        $this->contact = en_digits(trim($this->contact));

        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'contact' => ['required', 'regex:/^(09\d{9}|[^@\s]+@[^@\s]+\.[^@\s]+)$/'],
            'subject' => ['required', Rule::in(ContactMessage::SUBJECTS)],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ], ['contact.regex' => 'یک شماره موبایل یا ایمیل معتبر وارد کنید.'], ['body' => 'متن پیام', 'name' => 'نام']);

        // جلوگیری از اسپم: حداکثر ۳ پیام در ده دقیقه از هر IP
        if (RateLimiter::tooManyAttempts('contact:'.request()->ip(), 3)) {
            $this->addError('body', 'پیام‌های زیادی فرستادید. کمی بعد دوباره تلاش کنید.');

            return;
        }
        RateLimiter::hit('contact:'.request()->ip(), 600);

        ContactMessage::create($data + ['user_id' => Auth::id()]);

        $this->reset('body');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.pages.contact');
    }
}
