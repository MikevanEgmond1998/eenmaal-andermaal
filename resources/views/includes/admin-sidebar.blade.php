<div class="sidebar">
    <div class="hamburgermenu">
        <div id="mySidepanel" class="sidepanel">
            <div id="closebtn" class="closebtn">&times;</div>
            <div class="text-center mb-3 fw-bold title">EenmaalAndermaal</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('Admin.Index') }}"><i class="fas fa-fw fa-chart-line"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.users.list') }}"><i class="fas fa-fw fa-users"></i>Gebruikers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.auctions.list') }}"><i class="fas fa-fw fa-gavel"></i>Veilingen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('admin.statistics')}}"><i class="fas fa-fw fa-chart-bar"></i>Statistieken</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/categories"><i class="fas fa-fw fa-list-alt"></i>Categorieën</a>
                </li>
            </ul>

            <div class="bottom-info">
                <ul class="nav flex-column ">
                    <li class="nav-item">
                        <i>U bent ingelogd als:</i>
                    </li>
                    <li class="nav-item">
                        <b>"admin"</b>
                    </li>
                    <li class="nav-item">
                        <a class="nav-item" href="{{ route('Admin.logout') }}"
                        onclick="event.preventDefault();
                                              document.getElementById('logout-form').submit();">
                         Uitloggen
                     </a>

                     <form id="logout-form" action="{{ route('Admin.logout') }}" method="POST" class="d-none">
                         @csrf
                     </form>
                    </li>
                </ul>
            </div>
        </div>

        <button id="openbtn" class="openbtn">&#9776;</button>
    </div>
</div>



