<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * تعریف نقش‌ها با انتخاب دسترسی‌ها از config/permissions.php.
 * فقط مدیر کل به این صفحه دسترسی دارد. نقش‌های سیستمی (مدیر، کاربر) حذف نمی‌شوند
 * و دسترسی‌های نقش مدیر کل قابل تغییر نیست.
 */
#[Layout('layouts::admin'), Title('نقش‌ها و دسترسی‌ها')]
class Roles extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $key = '';

    public string $label = '';

    public string $description = '';

    public array $permissions = [];

    public function create(): void
    {
        $this->reset('editingId', 'key', 'label', 'description', 'permissions');
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $role = Role::findOrFail($id);
        abort_if($role->key === 'admin', 403);

        $this->resetErrorBag();
        $this->editingId = $role->id;
        $this->fill([
            'key' => $role->key,
            'label' => $role->label,
            'description' => $role->description ?? '',
            'permissions' => $role->permissions ?? [],
        ]);
        $this->showForm = true;
    }

    public function save(): void
    {
        $role = Role::findOrNew($this->editingId);
        abort_if($role->key === 'admin', 403);

        $catalog = collect(config('permissions'))->collapse()->keys()->all();

        $this->validate([
            'label' => ['required', 'string', 'max:40'],
            'key' => ['required', 'alpha_dash', 'max:30', Rule::unique('roles')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:200'],
            // فقط کلیدهای فهرست؛ '*' و 'roles.manage' از این راه داده نمی‌شوند
            'permissions.*' => [Rule::in($catalog)],
        ]);

        $role->fill([
            'label' => $this->label,
            'key' => $role->is_system ? $role->key : $this->key,
            'description' => $this->description ?: null,
            'permissions' => array_values($this->permissions),
        ])->save();

        $this->showForm = false;
        $this->dispatch('toast', message: 'نقش ذخیره شد.');
    }

    public function delete(int $id): void
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->is_system || $role->users_count) {
            $this->dispatch('toast', message: $role->is_system ? 'نقش سیستمی حذف نمی‌شود.' : 'اول کاربران این نقش را به نقش دیگری منتقل کنید.', type: 'error');

            return;
        }

        $role->delete();
        $this->dispatch('toast', message: 'نقش حذف شد.');
    }

    public function render()
    {
        return view('livewire.admin.roles', [
            'roles' => Role::withCount('users')->orderBy('id')->get(),
        ]);
    }
}
