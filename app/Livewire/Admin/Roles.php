<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

/**
 * تعریف نقش‌ها با انتخاب دسترسی‌ها از config/permissions.php (ذخیره با spatie/laravel-permission).
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
        $role = Role::with('permissions')->findOrFail($id);
        abort_if($role->isSuperAdmin(), 403);

        $this->resetErrorBag();
        $this->editingId = $role->id;
        $this->fill([
            'key' => $role->name,
            'label' => $role->label,
            'description' => $role->description ?? '',
            'permissions' => $role->permissionNames(),
        ]);
        $this->showForm = true;
    }

    public function save(): void
    {
        $role = Role::with('permissions')->findOrNew($this->editingId);
        abort_if($role->isSuperAdmin(), 403);

        $catalog = collect(config('permissions'))->collapse()->keys()->all();

        $this->validate([
            'label' => ['required', 'string', 'max:40'],
            'key' => ['required', 'alpha_dash', 'max:30', Rule::unique('roles', 'name')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:200'],
            // فقط کلیدهای فهرست؛ 'roles.manage' از این راه داده نمی‌شود
            'permissions.*' => [Rule::in($catalog)],
        ]);

        $old = $role->exists ? $role->permissionNames() : [];
        $new = array_values(array_unique($this->permissions));

        $role->fill([
            'label' => $this->label,
            'name' => $role->is_system ? $role->name : $this->key,
            'description' => $this->description ?: null,
        ])->save();

        // کلیدی که تازه به config اضافه شده ولی هنوز ردیفی در جدول permissions ندارد، ساخته می‌شود
        $role->syncPermissions(array_map(fn (string $name) => Permission::findOrCreate($name), $new));

        if ($role->wasRecentlyCreated ? $new !== [] : (array_diff($old, $new) || array_diff($new, $old))) {
            $role->logActivity('updated', ['permissions' => ['old' => $old, 'new' => $new]]);
        }

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
            'roles' => Role::with('permissions')->withCount('users')->orderBy('id')->get(),
        ]);
    }
}
