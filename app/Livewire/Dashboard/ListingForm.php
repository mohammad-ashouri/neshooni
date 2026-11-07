<?php

namespace App\Livewire\Dashboard;

use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\Platform;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * فرم ثبت و ویرایش پیج.
 *
 * قانون بررسی مدیر:
 * - پیج جدید همیشه «در انتظار تأیید» است.
 * - اگر اطلاعات اصلی پیج (عنوان، آیدی، دسته، آدرس و...) تغییر کند، پیج دوباره به صف تأیید می‌رود.
 * - تغییر فقط در گالری، پیج را از نمایش خارج نمی‌کند؛ هر عکس جدید جداگانه تأیید می‌شود.
 */
class ListingForm extends Component
{
    use WithFileUploads;

    public ?Listing $listing = null;

    /** آیا فرم برای سایت فروشگاهی است؟ (پلتفرم ثابت، بدون انتخاب پلتفرم و تعداد دنبال‌کننده) */
    #[Locked]
    public bool $isWebsite = false;

    public $platform_id = '';

    public string $handle = '';

    public string $title = '';

    // سه سطح دسته؛ category_id نهایی عمیق‌ترین سطح انتخاب‌شده است
    public $cat1 = '';

    public $cat2 = '';

    public $cat3 = '';

    public $followers = '';

    public string $description = '';

    /** عکس پروفایل پیج */
    public $image;

    // ---------- موقعیت ----------
    public string $country = 'IR';

    public string $province = '';

    public string $city = '';

    public bool $online_only = false;

    public string $address = '';

    public string $phone = '';

    public array $hours = [];

    // ---------- خرید قسطی ----------
    public bool $installment = false;

    /** کلیدهای Listing::INSTALLMENT_METHODS */
    public array $installments = [];

    public string $installment_note = '';

    // ---------- برچسب‌ها ----------
    /** نام برچسب‌ها؛ مثل ['کافه', 'تهرانسر'] */
    public array $tags = [];

    /** متنی که کاربر هنوز به برچسب تبدیل نکرده */
    public string $tagInput = '';

    // ---------- گالری ----------
    /** عکس‌های ذخیره‌شده: [['id' => 1, 'url' => '...', 'caption' => '...', 'status' => 'approved'], ...] */
    public array $photos = [];

    /** عکس‌های تازه انتخاب‌شده (هنوز ذخیره نشده) */
    public array $newPhotos = [];

    public array $newCaptions = [];

    /** $website از مسیر dashboard.websites.create می‌آید */
    public function mount(?Listing $listing = null, bool $website = false): void
    {
        $this->hours = Listing::defaultHours();

        if (! $listing?->exists) {
            $this->listing = null;
            $user = Auth::user();
            $this->fill(['province' => $user->province ?? '', 'city' => $user->city ?? '']);

            if ($website) {
                // اگر مدیر پلتفرم سایت فروشگاهی را غیرفعال کرده باشد، این صفحه وجود ندارد
                $this->isWebsite = true;
                $this->platform_id = (Platform::website() ?? abort(404))->id;
                $this->online_only = true;
            }

            return;
        }

        abort_unless($listing->user_id === Auth::id(), 403);
        $this->isWebsite = $listing->isWebsite();

        $this->fill($listing->only(['platform_id', 'handle', 'title', 'description']));
        $this->fill([
            'followers' => $listing->followers ?? '',
            'country' => $listing->country ?? 'IR',
            'online_only' => (bool) $listing->online_only,
            'province' => $listing->province ?? '',
            'city' => $listing->city ?? '',
            'address' => $listing->address ?? '',
            'phone' => $listing->phone ?? '',
            'hours' => $listing->hours ?: Listing::defaultHours(),
            'installment' => $listing->hasInstallments(),
            'installments' => $listing->installments ?? [],
            'installment_note' => $listing->installment_note ?? '',
            'tags' => $listing->tagNames(),
        ]);

        // دسته ذخیره‌شده به سه لیست کشویی تبدیل می‌شود
        $path = $listing->category?->path()->pluck('id')->all() ?? [];
        [$this->cat1, $this->cat2, $this->cat3] = array_pad($path, 3, '');

        $this->loadPhotos();
    }

