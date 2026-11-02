@extends('errors.layout')

@section('code', '503')
@section('title', 'در حال به‌روزرسانی')
@section('message')
    {{ ($maintenanceMessage ?? null) ?: 'سایت برای به‌روزرسانی چند دقیقه در دسترس نیست. به‌زودی برمی‌گردیم.' }}
    @if (! empty($endsAt))
        <span class="mt-2 block text-sm">بازگشت تقریبی: {{ fa_datetime($endsAt) }}</span>
    @endif
@endsection

{{-- در حالت تعمیر بقیه صفحه‌ها هم بسته‌اند؛ فقط راه ورود مدیر می‌ماند --}}
@section('actions')
    <a href="/" class="cursor-pointer btn-primary">صفحه اصلی</a>
    <a href="/login" class="cursor-pointer btn-light">ورود به پنل</a>
@endsection
