<div>
    <h1 class="mb-5 text-2xl font-extrabold">پیام‌های تماس با ما</h1>

    @if ($messages->isEmpty())
        <x-empty icon="✉️" title="پیامی نرسیده" />
    @else
        <div class="card divide-y divide-slate-100">
            @foreach ($messages as $message)
                <div wire:key="message-{{ $message->id }}">
                    <button wire:click="open({{ $message->id }})" class="flex w-full items-center gap-3 px-5 py-3.5 text-right hover:bg-slate-50">
                        <span @class(['size-2 shrink-0 rounded-full', 'bg-violet-500' => ! $message->read_at, 'bg-transparent' => $message->read_at])></span>
                        <span class="min-w-0 flex-1">
                            <span @class(['block truncate text-sm', 'font-bold' => ! $message->read_at])>{{ $message->name }} — {{ $message->subject }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ $message->body }}</span>
                        </span>
                        <span class="shrink-0 text-xs text-slate-400">{{ fa_date($message->created_at, 'd MMMM') }}</span>
                    </button>

                    @if ($openId === $message->id)
                        <div class="space-y-3 bg-slate-50 px-5 py-4 text-sm">
                            <p class="leading-8 whitespace-pre-line">{{ $message->body }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ $message->isEmail() ? 'mailto:'.$message->contact : 'tel:'.$message->contact }}" class="btn-light py-1.5 text-xs" dir="ltr">{{ $message->isEmail() ? '✉️' : '📞' }} {{ $message->contact }}</a>
                                <span class="text-xs text-slate-400">{{ fa_datetime($message->created_at) }}</span>
                                @can('messages.delete')
                                    <button wire:click="delete({{ $message->id }})" wire:confirm="پیام حذف شود؟" class="btn-danger ms-auto py-1.5 text-xs">حذف</button>
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $messages->links() }}</div>
    @endif
</div>
