<nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fas fa-gavel"></i> {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

                <li class="nav-item">
                    <a class="nav-link" href="{{route("categories")}}">Categorieën</a>
                </li>

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">

                <li class="nav-item mr-3">
                    <form method="GET" action="{{ route('zoeken') }}">
{{--                        @csrf--}}
                        <div class="input-group">
                            <input value="@if(isset($_GET['search'])){{$_GET['search']}}@endif" name="search" type="text" class="form-control" placeholder="Zoeken...">
                            <button id="btn-search" type="submit" class="btn btn-light"> <i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </li>
                <!-- Authentication Links -->
                @if (!Session::has('user'))

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Inloggen</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">Registreren</a>
                </li>

                @else

                <li class="nav-item">
                    <a class="nav-link bi bi-envelope-fill" href="{{route('messages')}}"><i class="fa fa-fw fa-envelope"></i> Berichten</a>
                </li>

                <li class="nav-item dropdown">

                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fa fa-fw fa-user"></i>
                        Welkom <span class="fw-bold">{{ Session::get('user')->username }}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                        <a class="dropdown-item" href="{{route("veilingen.gewonnen")}}">
                            Gewonnen veilingen
                        </a>
                        <a class="dropdown-item" href="{{route("veilingen.geboden")}}">
                            Geboden veilingen
                        </a>
                        @if (Session::get('user')->is_seller)
                        <a class="dropdown-item" href="{{route("veilingen.mijn")}}">
                            Mijn veilingen
                        </a>
                        <a class="dropdown-item" href="{{route("beoordeling.overzicht")}}">
                            Beoordelingen
                        </a>
                        <a class="dropdown-item" href="{{ route('veilingmaken') }}">
                            Veiling aanmaken
                        </a>
                        @endif

                        <a class="dropdown-item" href="{{route("mijnaccount")}}">
                            Mijn account
                        </a>

                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Uitloggen
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>

                @endif
            </ul>

        </div>
    </div>
</nav>

@if (!Cookie::has('cookie_allow'))
    @include('includes.cookie');
@endif
