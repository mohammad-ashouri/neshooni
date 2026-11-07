<?php

namespace App\Livewire\Admin;

use App\Models\Platform;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * مدیریت پلتفرم‌ها (اینستاگرام، تلگرام و...).
 * پلتفرم غیرفعال در فرم ثبت پیج و فیلترها نمایش داده نمی‌شود، ولی پیج‌های قبلی آن می‌مانند.
 */
#[Layout('layouts::admin'), Title('پلتفرم‌ها')]
class Platforms extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $key = '';

    public string $label = '';

    public string $color = '#7c3aed';

    /** مثل instagram.com/ — آیدی پیج به انتهای آن اضافه می‌شود */
    public string $base_url = '';

    public string $handle_type = 'username';

    public string $letter = '';

    public $logo;

    public bool $is_active = true;

    public int $sort = 0;

    public function create(): void
    {
        $this->reset('editingId', 'key', 'label', 'color', 'base_url', 'handle_type', 'letter', 'logo', 'is_active');
        $this->sort = (int) Platform::max('sort') + 1;
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $platform = Platform::findOrFail($id);
        $this->resetErrorBag();
        $this->logo = null;
        $this->editingId = $platform->id;
        $this->fill($platform->only(['key', 'label', 'color', 'base_url', 'handle_type', 'letter', 'is_active', 'sort']));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->base_url = preg_replace('#^https?://#', '', trim($this->base_url));

        $data = $this->validate([
            'key' => ['required', 'alpha_dash', 'max:30', Rule::unique('platforms')->ignore($this->editingId)],
            'label' => ['required', 'string', 'max:40'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // سایت فروشگاهی آدرس پایه ندارد؛ آیدی خودش آدرس کامل است
            'base_url' => [Rule::requiredIf($this->handle_type !== 'url'), 'nullable', 'string', 'max:100'],
            'handle_type' => ['required', 'in:username,phone,url'],
            'letter' => ['required', 'string', 'max:3'],
            'logo' => ['nullable', 'image', 'max:512'],
            'is_active' => ['boolean'],
            'sort' => ['integer'],
        ]);

        $platform = Platform::findOrNew($this->editingId);
        unset($data['logo']);
        $data['base_url'] = $data['handle_type'] === 'url' ? '' : $data['base_url'];

        if ($this->logo) {
            if ($platform->logo) {
                Storage::disk('public')->delete($platform->logo);
            }
            $data['logo'] = $this->logo->store('platforms', 'public');
        }

        $platform->fill($data)->save();

        $this->showForm = false;
        $this->dispatch('toast', message: 'پلتفرم ذخیره شد.');
    }

    public function removeLogo(): void
    {
        $platform = Platform::findOrFail($this->editingId);
        Storage::disk('public')->delete($platform->logo);
        $platform->update(['logo' => null]);
    }

    public function toggle(int $id): void
    {
        $platform = Platform::findOrFail($id);
        $platform->update(['is_active' => ! $platform->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.platforms', [
            'platforms' => Platform::withCount('listings')->orderBy('sort')->get(),
            'editing' => $this->editingId ? Platform::find($this->editingId) : null,
        ]);
    }
}
