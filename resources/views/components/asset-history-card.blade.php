@props(['title', 'empty' => 'Belum ada riwayat.', 'hasItems' => true])

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">{{ $title }}</h3>
    <div class="space-y-3">
        @unless($hasItems)
            <div class="text-center text-xs text-slate-400 font-medium py-8 bg-slate-50 rounded-xl border border-slate-100">
                {{ $empty }}
            </div>
        @else
            {{ $slot }}
        @endunless
    </div>
</div>
