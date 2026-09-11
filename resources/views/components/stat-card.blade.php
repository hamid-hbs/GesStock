@props(['label', 'value', 'color' => 'emerald', 'icon' => null, 'trend' => null, 'suffix' => null])

@php
$colors = [
    'emerald' => 'bg-emerald-100 text-emerald-700',
    'blue' => 'bg-blue-100 text-blue-700',
    'indigo' => 'bg-indigo-100 text-indigo-700',
    'amber' => 'bg-amber-100 text-amber-700',
    'red' => 'bg-red-100 text-red-700',
    'slate' => 'bg-slate-100 text-slate-600',
];
$badge = $colors[$color] ?? $colors['slate'];
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-2">
        <div class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $label }}</div>
        @if($icon)
        <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $badge }}">{!! $icon !!}</span>
        @endif
    </div>
    <div class="text-2xl font-bold text-slate-800 tabular-nums">{{ $value }}@if($suffix) <span class="text-sm font-medium text-slate-400">{{ $suffix }}</span>@endif</div>
    @if($trend !== null)
    <div class="mt-1 text-xs font-medium {{ str_starts_with($trend, '-') ? 'text-red-600' : (str_starts_with($trend, '+') ? 'text-emerald-600' : 'text-slate-400') }}">
        {{ $trend }} <span class="font-normal text-slate-400">vs période précédente</span>
    </div>
    @endif
</div>
