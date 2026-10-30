<div>
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">دسته‌بندی‌ها</h1>
        <button wire:click="create" class="btn-primary">+ دسته اصلی</button>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100">
                <tr><th class="th">نام</th><th class="th">نامک</th><th class="th">پیج</th><th class="th">ترتیب</th><th class="th"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($tree as ['category' => $category, 'depth' => $depth])
                    <tr wire:key="cat-{{ $category->id }}" @class(['bg-slate-50/60' => $depth === 1])>
                        <td class="td" style="padding-inline-start: {{ ($depth - 1) * 1.75 + 1 }}rem">
                            <span @class(['font-bold' => $depth === 1, 'text-slate-600' => $depth === 3])>
                                {{ $depth > 1 ? '└ ' : '' }}{{ $category->icon }} {{ $category->name }}
                            </span>
                        </td>
                        <td class="td font-mono text-xs text-slate-500" dir="ltr">{{ $category->slug }}</td>
                        <td class="td">{{ fa_number(collect($category->selfAndDescendantIds())->sum(fn ($id) => $counts[$id] ?? 0)) }}</td>
                        <td class="td">{{ fa_number($category->sort) }}</td>
                        <td class="td text-left">
                            @if ($depth < App\Models\Category::MAX_DEPTH)
                                <button wire:click="create({{ $category->id }})" class="rounded-lg px-2 py-1 text-xs text-violet-700 hover:bg-violet-50">+ زیردسته</button>
                            @endif
                            <button wire:click="edit({{ $category->id }})" class="rounded-lg px-2 py-1 text-xs text-slate-600 hover:bg-slate-100">ویرایش</button>
                            <button wire:click="delete({{ $category->id }})" wire:confirm="«{{ $category->name }}» حذف شود؟" class="rounded-lg px-2 py-1 text-xs text-rose-600 hover:bg-rose-50">حذف</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'ویرایش دسته' : 'دسته جدید'" close="$set('showForm', false)">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-[1fr_6rem] gap-3">
                    <div>
                        <label class="label">نام</label>
                        <input wire:model="name" class="input" autofocus>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">آیکون</label>
                        <input wire:model="icon" class="input text-center" placeholder="👗">
                    </div>
                </div>
                <div>
                    <label class="label">دسته والد</label>
                    <select wire:model="parent_id" class="input">
                        <option value="">— دسته اصلی —</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->pathLabel() }}</option>
                        @endforeach
                    </select>
                    @error('parent_id') <p class="error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">فقط دسته‌هایی نمایش داده می‌شوند که با انتخابشان از سه سطح بیشتر نشود.</p>
                </div>
                <div class="grid grid-cols-[1fr_6rem] gap-3">
                    <div>
                        <label class="label">نامک انگلیسی (در آدرس)</label>
                        <input wire:model="slug" class="input text-left" dir="ltr" placeholder="women-clothing">
                        @error('slug') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">ترتیب</label>
                        <input wire:model="sort" type="number" class="input">
                    </div>
                </div>
                <button class="btn-primary w-full">ذخیره</button>
            </form>
        </x-modal>
    @endif
</div>
