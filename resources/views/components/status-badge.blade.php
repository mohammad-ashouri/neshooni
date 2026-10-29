@props(['status'])

<span {{ $attributes->class(['chip ring-1 ring-inset', $status->badgeClass()]) }}>{{ $status->label() }}</span>
