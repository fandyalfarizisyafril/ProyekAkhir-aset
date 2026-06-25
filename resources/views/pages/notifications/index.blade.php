<x-app-layout>
    @php
        $toneClasses = [
            'success' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'warning' => 'bg-amber-50 text-amber-700 border-amber-100',
            'danger' => 'bg-rose-50 text-rose-700 border-rose-100',
            'info' => 'bg-blue-50 text-blue-700 border-blue-100',
        ];
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Notifikasi</h2>
            <p class="text-sm text-slate-500 mt-1">Pusat informasi aktivitas dan status terbaru sistem.</p>
        </div>

        @if(auth()->user()->unreadNotifications()->exists())
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="bg-white border border-slate-200 text-[#0F3092] hover:bg-blue-50 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl shadow-sm">
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-emerald-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $tone = $data['tone'] ?? 'info';
                $classes = $toneClasses[$tone] ?? $toneClasses['info'];
            @endphp
            <div class="p-5 border-b border-slate-100 last:border-b-0 flex flex-col sm:flex-row sm:items-center gap-4 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/30' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wider {{ $classes }}">
                            {{ $data['category'] ?? 'system' }}
                        </span>
                        @if(! $notification->read_at)
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#0F3092]">Baru</span>
                        @endif
                    </div>
                    <h3 class="mt-3 text-sm font-bold text-slate-800">{{ $data['title'] ?? 'Notifikasi Sistem' }}</h3>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $data['message'] ?? '-' }}</p>
                    <p class="mt-2 text-[11px] font-semibold text-slate-400">{{ $notification->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p>
                </div>

                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="sm:flex-shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl shadow-sm">
                        {{ ($data['url'] ?? null) ? 'Buka' : 'Tandai Dibaca' }}
                    </button>
                </form>
            </div>
        @empty
            <div class="p-10 text-center text-sm text-slate-400 font-medium">
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</x-app-layout>
