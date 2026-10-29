<?php

namespace App\Livewire\Dashboard;

use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\Platform;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    // ---------- گالری ----------
    /** عکس‌های ذخیره‌شده: [['id' => 1, 'url' => '...', 'caption' => '...', 'status' => 'approved'], ...] */
    public array $photos = [];

    /** عکس‌های تازه انتخاب‌شده (هنوز ذخیره نشده) */
    public array $newPhotos = [];

    public array $newCaptions = [];

    public function mount(?Listing $listing = null): void
    {
        $this->hours = Listing::defaultHours();

        if (! $listing?->exists) {
            $this->listing = null;
            $user = Auth::user();
            $this->fill(['province' => $user->province ?? '', 'city' => $user->city ?? '']);

            return;
        }

        abort_unless($listing->user_id === Auth::id(), 403);

        $this->fill($listing->only(['platform_id', 'handle', 'title', 'followers', 'description', 'country', 'online_only']));
        $this->fill([
            'province' => $listing->province ?? '',
            'city' => $listing->city ?? '',
            'address' => $listing->address ?? '',
            'phone' => $listing->phone ?? '',
            'hours' => $listing->hours ?: Listing::defaultHours(),
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
        $this->followers = en_digits((string) $this->followers);

        $this->validate([
            'platform_id' => ['required', 'exists:platforms,id'],
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
        ], [
            'cat1.required' => 'دسته‌بندی را انتخاب کنید.',
            'phone.regex' => 'شماره تلفن را با کد شهر یا موبایل وارد کنید؛ مثل 02112345678.',
        ], [
            'platform_id' => 'پلتفرم', 'handle' => 'آیدی', 'title' => 'نام پیج', 'description' => 'توضیحات', 'image' => 'تصویر',
        ]);

        $platform = Platform::findOrFail($this->platform_id);
        $handle = $platform->cleanHandle($this->handle);

        $duplicate = Listing::where('platform_id', $platform->id)->where('handle', $handle)
            ->when($this->listing, fn ($q) => $q->whereKeyNot($this->listing->id))
            ->exists();
        if ($duplicate || $handle === '') {
            $this->addError('handle', $duplicate ? 'این پیج قبلاً در نشونی ثبت شده است.' : 'آیدی معتبر نیست.');

            return;
        }

        $category = Category::fromCache($this->cat3 ?: ($this->cat2 ?: $this->cat1));

        $attributes = [
            'platform_id' => $platform->id,
            'handle' => $handle,
            'title' => trim($this->title),
            'category_id' => $category->id,
            'followers' => $this->followers === '' ? null : (int) $this->followers,
            'description' => trim($this->description),
            'country' => $this->country,
            'province' => $this->province ?: null,
            'city' => $this->city ?: null,
            'online_only' => $this->online_only,
            'address' => $this->online_only ? null : (trim($this->address) ?: null),
            'phone' => $this->phone ?: null,
            'hours' => $this->hours,
        ];

        $listing = $this->listing ?? new Listing(['user_id' => Auth::id(), 'status' => ModerationStatus::Pending]);

        if ($this->image) {
            if ($listing->image) {
                Storage::disk('public')->delete($listing->image);
            }
            $attributes['image'] = $this->image->store('listings', 'public');
        }

        $listing->fill($attributes);

        // تغییر اطلاعات اصلی پیج تأییدشده = بررسی دوباره
        $needsReview = $listing->exists && ($listing->isDirty() || $listing->status === ModerationStatus::Rejected);
        if ($needsReview) {
            $listing->status = ModerationStatus::Pending;
            $listing->reject_reason = null;
        }

        $isNew = ! $listing->exists;
        $listing->save();

        $this->saveCaptions();
        $this->saveNewPhotos($listing);

        $message = match (true) {
            $isNew => 'پیج ثبت شد و بعد از تأیید مدیر نمایش داده می‌شود.',
            $needsReview => 'تغییرات ذخیره شد. پیج دوباره بررسی و منتشر می‌شود.',
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
            'platforms' => Platform::active()->get(),
            'selectedPlatform' => $this->platform_id ? Platform::find($this->platform_id) : null,
            'roots' => Category::roots(),
            'subs1' => $level1?->subs() ?? collect(),
            'subs2' => $level2?->subs() ?? collect(),
            'provinces' => array_keys(config("locations.{$this->country}.provinces", [])),
            'cities' => config("locations.{$this->country}.provinces.{$this->province}", []),
        ])->title($this->listing ? 'ویرایش پیج' : 'ثبت پیج جدید');
    }
}
