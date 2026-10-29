<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('پروفایل')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $country = 'IR';

    public string $province = '';

    public string $city = '';

    public string $bio = '';

    public $avatar;

    // تغییر رمز
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->fill([
            'name' => $user->name,
            'country' => $user->country ?? 'IR',
            'province' => $user->province ?? '',
            'city' => $user->city ?? '',
            'bio' => $user->bio ?? '',
        ]);
    }

    public function updatedProvince(): void
    {
        $this->city = '';
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'country' => ['required', 'string', 'size:2'],
            'province' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [], ['name' => 'نام', 'bio' => 'معرفی', 'avatar' => 'تصویر']);

        $user = Auth::user();
        unset($data['avatar']);

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $this->avatar->store('avatars', 'public');
            $this->avatar = null;
        }

        $user->update($data);
        $this->dispatch('toast', message: 'پروفایل ذخیره شد.');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], ['current_password.current_password' => 'رمز فعلی اشتباه است.']);

        Auth::user()->update(['password' => $this->password]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('toast', message: 'رمز عبور تغییر کرد.');
    }

    public function render()
    {
        return view('livewire.dashboard.profile', [
            'provinces' => array_keys(config("locations.{$this->country}.provinces", [])),
            'cities' => config("locations.{$this->country}.provinces.{$this->province}", []),
        ]);
    }
}
