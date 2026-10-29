<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('پرداخت‌های من')]
class Payments extends Component
{
    use WithPagination;

    public function render()
    {
        $payments = Auth::user()->payments();

        return view('livewire.dashboard.payments', [
            'payments' => (clone $payments)->with('listing')->latest()->paginate(15),
            'total' => (clone $payments)->paid()->sum('amount'),
        ]);
    }
}
