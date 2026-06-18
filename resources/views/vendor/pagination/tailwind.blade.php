@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-end">
        <div class="inline-flex items-center overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 items-center justify-center border-r border-blue-100 text-slate-300 cursor-not-allowed" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-9 w-9 items-center justify-center border-r border-blue-100 text-[#0F3092] transition-colors hover:bg-blue-50" aria-label="Halaman sebelumnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden h-9 min-w-9 items-center justify-center border-r border-blue-100 px-3 text-xs font-semibold text-slate-400 sm:inline-flex" aria-disabled="true">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex h-9 min-w-9 items-center justify-center border-r border-blue-100 bg-[#0F3092] px-3 text-xs font-bold text-white" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="hidden h-9 min-w-9 items-center justify-center border-r border-blue-100 px-3 text-xs font-bold text-slate-600 transition-colors hover:bg-blue-50 hover:text-[#0F3092] sm:inline-flex" aria-label="Ke halaman {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-9 w-9 items-center justify-center text-[#0F3092] transition-colors hover:bg-blue-50" aria-label="Halaman berikutnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="inline-flex h-9 w-9 items-center justify-center text-slate-300 cursor-not-allowed" aria-disabled="true" aria-label="Halaman berikutnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