    // ---------- وابستگی لیست‌های کشویی ----------

    public function updatedCat1(): void
    {
        $this->cat2 = $this->cat3 = '';
    }

    public function updatedCat2(): void
    {
        $this->cat3 = '';
    }

    public function updatedProvince(): void
    {
        $this->city = '';
    }

    // ---------- ساعات کاری ----------

    /** الگوهای آماده: «شنبه تا پنجشنبه ۹ تا ۲۱»، «همه روزها» و «شبانه‌روزی» */
    public function preset(string $type): void
    {
        $this->hours['enabled'] = true;
        foreach ($this->hours['days'] as $i => $day) {
            $this->hours['days'][$i] = match ($type) {
                'workdays' => ['open' => $i !== 6, 'from' => '09:00', 'to' => '21:00'],
                'everyday' => ['open' => true, 'from' => '09:00', 'to' => '21:00'],
                '24h' => ['open' => true, 'from' => '00:00', 'to' => '23:59'],
            };
        }
    }

    /** ساعت شنبه برای همه روزهای باز کپی می‌شود */
    public function copyFirstDay(): void
    {
        foreach ($this->hours['days'] as $i => $day) {
            if ($day['open']) {
                $this->hours['days'][$i]['from'] = $this->hours['days'][0]['from'];
                $this->hours['days'][$i]['to'] = $this->hours['days'][0]['to'];
            }
        }
    }

    // ---------- برچسب‌ها ----------

    /** متن کادر برچسب (یک یا چند برچسب جداشده با ویرگول) به فهرست اضافه می‌شود */
    public function addTag(): void
    {
        $this->pushTags(Tag::parse($this->tagInput));
        $this->tagInput = '';
    }

    /** انتخاب از برچسب‌های پیشنهادی */
    public function pickTag(string $name): void
    {
        $this->pushTags(Tag::parse($name));
    }

    public function removeTag(int $index): void
    {
        array_splice($this->tags, $index, 1);
    }

    private function pushTags(array $names): void
    {
        $merged = array_values(array_unique([...$this->tags, ...$names]));
        if (count($merged) > Tag::MAX_PER_LISTING) {
            $this->dispatch('toast', message: 'حداکثر '.fa_number(Tag::MAX_PER_LISTING).' برچسب می‌توانید اضافه کنید.', type: 'error');
        }
        $this->tags = array_slice($merged, 0, Tag::MAX_PER_LISTING);
    }

    // ---------- گالری ----------

    public function updatedNewPhotos(): void
    {
        $this->validate(['newPhotos.*' => ['image', 'max:3072']], [], ['newPhotos.*' => 'تصویر']);

        $free = config('site.gallery_max') - count($this->photos);
        if (count($this->newPhotos) > $free) {
            $this->newPhotos = array_slice($this->newPhotos, 0, max(0, $free));
            $this->dispatch('toast', message: 'حداکثر '.fa_number(config('site.gallery_max')).' تصویر می‌توانید داشته باشید.', type: 'error');
        }
    }

    public function removeNewPhoto(int $index): void
    {
        array_splice($this->newPhotos, $index, 1);
        array_splice($this->newCaptions, $index, 1);
    }

    public function deletePhoto(int $id): void
    {
        $photo = $this->listing->photos()->findOrFail($id);
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        $this->loadPhotos();
    }

    /** جابه‌جایی ترتیب عکس: $direction = -1 (قبل) یا 1 (بعد) */
    public function movePhoto(int $index, int $direction): void
    {
        $target = $index + $direction;
        if (! isset($this->photos[$index], $this->photos[$target])) {
            return;
        }

        [$this->photos[$index], $this->photos[$target]] = [$this->photos[$target], $this->photos[$index]];
        foreach ($this->photos as $sort => $photo) {
            ListingPhoto::whereKey($photo['id'])->where('listing_id', $this->listing->id)->update(['sort' => $sort]);
        }
    }

    // ---------- ذخیره ----------

