@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        <ul class="navbar-nav ml-auto">
    {{-- Lien personnalisé Alertes RDV --}}
   

<li class="nav-item dropdown" id="notifications-dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    @if (auth()->user()->unreadNotifications->count())
                        <span id="notif-count" class="badge badge-warning navbar-badge">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header">
                        <span id="notif-header-count">{{ auth()->user()->unreadNotifications->count() }}</span>
                        Notifications
                    </span>
                    <div class="dropdown-divider"></div>

                    <div id="notif-list">
                        @forelse(auth()->user()->unreadNotifications as $notif)
                            <a href="{{ route('dossiers.show', $notif->data['dossier_id']) }}" class="dropdown-item"
                                onclick="event.preventDefault(); document.getElementById('notif-{{ $notif->id }}').submit();">
                                <i class="fas fa-calendar-check mr-2"></i>
                                {{ $notif->data['message'] }}
                                <span class="float-right text-muted text-sm">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                            </a>
                            <form id="notif-{{ $notif->id }}" action="{{ route('notifications.read', $notif->id) }}"
                                method="POST" style="display: none;">
                                @csrf
                            </form>
                            <div class="dropdown-divider"></div>
                        @empty
                            <span class="dropdown-item text-center text-muted">Aucune nouvelle notification</span>
                        @endforelse
                    </div>

                    <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">
                        Voir toutes les notifications
                    </a>
                </div>
            </li>

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
