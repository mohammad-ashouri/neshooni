<x-dashboard-shell :title="$listing ? 'ویرایش «'.$listing->title.'»' : 'ثبت پیج جدید'">
    <form wire:submit="save" class="space-y-6">
        @if ($listing?->status === App\Enums\ModerationStatus::Approved)
            <p class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-7 text-sky-700">
                ℹ️ اگر اطلاعات اصلی پیج را تغییر دهید، پیج تا تأیید دوباره مدیر از نمایش خارج می‌شود. افزودن یا ویرایش عکس‌های گالری روی نمایش پیج اثری ندارد.
            </p>
        @endif

        {{-- ===== اطلاعات اصلی ===== --}}
        <section class="card space-y-5 p-6">
            <h2 class="font-bold">اطلاعات پیج</h2>

            <div>
                <label class="label">پلتفرم</label>
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                    @foreach ($platforms as $platform)
                        <label wire:key="platform-{{ $platform->id }}" class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border border-slate-200 p-3 text-xs has-checked:border-violet-500 has-checked:bg-violet-50 has-checked:font-bold">
                            <input type="radio" wire:model.live="platform_id" value="{{ $platform->id }}" class="sr-only">
                            <x-platform-icon :platform="$platform" class="size-8 text-lg" />
                            {{ $platform->label }}
                        </label>
                    @endforeach
                </div>
                @error('platform_id') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label">{{ $selectedPlatform?->usesPhone() ? 'شماره' : 'آیدی یا لینک' }} پیج</label>
                    <div class="flex items-center rounded-xl border border-slate-300 bg-surface focus-within:border-violet-500 focus-within:ring-4 focus-within:ring-violet-100" dir="ltr">
                        <span class="ps-3 text-sm text-slate-400">{{ $selectedPlatform ? $selectedPlatform->base_url : '@' }}</span>
                        <input wire:model="handle" class="min-w-0 flex-1 bg-transparent px-2 py-2.5 outline-none" placeholder="{{ $selectedPlatform?->usesPhone() ? '09121234567' : 'my.shop' }}">
                    </div>
                    <p class="mt-1 text-xs text-slate-400">می‌توانید لینک کامل پیج را هم بچسبانید.</p>
                    @error('handle') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">نام پیج یا کسب‌وکار</label>
                    <input wire:model="title" class="input" placeholder="مثلاً گالری ماهور">
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- دسته‌بندی سه‌سطحی: هر لیست بعد از انتخاب لیست قبلی پر می‌شود --}}
            <div>
                <label class="label">دسته‌بندی</label>
                <div class="grid gap-3 sm:grid-cols-3">
                    <select wire:model.live="cat1" class="input">
                        <option value="">انتخاب دسته...</option>
                        @foreach ($roots as $item)
                            <option value="{{ $item->id }}">{{ $item->icon }} {{ $item->name }}</option>
                        @endforeach
                    </select>
                    @if ($subs1->isNotEmpty())
                        <select wire:model.live="cat2" class="input">
                            <option value="">همه زیردسته‌ها</option>
                            @foreach ($subs1 as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if ($subs2->isNotEmpty())
                        <select wire:model.live="cat3" class="input">
                            <option value="">همه موارد</option>
                            @foreach ($subs2 as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                @error('cat1') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-[1fr_12rem]">
                <div>
                    <label class="label">توضیحات</label>
                    <textarea wire:model="description" rows="4" class="input" placeholder="چه محصول یا خدمتی ارائه می‌دهید؟"></textarea>
                    @error('description') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="label">تعداد دنبال‌کننده</label>
                        <input wire:model="followers" class="input text-left" dir="ltr" inputmode="numeric" placeholder="12500">
                        @error('followers') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">عکس پروفایل پیج</label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-slate-300 p-2 hover:bg-slate-50">
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" class="size-12 rounded-xl object-cover">
                            @else
                                <x-avatar :src="$listing?->imageUrl()" :name="$title ?: '؟'" :seed="$listing?->id ?? 0" class="size-12 text-lg" />
                            @endif
                            <span class="text-xs text-slate-500">انتخاب تصویر</span>
                            <input type="file" wire:model="image" accept="image/*" class="sr-only">
                        </label>
                        <div wire:loading wire:target="image" class="mt-1 text-xs text-violet-600">در حال بارگذاری...</div>
                        @error('image') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== موقعیت و تماس ===== --}}
        <section class="card space-y-5 p-6">
            <h2 class="font-bold">📍 موقعیت و تماس</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label">کشور</label>
                    <select wire:model.live="country" class="input">
                        @foreach (config('locations') as $code => $item)
                            <option value="{{ $code }}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">استان</label>
                    <select wire:model.live="province" class="input">
                        <option value="">انتخاب کنید</option>
                        @foreach ($provinces as $item)
                            <option>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">شهر</label>
                    <select wire:model="city" class="input" @disabled(! $province)>
                        <option value="">انتخاب کنید</option>
                        @foreach ($cities as $item)
                            <option>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model.live="online_only" class="accent-violet-600"> فقط فروش آنلاین دارم (محل مراجعه حضوری ندارم)
            </label>

            <div class="grid gap-4 sm:grid-cols-[1fr_14rem]">
                @unless ($online_only)
                    <div>
                        <label class="label">آدرس</label>
                        <input wire:model="address" class="input" placeholder="خیابان، کوچه، پلاک">
                        @error('address') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endunless
                <div>
                    <label class="label">تلفن تماس (اختیاری)</label>
                    <input wire:model="phone" class="input text-left" dir="ltr" placeholder="02112345678">
                    @error('phone') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ===== ساعات کاری ===== --}}
        <section class="card space-y-4 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold">🕘 ساعات کاری</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="hours.enabled" class="accent-violet-600"> نمایش ساعات کاری
                </label>
            </div>

            @if ($hours['enabled'])
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="preset('workdays')" class="filter-chip">شنبه تا پنجشنبه ۹ تا ۲۱</button>
                    <button type="button" wire:click="preset('everyday')" class="filter-chip">همه روزها ۹ تا ۲۱</button>
                    <button type="button" wire:click="preset('24h')" class="filter-chip">شبانه‌روزی</button>
                    <button type="button" wire:click="copyFirstDay" class="filter-chip">ساعت شنبه برای همه روزها</button>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($hours['days'] as $i => $day)
                        <div class="flex flex-wrap items-center gap-3 py-2" wire:key="day-{{ $i }}">
                            <label class="flex w-28 items-center gap-2 text-sm">
                                <input type="checkbox" wire:model.live="hours.days.{{ $i }}.open" class="accent-violet-600"> {{ App\Models\Listing::WEEK_DAYS[$i] }}
                            </label>
                            @if ($day['open'])
                                <input type="time" wire:model.blur="hours.days.{{ $i }}.from" class="input w-32 py-1.5" dir="ltr">
                                <span class="text-sm text-slate-400">تا</span>
                                <input type="time" wire:model.blur="hours.days.{{ $i }}.to" class="input w-32 py-1.5" dir="ltr">
                                @if ($day['to'] <= $day['from'] && $day['to'] !== '23:59')
                                    <span class="text-xs text-slate-500">(تا بامداد روز بعد)</span>
                                @endif
                            @else
                                <span class="text-sm text-slate-400">تعطیل</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('hours.days.*') <p class="error">ساعت‌ها را درست وارد کنید.</p> @enderror
            @endif
        </section>

        {{-- ===== گالری ===== --}}
        <section class="card space-y-4 p-6">
            <div>
                <h2 class="font-bold">🖼️ نمونه کار و محصولات</h2>
                <p class="mt-1 text-sm text-slate-500">حداکثر {{ fa_number(config('site.gallery_max')) }} تصویر. هر تصویر بعد از تأیید مدیر نمایش داده می‌شود.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($photos as $i => $photo)
                    <div class="space-y-1.5" wire:key="photo-{{ $photo['id'] }}">
                        <div class="relative aspect-square overflow-hidden rounded-xl bg-slate-100">
                            <img src="{{ $photo['url'] }}" class="size-full object-cover">
                            <x-status-badge :status="App\Enums\ModerationStatus::from($photo['status'])" class="absolute top-1.5 right-1.5 text-[10px]" />
                            <div class="absolute inset-x-1.5 bottom-1.5 flex justify-between">
                                <span class="flex gap-1">
                                    <button type="button" wire:click="movePhoto({{ $i }}, -1)" class="rounded-lg bg-black/50 px-2 text-white" title="قبلی">→</button>
                                    <button type="button" wire:click="movePhoto({{ $i }}, 1)" class="rounded-lg bg-black/50 px-2 text-white" title="بعدی">←</button>
                                </span>
                                <button type="button" wire:click="deletePhoto({{ $photo['id'] }})" wire:confirm="این تصویر حذف شود؟" class="rounded-lg bg-rose-600/90 px-2 text-xs text-white">حذف</button>
                            </div>
                        </div>
                        <input wire:model="photos.{{ $i }}.caption" class="input py-1.5 text-xs" placeholder="توضیح کوتاه">
                    </div>
                @endforeach

                @foreach ($newPhotos as $i => $file)
                    <div class="space-y-1.5" wire:key="new-{{ $i }}">
                        <div class="relative aspect-square overflow-hidden rounded-xl bg-slate-100 ring-2 ring-violet-300">
                            <img src="{{ $file->temporaryUrl() }}" class="size-full object-cover">
                            <span class="chip absolute top-1.5 right-1.5 bg-violet-600 text-[10px] text-white">جدید</span>
                            <button type="button" wire:click="removeNewPhoto({{ $i }})" class="absolute bottom-1.5 left-1.5 rounded-lg bg-rose-600/90 px-2 text-xs text-white">حذف</button>
                        </div>
                        <input wire:model="newCaptions.{{ $i }}" class="input py-1.5 text-xs" placeholder="توضیح کوتاه">
                    </div>
                @endforeach

                @if (count($photos) + count($newPhotos) < config('site.gallery_max'))
                    <label class="grid aspect-square cursor-pointer place-items-center rounded-xl border-2 border-dashed border-slate-300 text-center text-sm text-slate-500 hover:border-violet-400 hover:bg-violet-50">
                        <span wire:loading.remove wire:target="newPhotos">➕<br>افزودن تصویر</span>
                        <span wire:loading wire:target="newPhotos">در حال بارگذاری...</span>
                        <input type="file" wire:model="newPhotos" accept="image/*" multiple class="sr-only">
                    </label>
                @endif
            </div>
            @error('newPhotos.*') <p class="error">{{ $message }}</p> @enderror
        </section>

        <div class="flex gap-2">
            <button class="btn-primary px-8" wire:loading.attr="disabled" wire:target="save">{{ $listing ? 'ذخیره تغییرات' : 'ثبت پیج' }}</button>
            <a href="{{ route('dashboard') }}" wire:navigate class="btn-light">انصراف</a>
        </div>
        @if ($errors->any())
            <p class="error">لطفاً خطاهای فرم را برطرف کنید.</p>
        @endif
    </form>
</x-dashboard-shell>
