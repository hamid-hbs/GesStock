@props(['title', 'subtitle' => null, 'icon' => null, 'wide' => false])

<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-50" {{ $attributes->only('wire:click') }}></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full {{ $wide ? 'max-w-lg' : 'max-w-md' }} my-8">
            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-3">
                    @if($icon)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">{!! $icon !!}</span>
                    @endif
                    <div>
                        <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $title }}</h3>
                        @if($subtitle)<p class="text-xs text-slate-500">{{ $subtitle }}</p>@endif
                    </div>
                </div>
                {{ $close ?? '' }}
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
