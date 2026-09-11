@props(['message' => 'Rien à afficher pour le moment.'])

<div class="bg-white rounded-xl border border-dashed border-slate-300 px-6 py-10 text-center">
    <p class="text-sm text-slate-400 mb-3">{{ $message }}</p>
    {{ $slot }}
</div>
