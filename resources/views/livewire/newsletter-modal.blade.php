<div>
    @if ($visible)
        {{-- کمی بعد از باز شدن صفحه نمایش داده می‌شود تا مزاحم نباشد --}}
        <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 1500)" x-show="show" x-transition.opacity x-cloak
             class="fixed inset-0 z-[80] grid place-items-center bg-slate-950/50 p-4 backdrop-blur-sm" wire:click.self="dismiss">
            <div class="card w-full max-w-md overflow-hidden shadow-2xl">
                <div class="bg-linear-to-l from-violet-600 to-fuchsia-500 px-6 py-6 text-center text-white">
                    <div class="text-4xl">{{ $category?->root()->icon ?? '📬' }}</div>
                    <h2 class="mt-2 text-lg font-extrabold">
                        {{ $category ? "به «{$category->name}» علاقه دارید؟" : 'از پیج‌های تازه باخبر شوید' }}
                    </h2>
                </div>

                <div class="p-6">
                    @if ($done)
                        <p class="text-center leading-7 text-slate-600">✅ عضو خبرنامه شدید. پیج‌های جدید را به شما خبر می‌دهیم.</p>
                        <button wire:click="dismiss" class="cursor-pointer btn-primary mt-5 w-full">باشه</button>
                    @else
                        <p class="text-center text-sm leading-7 text-slate-600">
                            @if ($category)
                                هر وقت پیج تازه‌ای در «{{ $category->pathLabel() }}» ثبت شد، باخبرتان می‌کنیم.
                            @else
                                عضو خبرنامه نشونی شوید تا تازه‌ترین پیج‌ها و کانال‌ها را از دست ندهید.
                            @endif
                        </p>

                        <form wire:submit="subscribe" class="mt-5 space-y-3">
                            @guest
                                <div>
                                    <input wire:model="contact" class="input text-center" dir="ltr" placeholder="09121234567 یا email@example.com">
                                    @error('contact') <p class="error text-center">{{ $message }}</p> @enderror
                                </div>
                            @endguest
                            <button class="cursor-pointer btn-primary w-full">عضویت در خبرنامه</button>
                            <button type="button" wire:click="dismiss" class="cursor-pointer w-full py-1 text-sm text-slate-500 hover:text-slate-700">نه، ممنون</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
