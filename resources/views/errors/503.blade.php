@extends('errors.layout')

@section('code', '503')
@section('title', 'در حال به‌روزرسانی')
@section('message')
    {{ ($maintenanceMessage ?? null) ?: 'سایت برای به‌روزرسانی چند دقیقه در دسترس نیست. به‌زودی برمی‌گردیم.' }}
    @if (! empty($endsAt))
        <span class="mt-2 block text-sm">بازگشت تقریبی: {{ fa_datetime($endsAt) }}</span>
    @endif
@endsection
