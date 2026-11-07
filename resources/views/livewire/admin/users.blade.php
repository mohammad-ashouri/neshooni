<div>
    <h1 class="mb-5 text-2xl font-extrabold">کاربران</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        <input wire:model.live.debounce.400ms="q" class="input w-full py-2 sm:w-64" placeholder="🔍 نام یا موبایل...">
        <select wire:model.live="role" class="input w-auto py-2">
            <option value="">همه نقش‌ها</option>
            @foreach ($roles as $item)
                <option value="{{ $item->id }}">{{ $item->label }}</option>
            @endforeach
        </select>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100">
                <tr><th class="th">کاربر</th><th class="th">موبایل</th><th class="th">پیج</th><th class="th">عضویت</th><th class="th">نشان</th><th class="th">نقش</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="td">
                            <a href="{{ route('business.show', $user) }}" wire:navigate class="cursor-pointer flex items-center gap-2 hover:text-violet-700">
                                <x-avatar :src="$user->avatarUrl()" :name="$user->name" :seed="$user->id" class="size-8 text-sm" rounded="rounded-full" />
                                <span class="font-semibold">{{ $user->name }}</span>
                            </a>
                        </td>
                        <td class="td font-mono text-xs" dir="ltr">{{ $user->mobile }}</td>
                        <td class="td">{{ fa_number($user->listings_count) }}</td>
                        <td class="td text-slate-500">{{ fa_date($user->created_at) }}</td>
                        <td class="td">
                            <button wire:click="toggleVerified({{ $user->id }})" title="{{ $user->isVerified() ? 'برداشتن نشان' : 'دادن نشان' }}" @class(['cursor-pointer rounded-lg px-2 py-1', 'text-sky-500' => $user->isVerified(), 'text-slate-300 hover:text-sky-400' => ! $user->isVerified()])>
                                <x-verified-badge class="size-5 text-current" />
                            </button>
                        </td>
                        <td class="td">
                            @if ($this->canManage($user))
                                <select wire:change="changeRole({{ $user->id }}, $event.target.value)" class="input w-36 py-1.5 text-xs">
                                    @foreach ($roles as $item)
                                        @if ($this->canAssign($item) || $item->id === $user->role?->id)
                                            <option value="{{ $item->id }}" @selected($item->id === $user->role?->id)>{{ $item->label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @else
                                <span class="chip bg-slate-100 text-slate-600">{{ $user->roleLabel() }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
