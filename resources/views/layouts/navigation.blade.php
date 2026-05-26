@if(Auth::check())
    @if(Auth::user()->role === 'Super Admin')
        @include('layouts.navigation-super-admin')
    @elseif(Auth::user()->role === 'Admin Perbidang')
        @include('layouts.navigation-admin-perbidang')
    @elseif(Auth::user()->role === 'Kepala Dinas')
        @include('layouts.navigation-kepala-dinas')
    @else
        @include('layouts.navigation-user')
    @endif
@endif
