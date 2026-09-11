@props(['tone' => 'success'])

@php
$tones = [
    'success' => ['box' => 'bg-emerald-50 border-emerald-200 border-l-emerald-500 text-emerald-800', 'icon' => 'text-emerald-500', 'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    'error' => ['box' => 'bg-red-50 border-red-200 border-l-red-500 text-red-800', 'icon' => 'text-red-500', 'path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    'info' => ['box' => 'bg-blue-50 border-blue-200 border-l-blue-500 text-blue-800', 'icon' => 'text-blue-500', 'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];
$t = $tones[$tone] ?? $tones['success'];
@endphp

<div class="flex items-start gap-3 border border-l-4 rounded-lg shadow-sm text-sm px-4 py-3 mb-6 {{ $t['box'] }}">
    <svg class="h-5 w-5 shrink-0 {{ $t['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['path'] }}"/></svg>
    <span>{{ $slot }}</span>
</div>
