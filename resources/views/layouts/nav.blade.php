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
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ url('/') }}">
                        <i class="fas fa-home text-primary"></i> Avaleht
                    </a>
                </li>
            </ul>

            {{-- Parem pool --}}
            <ul class="navbar-nav ms-auto">
                @guest
                    {{-- Pole sisse loginud --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Logi sisse
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus"></i> Registreeri
                        </a>
                    </li>
                @else
                    {{-- Sisse loginud --}}
                    @auth
                        {{-- hasAccess on Orchid osa --}}
                        @if(method_exists(auth()->user(), 'hasAccess') && auth()->user()->hasAccess('platform.index')) 
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/admin') }}">
                                    <i class="fas fa-tools"></i> Admin paneel
                                </a>
                            </li>
                        @endif
                    @endauth


                    <li class="nav-item">
                        <a class="nav-link"
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Logi välja
                        </a>

                        <form id="logout-form"
                            action="{{ route('logout') }}"
                            method="POST"
                            class="d-none">
                            @csrf
                        </form>
                    </li>
                @endguest

            </ul>
        </div>
    </div>
</nav>