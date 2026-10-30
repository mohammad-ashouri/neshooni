<?php

namespace App\Livewire\Pages;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\Report;
use App\Models\Review;
use App\Services\InterestTracker;
use App\Services\ListingStats;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * صفحه نمایش یک پیج: گالری، ساعات کاری، دنبال کردن، اشتراک‌گذاری، گزارش و نظرات.
 */
class ListingShow extends Component
{
    public Listing $listing;

    /** ایندکس عکسی که در نمایش بزرگ باز است (null = بسته) */
    public ?int $photo = null;

    public bool $showShare = false;

    // ---------- گزارش تخلف ----------
    /** 'listing' یا شناسه نظر */
    public ?string $reportTarget = null;

    public string $reportReason = '';

    public string $reportBody = '';

    // ---------- فرم نظر ----------
    #[Validate('required|integer|between:1,5', message: 'امتیاز را انتخاب کنید.')]
    public int $rating = 0;

    #[Validate('required|string|min:5|max:1000', as: 'متن نظر')]
    public string $body = '';

    public function mount(Listing $listing, ListingStats $stats, InterestTracker $tracker): void
    {
        $isOwner = Auth::id() === $listing->user_id;

        // پیج تأییدنشده فقط برای صاحبش و ناظرها قابل مشاهده است
        abort_unless($listing->status === ModerationStatus::Approved || $isOwner || Gate::allows('listings.moderate'), 404);

        if (! $isOwner) {
            $stats->record($listing, 'views');
            $tracker->add($listing->category_id, 'view');
        }

        // اگر کاربر قبلاً نظر داده، فرم با همان نظر پر می‌شود
        if ($review = $this->myReview) {
            $this->rating = $review->rating;
            $this->body = $review->body;
        }
    }

    #[Computed]
    public function isOwner(): bool
    {
        return Auth::id() === $this->listing->user_id;
    }

    #[Computed]
    public function isFollowing(): bool
    {
        return Auth::check() && $this->listing->fans()->whereKey(Auth::id())->exists();
    }

    #[Computed]
    public function myReview(): ?Review
    {
        return Auth::check() ? $this->listing->reviews()->where('user_id', Auth::id())->first() : null;
    }

    #[Computed]
    public function photos()
    {
        return $this->listing->approvedPhotos;
    }

    // ---------- گالری ----------

    public function showPhoto(int $index): void
    {
        $this->photo = $index;
    }

    public function nextPhoto(int $step = 1): void
    {
        $count = $this->photos->count();
        $this->photo = ($this->photo + $step + $count) % $count;
    }

    // ---------- دنبال کردن ----------

    public function toggleFollow(InterestTracker $tracker)
    {
        if (! Auth::check()) {
            return $this->redirectRoute('login', navigate: true);
        }

        if ($this->isFollowing) {
            $this->listing->fans()->detach(Auth::id());
            $this->dispatch('toast', message: 'دیگر این پیج را دنبال نمی‌کنید.', type: 'info');
        } else {
            $this->listing->fans()->attach(Auth::id());
            $tracker->add($this->listing->category_id, 'follow');
            $this->dispatch('interest-updated');
            $this->dispatch('toast', message: 'پیج به لیست دنبال‌شده‌ها اضافه شد.');
        }

        unset($this->isFollowing);
    }

    /** کلیک روی «مشاهده در اینستاگرام/...» شمرده می‌شود و کاربر به پیج اصلی می‌رود */
    public function visit(ListingStats $stats)
    {
        if (! $this->isOwner) {
            $stats->record($this->listing, 'clicks');
        }

        return $this->redirect($this->listing->externalUrl());
    }

    // ---------- گزارش ----------

    public function openReport(string $target)
    {
        if (! Auth::check()) {
            session()->flash('toast', ['message' => 'برای گزارش تخلف ابتدا وارد شوید.', 'type' => 'info']);

            return $this->redirectRoute('login', navigate: true);
        }

        $this->reset('reportReason', 'reportBody');
        $this->reportTarget = $target;
    }

    public function submitReport(): void
    {
        $type = $this->reportTarget === 'listing' ? 'listing' : 'review';
        $this->validate([
            'reportReason' => ['required', 'in:'.implode(',', Report::REASONS[$type])],
            'reportBody' => ['nullable', 'string', 'max:1000'],
        ], ['reportReason.required' => 'دلیل گزارش را انتخاب کنید.']);

        $target = $type === 'listing' ? $this->listing : $this->listing->reviews()->findOrFail($this->reportTarget);

        $target->reports()->create([
            'reporter_id' => Auth::id(),
            'reason' => $this->reportReason,
            'body' => $this->reportBody,
        ]);

        $this->reportTarget = null;
        $this->dispatch('toast', message: 'گزارش شما ثبت شد و بررسی می‌شود. ممنون!');
    }

    // ---------- نظر ----------

    public function submitReview(InterestTracker $tracker)
    {
        if (! Auth::check()) {
            return $this->redirectRoute('login', navigate: true);
        }
        abort_if($this->isOwner, 403);

        $this->validate();
        $isNew = $this->myReview === null;

        // هر کاربر یک نظر دارد؛ ویرایش نظر دوباره به صف تأیید می‌رود
        $this->listing->reviews()->updateOrCreate(
            ['user_id' => Auth::id()],
            ['rating' => $this->rating, 'body' => trim($this->body), 'status' => ModerationStatus::Pending],
        );

        if ($isNew) {
            $tracker->add($this->listing->category_id, 'review');
            $this->dispatch('interest-updated');
        }

        unset($this->myReview);
        $this->dispatch('toast', message: 'نظر شما ثبت شد و بعد از تأیید مدیر نمایش داده می‌شود.');
    }

    public function render()
    {
        $reviews = $this->listing->approvedReviews()->with('user')->get();

        return view('livewire.pages.listing-show', [
            'reviews' => $reviews,
            'average' => $reviews->avg('rating') ?? 0,
            'distribution' => collect(range(5, 1))->mapWithKeys(fn ($star) => [$star => $reviews->where('rating', $star)->count()]),
            'followersCount' => $this->listing->fans()->count(),
            'hours' => $this->listing->hoursStatus(),
            'similar' => Listing::approved()->forCards()
                ->where('id', '!=', $this->listing->id)
                ->when($this->listing->category, fn ($q) => $q->inCategory($this->listing->category->parent_id ? $this->listing->category->parent : $this->listing->category))
                ->inRandomOrder()->limit(4)->get(),
        ])->title($this->listing->title);
    }
}
