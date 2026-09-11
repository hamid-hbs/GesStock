@props(['tone' => 'slate'])

@php
$tones = [
    'emerald' => 'bg-emerald-100 text-emerald-700',
    'blue' => 'bg-blue-100 text-blue-700',
    'amber' => 'bg-amber-100 text-amber-800',
    'red' => 'bg-red-100 text-red-700',
    'slate' => 'bg-slate-100 text-slate-600',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full text-xs font-medium px-2.5 py-0.5 '.($tones[$tone] ?? $tones['slate'])]) }}>{{ $slot }}</span>
