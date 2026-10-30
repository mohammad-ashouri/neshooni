<?php

/*
|--------------------------------------------------------------------------
| پیام‌های فارسی اعتبارسنجی
|--------------------------------------------------------------------------
| فقط قانون‌هایی که در پروژه استفاده می‌شوند. نام فیلدها در بخش attributes
| یا در خود کامپوننت (پارامتر سوم validate) تعریف می‌شود.
*/

return [
    'required' => ':attribute الزامی است.',
    'string' => ':attribute باید متن باشد.',
    'integer' => ':attribute باید عدد صحیح باشد.',
    'numeric' => ':attribute باید عدد باشد.',
    'boolean' => ':attribute نامعتبر است.',
    'array' => ':attribute نامعتبر است.',
    'in' => ':attribute انتخاب‌شده معتبر نیست.',
    'exists' => ':attribute انتخاب‌شده معتبر نیست.',
    'unique' => 'این :attribute قبلاً ثبت شده است.',
    'confirmed' => 'تکرار :attribute با آن یکسان نیست.',
    'regex' => 'قالب :attribute معتبر نیست.',
    'digits' => ':attribute باید :digits رقم باشد.',
    'size' => [
        'string' => ':attribute باید :size کاراکتر باشد.',
    ],
    'date_format' => ':attribute باید به شکل :format باشد.',
    'between' => [
        'numeric' => ':attribute باید بین :min و :max باشد.',
    ],
    'min' => [
        'numeric' => ':attribute نباید کمتر از :min باشد.',
        'string' => ':attribute باید حداقل :min کاراکتر باشد.',
        'array' => 'حداقل :min مورد انتخاب کنید.',
    ],
    'max' => [
        'numeric' => ':attribute نباید بیشتر از :max باشد.',
        'string' => ':attribute نباید بیشتر از :max کاراکتر باشد.',
        'file' => 'حجم :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'array' => 'حداکثر :max مورد می‌توانید انتخاب کنید.',
    ],
    'file' => ':attribute باید فایل باشد.',
    'image' => ':attribute باید تصویر باشد.',
    'mimes' => ':attribute باید از نوع :values باشد.',
    'current_password' => 'رمز عبور اشتباه است.',
    'captcha' => 'کد امنیتی اشتباه است.',
    'password' => [
        'letters' => ':attribute باید حداقل یک حرف داشته باشد.',
        'mixed' => ':attribute باید حروف بزرگ و کوچک داشته باشد.',
        'numbers' => ':attribute باید حداقل یک عدد داشته باشد.',
        'symbols' => ':attribute باید حداقل یک نماد داشته باشد.',
        'uncompromised' => 'این :attribute در نشت‌های اطلاعاتی دیده شده؛ رمز دیگری انتخاب کنید.',
    ],

    'attributes' => [
        'name' => 'نام',
        'mobile' => 'شماره موبایل',
        'password' => 'رمز عبور',
        'current_password' => 'رمز فعلی',
        'contact' => 'موبایل یا ایمیل',
        'subject' => 'موضوع',
        'body' => 'متن',
        'code' => 'کد',
        'rating' => 'امتیاز',
        'reason' => 'دلیل',
        'label' => 'عنوان',
        'key' => 'کلید',
        'slug' => 'نامک',
        'color' => 'رنگ',
        'base_url' => 'آدرس پایه',
        'logo' => 'لوگو',
        'business_type' => 'نوع کسب‌وکار',
        'captcha' => 'کد امنیتی',
    ],
];
