<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * کاربران و تعیین نقش.
 *
 * جلوگیری از افزایش دسترسی:
 * - کسی نقش خودش را تغییر نمی‌دهد.
 * - غیر مدیر کل نمی‌تواند نقش مدیر کل را تغییر دهد یا نقشی بدهد که دسترسی بیشتری از خودش دارد.
 */
#[Layout('layouts::admin'), Title('کاربران')]
class Users extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $role = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function changeRole(int $userId, int $roleId): void
    {
        $user = User::with('roles')->findOrFail($userId);
        $role = Role::with('permissions')->findOrFail($roleId);

        if (! $this->canManage($user) || ! $this->canAssign($role)) {
            $this->dispatch('toast', message: 'اجازه این تغییر را ندارید.', type: 'error');

            return;
        }

        $user->setRole($role);
        $this->dispatch('toast', message: "نقش {$user->name} به «{$role->label}» تغییر کرد.");
    }

    public function toggleVerified(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['verified_at' => $user->isVerified() ? null : now()]);
    }

    public function canManage(User $user): bool
    {
        $me = Auth::user();

        return $user->id !== $me->id && ($me->isAdmin() || ! $user->isAdmin());
    }

    public function canAssign(Role $role): bool
    {
        $me = Auth::user();

        return $me->isAdmin() || (! $role->isSuperAdmin() && collect($role->permissionNames())->every(fn (string $permission) => $me->hasPermission($permission)));
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'users' => User::with('roles')->withCount('listings')
                ->when($this->q !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->q}%")->orWhere('mobile', 'like', '%'.en_digits($this->q).'%')))
                ->when($this->role, fn ($q) => $q->whereRelation('roles', 'roles.id', $this->role))
                ->latest()
                ->paginate(20),
            'roles' => Role::with('permissions')->orderBy('id')->get(),
        ]);
    }
}