    public function save()
    {
        $this->handle = en_digits($this->handle);
        $this->phone = en_digits($this->phone);
        $this->followers = $this->isWebsite ? '' : en_digits((string) $this->followers);
        // برچسبی که نوشته شده ولی Enter نخورده هم ذخیره شود
        if (trim($this->tagInput) !== '') {
            $this->addTag();
        }

        // فرم سایت فقط پلتفرم سایت و فرم پیج فقط پلتفرم‌های اجتماعی را می‌پذیرد
        $kindRule = function (string $attribute, $value, \Closure $fail) {
            if (Platform::whereKey($value)->value('handle_type') === 'url' xor $this->isWebsite) {
                $fail('پلتفرم انتخاب‌شده معتبر نیست.');
            }
        };

        $this->validate([
            'platform_id' => ['required', 'exists:platforms,id', $kindRule],
            'handle' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'min:2', 'max:80'],
            'cat1' => ['required'],
            'followers' => ['nullable', 'integer', 'min:0'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'province' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:300'],
            'phone' => ['nullable', 'regex:/^0\d{9,10}$/'],
            'hours.days.*.from' => ['required', 'date_format:H:i'],
            'hours.days.*.to' => ['required', 'date_format:H:i'],
            'photos.*.caption' => ['nullable', 'string', 'max:80'],
            'newCaptions.*' => ['nullable', 'string', 'max:80'],
            'installments' => $this->installment ? ['required', 'array', 'min:1'] : ['array'],
            'installments.*' => ['string', 'in:'.implode(',', array_keys(Listing::INSTALLMENT_METHODS))],
            'installment_note' => ['nullable', 'string', 'max:150'],
            'tags' => ['array', 'max:'.Tag::MAX_PER_LISTING],
            'tags.*' => ['string', 'min:2', 'max:'.Tag::MAX_LENGTH],
        ], [
            'cat1.required' => 'دسته‌بندی را انتخاب کنید.',
            'installments.required' => 'حداقل یک روش خرید قسطی را انتخاب کنید.',
            'installments.min' => 'حداقل یک روش خرید قسطی را انتخاب کنید.',
            'phone.regex' => 'شماره تلفن را با کد شهر یا موبایل وارد کنید؛ مثل 02112345678.',
        ], [
            'platform_id' => 'پلتفرم',
            'handle' => $this->isWebsite ? 'آدرس سایت' : 'آیدی',
            'title' => $this->isWebsite ? 'نام فروشگاه' : 'نام پیج',
            'description' => 'توضیحات', 'image' => 'تصویر',
            'installment_note' => 'شرایط اقساط',
            'tags' => 'برچسب‌ها',
        ]);
        $kind = $this->isWebsite ? 'سایت' : 'پیج';

        $platform = Platform::findOrFail($this->platform_id);
        $handle = $platform->cleanHandle($this->handle);

        $duplicate = Listing::where('platform_id', $platform->id)->where('handle', $handle)
            ->when($this->listing, fn ($q) => $q->whereKeyNot($this->listing->id))
            ->exists();
        if ($duplicate || $handle === '') {
            $this->addError('handle', match (true) {
                $duplicate => "این {$kind} قبلاً در نشونی ثبت شده است.",
                $this->isWebsite => 'آدرس سایت معتبر نیست؛ مثلاً myshop.ir',
                default => 'آیدی معتبر نیست.',
            });

            return;
        }

        $category = Category::fromCache($this->cat3 ?: ($this->cat2 ?: $this->cat1));

        $attributes = [
            'platform_id' => $platform->id,
            'handle' => $handle,
            'title' => trim($this->title),
            'category_id' => $category->id,
            'followers' => (int) $this->followers,
            'description' => trim($this->description),
            'country' => $this->country,
            'province' => $this->province ?: null,
            'city' => $this->city ?: null,
            'online_only' => $this->online_only,
            'address' => $this->online_only ? null : (trim($this->address) ?: null),
            'phone' => $this->phone ?: null,
            // پیجی که هیچ‌وقت ساعت کاری نداشته و الان هم فعالش نکرده، همان خالی می‌ماند
            // (وگرنه ساعت پیش‌فرض فرم «تغییر» حساب می‌شد و پیج بی‌دلیل به صف بررسی می‌رفت)
            'hours' => $this->hours['enabled'] || $this->listing?->hours ? $this->hours : null,
            // ترتیب ثابت تا جابه‌جایی تیک‌ها «تغییر» حساب نشود و پیج بی‌دلیل به صف بررسی نرود
            'installments' => $this->installment
                ? array_values(array_intersect(array_keys(Listing::INSTALLMENT_METHODS), $this->installments))
                : null,
            'installment_note' => $this->installment ? (trim($this->installment_note) ?: null) : null,
        ];

        $listing = $this->listing ?? new Listing(['user_id' => Auth::id(), 'status' => ModerationStatus::Pending]);

        if ($this->image) {
            if ($listing->image) {
                Storage::disk('public')->delete($listing->image);
            }
            $attributes['image'] = $this->image->store('listings', 'public');
        }

        $listing->fill($attributes);

        // برچسب‌ها هم جزو اطلاعات اصلی‌اند (در جستجو دیده می‌شوند)؛ ترتیب مهم نیست
        $tags = collect($this->tags)->map(fn (string $t) => Tag::normalize($t))->filter()->unique()->sort()->values()->all();
        $tagsChanged = $listing->exists && $tags !== collect($listing->tagNames())->sort()->values()->all();

        // تغییر اطلاعات اصلی پیج تأییدشده = بررسی دوباره
        $needsReview = $listing->exists && ($listing->isDirty() || $tagsChanged || $listing->status === ModerationStatus::Rejected);
        if ($needsReview) {
            $listing->status = ModerationStatus::Pending;
            $listing->reject_reason = null;
        }

        $isNew = ! $listing->exists;
        $listing->save();
        $listing->syncTags($tags);

        $this->saveCaptions();
        $this->saveNewPhotos($listing);

        $message = match (true) {
            $isNew => "{$kind} ثبت شد و بعد از تأیید مدیر نمایش داده می‌شود.",
            $needsReview => "تغییرات ذخیره شد. {$kind} دوباره بررسی و منتشر می‌شود.",
            default => 'تغییرات ذخیره شد.',
        };
        session()->flash('toast', ['message' => $message]);

        return $this->redirectRoute('dashboard', navigate: true);
    }

