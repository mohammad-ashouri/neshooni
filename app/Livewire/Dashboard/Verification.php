<?php

namespace App\Livewire\Dashboard;

use App\Enums\ModerationStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * درخواست نشان تأیید (تیک آبی).
 * کاربر مدرک را بارگذاری می‌کند، هزینه بررسی را می‌پردازد و مدیر درخواست را تأیید یا رد می‌کند.
 */
#[Title('نشان تأیید')]
class Verification extends Component
{
    use WithFileUploads;

    public string $business_type = 'individual';

    public $document;

    public string $note = '';

    public function submit()
    {
        abort_if(Auth::user()->isVerified(), 403);

        $this->validate([
            'business_type' => ['required', 'in:individual,company'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [], ['document' => 'مدرک', 'note' => 'توضیحات']);

        $user = Auth::user();

        $request = $user->verificationRequests()->create([
            'business_type' => $this->business_type,
            // مدارک روی دیسک خصوصی (local) ذخیره می‌شوند و فقط مدیر آن‌ها را می‌بیند
            'document' => $this->document->store('verifications', 'local'),
            'note' => trim($this->note) ?: null,
            'status' => ModerationStatus::Pending,
        ]);

        $payment = $user->payments()->create([
            'type' => 'verify',
            'verification_request_id' => $request->id,
            'amount' => config('site.verification_price'),
            'title' => 'بررسی مدارک نشان تأیید',
            'status' => 'pending',
        ]);

        return $this->redirectRoute('checkout', $payment, navigate: true);
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.dashboard.verification', [
            'user' => $user,
            'latest' => $user->verificationRequests()->latest()->first(),
            'unpaid' => $user->payments()->where('type', 'verify')->where('status', 'pending')->latest()->first(),
        ]);
    }
}
