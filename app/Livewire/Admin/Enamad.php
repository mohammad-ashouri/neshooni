<?php

namespace App\Livewire\Admin;

use App\Support\Enamad as SiteEnamad;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * ویرایش کد اینماد فوتر.
 * کد بدون فیلتر در همه صفحه‌ها چاپ می‌شود، پس دسترسی «site.enamad» عمداً در config/permissions.php نیست
 * و فقط مدیر کل ('*') این بخش را دارد.
 */
#[Layout('layouts::admin'), Title('نماد اینماد')]
class Enamad extends Component
{
    public string $code = '';

    public function mount(): void
    {
        $this->code = SiteEnamad::code() ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'code' => ['nullable', 'string', 'max:3000', function ($attribute, $value, $fail) {
                if (filled($value) && ! str_contains($value, 'enamad.ir')) {
                    $fail('این کد اینماد نیست؛ کد را از پنل enamad.ir کپی کنید.');
                }
            }],
        ]);

        SiteEnamad::save($this->code);

        $this->dispatch('toast', message: filled(trim($this->code)) ? 'کد اینماد ذخیره شد و در فوتر نمایش داده می‌شود.' : 'اینماد از فوتر برداشته شد.');
    }

    public function render()
    {
        return view('livewire.admin.enamad', ['saved' => SiteEnamad::code()]);
    }
}
