@props([
    'title',
    'value',
    'trend',
    'href' => null,
    'type' => 'info' // Can be: 'info', 'success', 'warning', 'danger'
])

@php
    // Determine colors and icon based on card type
    switch ($type) {
        case 'success':
            $bgIcon = 'bg-[#EBFDF5]';
            $iconColor = 'text-[#10B981]';
            $trendClass = 'text-slate-400 text-[11px] font-medium';
            break;
            
        case 'danger':
            $bgIcon = 'bg-[#FFF1F2]';
            $iconColor = 'text-[#F43F5E]';
            $trendClass = 'text-[#F43F5E] text-[11px] font-semibold flex items-center space-x-1';
            break;

        case 'warning':
            $bgIcon = 'bg-amber-50';
            $iconColor = 'text-amber-500';
            $trendClass = 'text-amber-700 text-[11px] font-semibold flex items-center space-x-1';
            break;
            
        case 'info':
        default:
            $bgIcon = 'bg-[#EFF6FF]';
            $iconColor = 'text-[#3B82F6]';
            $trendClass = 'text-[#10B981] text-[11px] font-semibold flex items-center space-x-1';
            break;
    }

    $cardClasses = 'bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 flex items-center gap-3 sm:gap-4 transition-all duration-200 hover:shadow-md min-w-0';
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $cardClasses }} cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0F3092] focus:ring-offset-2">
@else
    <div class="{{ $cardClasses }}">
@endif
    <!-- Icon Container -->
    <div class="h-12 w-12 sm:h-16 sm:w-16 {{ $bgIcon }} rounded-2xl flex items-center justify-center flex-shrink-0">
        @if($type === 'success')
            <!-- Shield Check / Approved Icon -->
            <svg class="h-6 w-6 sm:h-8 sm:w-8 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        @elseif($type === 'danger')
            <!-- Alert/Exclamation Badge -->
            <svg class="h-6 w-6 sm:h-8 sm:w-8 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        @elseif($type === 'warning')
            <!-- Clock / Pending Icon -->
            <svg class="h-6 w-6 sm:h-8 sm:w-8 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @else
            <!-- Box / Total Aset Icon -->
            <svg class="h-6 w-6 sm:h-8 sm:w-8 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        @endif
    </div>

    <!-- Details -->
    <div class="flex flex-col min-w-0">
        <span class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider truncate">
            {{ $title }}
        </span>
        <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight my-0.5 sm:my-1">
            {{ $value }}
        </span>
        
        <!-- Trend/Subtext Rendering -->
        <span class="{{ $trendClass }} leading-snug">
            @if($type === 'info')
                <!-- Green Upward Trend Arrow -->
                <svg class="h-3 w-3 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span>{{ $trend }}</span>
            @elseif($type === 'danger')
                <!-- Red Attention/Warning Symbol -->
                <svg class="h-3.5 w-3.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $trend }}</span>
            @elseif($type === 'warning')
                <svg class="h-3.5 w-3.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                </svg>
                <span>{{ $trend }}</span>
            @else
                <!-- Success has neutral text in mockup -->
                <span>{{ $trend }}</span>
            @endif
        </span>
    </div>

@if($href)
    </a>
@else
    </div>
@endif
