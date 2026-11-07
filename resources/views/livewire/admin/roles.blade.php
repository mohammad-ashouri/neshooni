<div>
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">نقش‌ها و دسترسی‌ها</h1>
        <button wire:click="create" class="cursor-pointer btn-primary">+ نقش جدید</button>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($roles as $role)
            <article class="card p-5" wire:key="role-{{ $role->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-bold">{{ $role->label }} <span class="font-mono text-xs font-normal text-slate-400" dir="ltr">{{ $role->name }}</span></h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $role->description }}</p>
                    </div>
                    <span class="chip bg-slate-100 text-slate-600">{{ fa_number($role->users_count) }} کاربر</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @forelse ($role->permissionLabels() as $label)
                        <span class="chip bg-violet-50 text-violet-700">{{ $label }}</span>
                    @empty
                        <span class="text-xs text-slate-400">بدون دسترسی به پنل</span>
                    @endforelse
                </div>
                @unless ($role->isSuperAdmin())
                    <div class="mt-4 flex gap-2">
                        <button wire:click="edit({{ $role->id }})" class="cursor-pointer btn-light py-1.5 text-xs">ویرایش</button>
                        @unless ($role->is_system)
                            <button wire:click="delete({{ $role->id }})" wire:confirm="نقش «{{ $role->label }}» حذف شود؟" class="cursor-pointer btn-danger py-1.5 text-xs">حذف</button>
                        @endunless
                    </div>
                @endunless
            </article>
        @endforeach
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'ویرایش نقش' : 'نقش جدید'" close="$set('showForm', false)" width="max-w-2xl">
            <form wire:submit="save" class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="label">نام نقش</label>
                        <input wire:model="label" class="input" placeholder="مثلاً پشتیبان">
                        @error('label') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">کلید انگلیسی</label>
                        <input wire:model="key" class="input text-left" dir="ltr" placeholder="support">
                        @error('key') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="label">توضیح</label>
                    <input wire:model="description" class="input">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (config('permissions') as $group => $items)
                        <fieldset class="rounded-xl border border-slate-200 p-3">
                            <legend class="px-1 text-sm font-bold">{{ $group }}</legend>
                            @foreach ($items as $permission => $label)
                                <label class="flex items-center gap-2 py-1 text-sm">
                                    <input type="checkbox" wire:model="permissions" value="{{ $permission }}" class="accent-violet-600"> {{ $label }}
                                </label>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
                @error('permissions.*') <p class="error">دسترسی نامعتبر است.</p> @enderror
                <p class="text-xs text-slate-400">نقشی که هیچ دسترسی نداشته باشد، به پنل مدیریت راه ندارد.</p>
                <button class="cursor-pointer btn-primary w-full">ذخیره نقش</button>
            </form>
        </x-modal>
    @endif
</div>
