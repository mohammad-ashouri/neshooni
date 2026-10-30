<?php

namespace App\Livewire\Dashboard;

use App\Models\Listing;
use App\Services\ListingSeoAudit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * ابزار سئوی پیج برای صاحب کسب‌وکار:
 * پیش‌نمایش نتیجه گوگل، عنوان و توضیح اختصاصی، امتیاز و چک‌لیست، کلمات کلیدی و لینک ابزارهای بررسی.
 *
 * عنوان و توضیح در جدول seo پکیج laravel-seo ذخیره می‌شود و Listing::seoData() از آن استفاده می‌کند.
 * چون فقط متا است (روی محتوای صفحه اثری ندارد) پیج را به صف بررسی نمی‌برد.
 */
#[Title('سئوی پیج')]
class SeoTools extends Component
{
    public ?int $listingId = null;

    public string $seoTitle = '';

    public string $seoDescription = '';

    public function mount(?Listing $listing = null): void
    {
        if ($listing?->exists) {
            abort_unless($listing->user_id === Auth::id(), 403);
            $this->listingId = $listing->id;
        } else {
            $this->listingId = Auth::user()->listings()->latest()->value('id');
        }

        $this->loadFields();
    }

    public function updatedListingId(): void
    {
        unset($this->listing);
        $this->loadFields();
    }

    #[Computed]
    public function listing(): ?Listing
    {
        return $this->listingId ? Auth::user()->listings()->with(['platform', 'category'])->find($this->listingId) : null;
    }

    public function save(): void
    {
        $this->seoTitle = str($this->seoTitle)->squish()->toString();
        $this->seoDescription = str($this->seoDescription)->squish()->toString();

        $this->validate([
            'seoTitle' => ['nullable', 'string', 'max:70'],
            'seoDescription' => ['nullable', 'string', 'max:200'],
        ], [], ['seoTitle' => 'عنوان گوگل', 'seoDescription' => 'توضیح گوگل']);

        // seo رابطه withDefault دارد؛ برای پیج‌های قدیمی که ردیف ندارند، همین‌جا ساخته می‌شود
        $this->listing->seo->fill([
            'title' => $this->seoTitle ?: null,
            'description' => $this->seoDescription ?: null,
        ])->save();

        unset($this->listing);
        $this->dispatch('toast', message: 'تنظیمات سئو ذخیره شد.');
    }

    /** خالی کردن فیلدها = برگشت به عنوان و توضیح خودکار */
    public function useAuto(): void
    {
        $this->seoTitle = $this->seoDescription = '';
        $this->save();
    }

    private function loadFields(): void
    {
        $this->seoTitle = $this->listing?->seo->title ?? '';
        $this->seoDescription = $this->listing?->seo->description ?? '';
        $this->resetErrorBag();
    }

    public function render(ListingSeoAudit $audit)
    {
        $listing = $this->listing;

        return view('livewire.dashboard.seo-tools', [
            'listings' => Auth::user()->listings()->latest()->get(['id', 'title']),
            'audit' => $listing ? $audit->run($listing) : null,
            // پیش‌نمایش گوگل با همان متنی که الان در فرم است (قبل از ذخیره)
            'previewTitle' => ($this->seoTitle ?: $listing?->autoSeoTitle()).config('seo.title.suffix'),
            'previewDescription' => $this->seoDescription ?: $listing?->autoSeoDescription(),
            'url' => $listing ? route('listings.show', $listing) : null,
        ]);
    }
}
