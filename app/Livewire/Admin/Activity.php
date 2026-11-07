<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * تاریخچه اقدامات: چه کسی، کی، چه چیزی را ایجاد، ویرایش یا حذف کرد.
 * از صفحه پیج‌ها با ?subject=listing&id=12 تاریخچه یک پیج باز می‌شود.
 */
#[Layout('layouts::admin'), Title('تاریخچه اقدامات')]
class Activity extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $subject = '';

    /** شناسه مورد (نام id در Livewire رزرو است) */
    #[Url(as: 'id', except: '')]
    public string $subjectId = '';

    /** نام یا موبایل انجام‌دهنده */
    #[Url(except: '')]
    public string $q = '';

    /** فقط اقدامات اعضای پنل (مدیر و ناظر)، نه کاربران عادی */
    #[Url(except: false)]
    public bool $staff = false;

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $class = ActivityLog::SUBJECTS[$this->subject][0] ?? null;

        $logs = ActivityLog::with('user.role')
            ->when($class, fn (Builder $q) => $q->where('subject_type', $class))
            ->when($class && $this->subjectId !== '', fn (Builder $q) => $q->where('subject_id', en_digits(trim($this->subjectId))))
            ->when($this->q !== '', function (Builder $q) {
                $term = '%'.en_digits(trim($this->q)).'%';
                $q->whereHas('user', fn (Builder $u) => $u->where('name', 'like', $term)->orWhere('mobile', 'like', $term));
            })
            ->when($this->staff, fn (Builder $q) => $q->whereHas('user', fn (Builder $u) => $u->whereIn('role_id', Role::all()->filter->hasPanelAccess()->modelKeys())))
            ->latest('id')
            ->paginate(30);

        return view('livewire.admin.activity', ['logs' => $logs]);
    }
}
