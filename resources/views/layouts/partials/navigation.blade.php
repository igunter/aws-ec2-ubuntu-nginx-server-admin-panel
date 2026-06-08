<ul class="nav flex-column p-3 p-md-0">
    <li class="nav-item">
        <a class="nav-link px-0 {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}" href="{{ route('home') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-0 {{ request()->routeIs('accounts.*') ? 'active fw-semibold' : '' }}" href="{{ route('accounts.index') }}">
            <i class="bi bi-people"></i> Accounts
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-0 {{ request()->routeIs('ftp-accounts.*') ? 'active fw-semibold' : '' }}" href="{{ route('ftp-accounts.index') }}">
            <i class="bi bi-hdd-network"></i> FTP Accounts
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-0 {{ request()->routeIs('users.*') ? 'active fw-semibold' : '' }}" href="{{ route('users.index') }}">
            <i class="bi bi-person-gear"></i> Users
        </a>
    </li>
</ul>

<hr class="my-3">

<p class="px-3 px-md-0 mb-1 text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Server</p>
<ul class="nav flex-column p-3 p-md-0">
    <li class="nav-item">
        <a class="nav-link px-0 {{ request()->routeIs('git.pull.show') ? 'active fw-semibold' : '' }}" href="{{ route('git.pull.show') }}">
            <i class="bi bi-github"></i> Pull Git Updates
        </a>
    </li>
</ul>