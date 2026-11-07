<div>
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">پلتفرم‌ها</h1>
        <button wire:click="create" class="cursor-pointer btn-primary">+ پلتفرم جدید</button>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100">
                <tr><th class="th">پلتفرم</th><th class="th">آدرس پایه</th><th class="th">نوع شناسه</th><th class="th">پیج</th><th class="th">وضعیت</th><th class="th"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($platforms as $platform)
                    <tr wire:key="platform-{{ $platform->id }}" @class(['opacity-50' => ! $platform->is_active])>
                        <td class="td"><span class="flex items-center gap-2"><x-platform-icon :platform="$platform" class="size-8 text-lg" /> <b>{{ $platform->label }}</b></span></td>
                        <td class="td font-mono text-xs" dir="ltr">{{ $platform->base_url }}</td>
                        <td class="td">{{ $platform->usesUrl() ? 'آدرس سایت' : ($platform->usesPhone() ? 'شماره تلفن' : 'نام کاربری') }}</td>
                        <td class="td">{{ fa_number($platform->listings_count) }}</td>
                        <td class="td">
                            <button wire:click="toggle({{ $platform->id }})" @class(['cursor-pointer chip', 'bg-emerald-50 text-emerald-700' => $platform->is_active, 'bg-slate-100 text-slate-500' => ! $platform->is_active])>{{ $platform->is_active ? 'فعال' : 'غیرفعال' }}</button>
                        </td>
                        <td class="td text-left"><button wire:click="edit({{ $platform->id }})" class="cursor-pointer rounded-lg px-2 py-1 text-xs text-violet-700 hover:bg-violet-50">ویرایش</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'ویرایش پلتفرم' : 'پلتفرم جدید'" close="$set('showForm', false)">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">نام فارسی</label>
                        <input wire:model="label" class="input" placeholder="اینستاگرام">
                        @error('label') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">کلید انگلیسی</label>
                        <input wire:model="key" class="input text-left" dir="ltr" placeholder="instagram">
                        @error('key') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="label">آدرس پایه پروفایل</label>
                    <input wire:model="base_url" class="input text-left" dir="ltr" placeholder="instagram.com/">
                    <p class="mt-1 text-xs text-slate-400">آیدی پیج به انتهای این آدرس اضافه می‌شود. برای نوع «آدرس سایت» خالی بگذارید.</p>
                    @error('base_url') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="label">نوع شناسه</label>
                        <select wire:model="handle_type" class="input">
                            <option value="username">نام کاربری</option>
                            <option value="phone">شماره تلفن</option>
                            <option value="url">آدرس سایت</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">رنگ</label>
                        <input wire:model.live="color" type="color" class="input h-[46px] p-1">
                        @error('color') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">حرف نشان</label>
                        <input wire:model.live="letter" class="input text-center" maxlength="3">
                        @error('letter') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="size-12 rounded-lg object-cover">
                    @elseif ($editing?->logoUrl())
                        <img src="{{ $editing->logoUrl() }}" class="size-12 rounded-lg object-cover">
                        <button type="button" wire:click="removeLogo" class="cursor-pointer text-xs text-rose-600 hover:underline">حذف لوگو</button>
                    @else
                        <span class="grid size-12 place-items-center rounded-lg text-lg font-black text-white" style="background: {{ $color }}">{{ $letter }}</span>
                    @endif
                    <label class="btn-light cursor-pointer py-2 text-xs">
                        بارگذاری لوگو
                        <input type="file" wire:model="logo" accept="image/*" class="sr-only">
                    </label>
                    @error('logo') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active" class="accent-violet-600"> فعال</label>
                    <label class="flex items-center gap-2 text-sm">ترتیب <input wire:model="sort" type="number" class="input w-20 py-1.5"></label>
                </div>
                <button class="cursor-pointer btn-primary w-full">ذخیره</button>
            </form>
        </x-modal>
    @endif
</div>
