<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin'), Title('پرداخت‌ها و درآمد')]
class Payments extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: 'paid')]
    public string $status = 'paid';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Payment::query()
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));

        return view('livewire.admin.payments', [
            'payments' => (clone $query)->with('user')->latest()->paginate(20),
            'sum' => (clone $query)->sum('amount'),
            'revenue' => [
                'today' => Payment::paid()->whereDate('paid_at', today())->sum('amount'),
                'month' => Payment::paid()->where('paid_at', '>=', now()->subDays(30))->sum('amount'),
                'featured' => Payment::paid()->where('type', 'featured')->sum('amount'),
                'verify' => Payment::paid()->where('type', 'verify')->sum('amount'),
            ],
        ]);
    }
}
