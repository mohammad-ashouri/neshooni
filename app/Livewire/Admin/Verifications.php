<?php

namespace App\Livewire\Admin;

use App\Enums\ModerationStatus;
use App\Models\VerificationRequest;
use App\Notifications\SiteMessage;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * بررسی درخواست‌های نشان تأیید. فقط درخواست‌های پرداخت‌شده قابل تأیید هستند.
 */
#[Layout('layouts::admin'), Title('درخواست‌های نشان تأیید')]
class Verifications extends Component
{
    #[Url]
    public string $status = 'pending';

    public ?int $rejecting = null;

    public string $reason = '';

    /** مدرک روی دیسک خصوصی است و فقط از این طریق دانلود می‌شود */
    public function download(int $id)
    {
        $request = VerificationRequest::findOrFail($id);
        $disk = Storage::disk('local')->exists($request->document) ? 'local' : 'public';

        return Storage::disk($disk)->download($request->document);
    }

    public function approve(int $id): void
    {
        $request = VerificationRequest::with('user')->findOrFail($id);
        $request->update(['status' => ModerationStatus::Approved, 'reason' => null]);
        $request->user->update(['verified_at' => now()]);
        $request->user->notify(new SiteMessage('تبریک! نشان تأیید کسب‌وکار شما فعال شد.', route('dashboard.verification')));

        $this->dispatch('toast', message: 'نشان تأیید برای '.$request->user->name.' فعال شد.');
    }

    public function reject(): void
    {
        $this->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'دلیل رد']);

        $request = VerificationRequest::with('user')->findOrFail($this->rejecting);
        $request->update(['status' => ModerationStatus::Rejected, 'reason' => $this->reason]);
        $request->user->notify(new SiteMessage('درخواست نشان تأیید رد شد: '.$this->reason, route('dashboard.verification')));

        $this->reset('rejecting', 'reason');
        $this->dispatch('toast', message: 'درخواست رد شد.', type: 'info');
    }

    public function render()
    {
        return view('livewire.admin.verifications', [
            'requests' => VerificationRequest::with(['user', 'user.payments' => fn ($q) => $q->where('type', 'verify')])
                ->where('status', $this->status)
                ->latest()
                ->get(),
        ]);
    }
}
