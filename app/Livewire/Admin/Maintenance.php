<?php

namespace App\Livewire\Admin;

use App\Support\Maintenance as SiteMaintenance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * روشن و خاموش کردن حالت تعمیر سایت.
 * مدیر در حالت تعمیر هم سایت را عادی می‌بیند تا بتواند تغییرات را بررسی کند.
 */
#[Layout('layouts::admin'), Title('حالت تعمیر')]
class Maintenance extends Component
{
    /** پیامی که به بازدیدکننده‌ها نشان داده می‌شود؛ خالی یعنی متن پیش‌فرض */
    public string $message = '';

    /** مدت تقریبی تعمیر به دقیقه؛ خالی یعنی نامشخص */
    public string $duration = '';

    public function mount(): void
    {
        $this->message = SiteMaintenance::message() ?? '';
    }

    public function enable(): void
    {
        $this->validate([
            'message' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'in:'.implode(',', array_keys($this->durations()))],
        ]);

        SiteMaintenance::enable(
            trim($this->message),
            $this->duration ? now()->addMinutes((int) $this->duration) : null,
            auth()->user(),
        );

        $this->dispatch('toast', message: 'حالت تعمیر روشن شد. بازدیدکننده‌ها صفحه تعمیر را می‌بینند.');
    }

    public function disable(): void
    {
        SiteMaintenance::disable();
        $this->reset('duration');

        $this->dispatch('toast', message: 'حالت تعمیر خاموش شد و سایت برای همه باز است.');
    }

    /** @return array<int, string> */
    public function durations(): array
    {
        return [15 => '۱۵ دقیقه', 30 => 'نیم ساعت', 60 => '۱ ساعت', 120 => '۲ ساعت', 240 => '۴ ساعت', 1440 => '۱ روز'];
    }

    public function render()
    {
        return view('livewire.admin.maintenance', [
            'active' => SiteMaintenance::isActive(),
            'startedAt' => SiteMaintenance::startedAt(),
            'endsAt' => SiteMaintenance::endsAt(),
        ]);
    }
}
