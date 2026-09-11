@props(['title', 'subtitle' => null])

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
        @if($subtitle)<p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>@endif
    </div>
    <div class="flex items-center gap-2">
        {{ $slot }}
    </div>
</div>