    private function saveCaptions(): void
    {
        foreach ($this->photos as $photo) {
            ListingPhoto::whereKey($photo['id'])->update(['caption' => trim($photo['caption'] ?? '') ?: null]);
        }
    }

    private function saveNewPhotos(Listing $listing): void
    {
        $sort = count($this->photos);
        foreach ($this->newPhotos as $i => $file) {
            $listing->photos()->create([
                'path' => $file->store('gallery', 'public'),
                'caption' => trim($this->newCaptions[$i] ?? '') ?: null,
                'status' => ModerationStatus::Pending,
                'sort' => $sort++,
            ]);
        }
    }

    private function loadPhotos(): void
    {
        $this->photos = $this->listing->photos()->get()
            ->map(fn (ListingPhoto $p) => ['id' => $p->id, 'url' => $p->url(), 'caption' => $p->caption ?? '', 'status' => $p->status->value])
            ->all();
    }

    public function render()
    {
        $level1 = Category::fromCache($this->cat1);
        $level2 = Category::fromCache($this->cat2);

        return view('livewire.dashboard.listing-form', [
            'platforms' => $this->isWebsite ? collect() : Platform::active()->social()->get(),
            'selectedPlatform' => $this->platform_id ? Platform::find($this->platform_id) : null,
            'roots' => Category::roots(),
            'subs1' => $level1?->subs() ?? collect(),
            'subs2' => $level2?->subs() ?? collect(),
            'provinces' => array_keys(config("locations.{$this->country}.provinces", [])),
            'cities' => config("locations.{$this->country}.provinces.{$this->province}", []),
            'suggestedTags' => Tag::popular(15)->diff($this->tags)->take(10),
        ])->title($this->pageTitle());
    }

    private function pageTitle(): string
    {
        if ($this->listing) {
            return $this->isWebsite ? 'ویرایش سایت فروشگاهی' : 'ویرایش پیج';
        }

        return $this->isWebsite ? 'ثبت سایت فروشگاهی' : 'ثبت پیج جدید';
    }
}
