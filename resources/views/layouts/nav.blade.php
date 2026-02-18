<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fas fa-book"></i> ToDo ja Blog
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            {{-- Vasak pool --}}
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">
                        Avaleht
                    </a>
                </li>
            </ul>

            {{-- Parem pool --}}
            <ul class="navbar-nav ms-auto">

                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                Logi sisse
                            </a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                Registreeru
                            </a>
                        </li>
                    @endif
                @else
                    {{-- Admin link (ainult administraatorile) --}}
                    @if(method_exists(auth()->user(), 'hasAccess') && auth()->user()->hasAccess('platform.index')) 
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/admin') }}">
                                <i class="fas fa-tools"></i> Admin paneel
                            </a>
                        </li>
                    @endif

                    {{-- NOTIFICATIONS (Variant 2) --}}
                    @php
                        $unreadCount = auth()->user()->unreadNotifications()->count();
                        $unread = auth()->user()->unreadNotifications()->latest()->take(10)->get();
                    @endphp

                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle position-relative"
                           href="#"
                           id="notifDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="fas fa-bell"></i>

                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 320px;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Teavitused</span>

                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button class="btn btn-sm btn-link text-decoration-none" type="submit">
                                            Märgi kõik loetuks
                                        </button>
                                    </form>
                                @endif
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            @forelse($unread as $n)
                                <li>
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="px-3 py-2">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-start p-0 text-decoration-none w-100">
                                            <div class="fw-semibold">{{ data_get($n->data, 'title', 'Teavitus') }}</div>
                                            <div class="small text-muted">{{ data_get($n->data, 'message', '') }}</div>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="px-3 py-2 text-muted">Uusi teavitusi pole.</li>
                            @endforelse
                        </ul>
                    </li>

                    {{-- Kasutaja dropdown --}}
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown"
                           class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-haspopup="true"
                           aria-expanded="false"
                           v-pre>
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ url('home') }}">
                                Minu avaleht
                            </a>

                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logi välja
                            </a>

                            <form id="logout-form"
                                action="{{ route('logout') }}"
                                method="POST"
                                class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest

            </ul>
        </div>
    </div>
</nav>
