<?php

namespace App\Livewire\Pages;

use App\Models\ContactMessage;
use App\Support\Seo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use RalphJSmit\Laravel\SEO\Support\SEOData;

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
        Seo::set(new SEOData(
            title: 'تماس با ما',
            description: 'راه‌های ارتباط با پشتیبانی نشونی: تلفن '.config('site.contact.phone').'، ایمیل '.config('site.contact.email').' و فرم ارسال پیام برای پشتیبانی، همکاری و گزارش تخلف.',
            schema: Seo::schemas([
                '@context' => 'https://schema.org',
                '@type' => 'ContactPage',
                'url' => route('contact'),
                'mainEntity' => [
                    '@type' => 'Organization',
                    'name' => config('site.name'),
                    'email' => config('site.contact.email'),
                    'telephone' => config('site.contact.phone'),
                    'address' => config('site.contact.address'),
                ],
            ]),
        ));

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
