<ul class="nav flex-column p-3 p-md-0">
    <li class="nav-item">dfsdsd</li>
    <li class="nav-item">dfsdsd</li>
    <li class="nav-item">dfsdsd</li>
</ul>

<hr class="my-2">

<p class="px-3 px-md-0 mb-1 text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Server</p>
<ul class="nav flex-column p-3 p-md-0">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('git.pull.show') ? 'active fw-semibold' : '' }}" href="{{ route('git.pull.show') }}">
            <i class="bi bi-cloud-download"></i> Pull Updates
        </a>
    </li>
</ul>