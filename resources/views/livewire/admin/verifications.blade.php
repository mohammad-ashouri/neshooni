<div>
    <h1 class="mb-5 text-2xl font-extrabold">درخواست‌های نشان تأیید</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (App\Enums\ModerationStatus::cases() as $case)
            <button wire:click="$set('status', '{{ $case->value }}')" @class(['filter-chip', 'filter-chip-active' => $status === $case->value])>{{ $case->label() }}</button>
        @endforeach
    </div>

    @if ($requests->isEmpty())
        <x-empty icon="✅" title="درخواستی نیست" />
    @else
        <div class="space-y-3">
            @foreach ($requests as $request)
                @php($paid = $request->user->payments->where('verification_request_id', $request->id)->where('status', 'paid')->isNotEmpty())
                <article class="card p-5" wire:key="verify-{{ $request->id }}">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-avatar :src="$request->user->avatarUrl()" :name="$request->user->name" :seed="$request->user_id" class="size-11 text-lg" rounded="rounded-full" />
                        <div class="flex-1">
                            <a href="{{ route('business.show', $request->user) }}" wire:navigate class="font-bold hover:text-violet-700">{{ $request->user->name }}</a>
                            <div class="text-xs text-slate-500">{{ $request->businessTypeLabel() }} · {{ fa_digits($request->user->mobile) }} · {{ fa_date($request->created_at) }}</div>
                        </div>
                        <span @class(['chip', 'bg-emerald-50 text-emerald-700' => $paid, 'bg-amber-50 text-amber-700' => ! $paid])>{{ $paid ? 'پرداخت شده' : 'پرداخت نشده' }}</span>
                    </div>
                    @if ($request->note)
                        <p class="mt-3 text-sm text-slate-600">{{ $request->note }}</p>
                    @endif
                    @if ($request->reason)
                        <p class="mt-2 text-xs text-rose-600">دلیل رد: {{ $request->reason }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button wire:click="download({{ $request->id }})" class="btn-light py-2">📄 دانلود مدرک</button>
                        @if ($request->status === App\Enums\ModerationStatus::Pending)
                            <button wire:click="approve({{ $request->id }})" @disabled(! $paid) class="btn-success py-2" title="{{ $paid ? '' : 'هزینه بررسی پرداخت نشده' }}">تأیید و اعطای نشان</button>
                            <button wire:click="$set('rejecting', {{ $request->id }})" class="btn-danger py-2">رد</button>
                        @endif
                    </div>

                    @if ($rejecting === $request->id)
                        <form wire:submit="reject" class="mt-3 flex flex-col gap-2 sm:flex-row">
                            <input wire:model="reason" class="input flex-1" placeholder="دلیل رد (برای کاربر ارسال می‌شود)">
                            <button class="btn-danger">ثبت رد</button>
                            <button type="button" wire:click="$set('rejecting', null)" class="btn-light">انصراف</button>
                        </form>
                        @error('reason') <p class="error">{{ $message }}</p> @enderror
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</div>
