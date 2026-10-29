<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('دنبال‌شده‌ها')]
class Following extends Component
{
    public function unfollow(int $listingId): void
    {
        Auth::user()->follows()->detach($listingId);
        $this->dispatch('toast', message: 'از لیست دنبال‌شده‌ها حذف شد.', type: 'info');
    }

    public function render()
    {
        return view('livewire.dashboard.following', [
            'listings' => Auth::user()->follows()->approved()->forCards()->latest('follows.created_at')->get(),
        ]);
    }
}
