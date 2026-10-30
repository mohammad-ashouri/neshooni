<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin'), Title('پیام‌های تماس')]
class Messages extends Component
{
    use WithPagination;

    /** پیامی که باز است */
    public ?int $openId = null;

    public function open(int $id): void
    {
        $this->openId = $this->openId === $id ? null : $id;
        ContactMessage::whereKey($id)->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function delete(int $id): void
    {
        Gate::authorize('messages.delete');
        ContactMessage::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'پیام حذف شد.');
    }

    public function render()
    {
        return view('livewire.admin.messages', [
            'messages' => ContactMessage::latest()->paginate(20),
        ]);
    }
}
