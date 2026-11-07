<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Services\PaymentGateways;
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

    #[Url(except: '')]
    public string $gateway = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render(PaymentGateways $gateways)
    {
        $query = Payment::query()
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->gateway, fn ($q) => $q->where('gateway', $this->gateway));

        return view('livewire.admin.payments', [
            'payments' => (clone $query)->with('user')->latest()->paginate(20),
            'sum' => (clone $query)->sum('amount'),
            'revenue' => [
                'today' => Payment::paid()->whereDate('paid_at', today())->sum('amount'),
                'month' => Payment::paid()->where('paid_at', '>=', now()->subDays(30))->sum('amount'),
                'featured' => Payment::paid()->where('type', 'featured')->sum('amount'),
                'verify' => Payment::paid()->where('type', 'verify')->sum('amount'),
            ],
            'activeGateways' => $gateways->available(),
            // فیلتر درگاه: هم درگاه‌های فعال و هم درگاه‌هایی که قبلاً با آن‌ها پرداخت شده
            'usedGateways' => Payment::whereNotNull('gateway')->distinct()->pluck('gateway')
                ->merge(array_keys($gateways->available()))->unique()
                ->mapWithKeys(fn ($name) => [$name => config("payment.labels.{$name}", $name)])->all(),
        ]);
    }
}
