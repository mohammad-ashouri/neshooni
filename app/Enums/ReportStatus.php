<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'در انتظار بررسی',
            self::Resolved => 'رسیدگی شده',
            self::Dismissed => 'رد شده',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Resolved => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Dismissed => 'bg-slate-100 text-slate-500 ring-slate-200',
        };
    }
}
