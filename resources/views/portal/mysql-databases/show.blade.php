@extends('layouts.portal')

@section('content')

<div class="card mb-4">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        {{ $mysqlDatabase->name }}
        <div>
            <a href="{{ route('portal.mysql-databases.users.create', $mysqlDatabase) }}" class="btn btn-sm btn-primary me-1">
                <i class="bi bi-plus-lg"></i> Add User
            </a>
            <form action="{{ route('portal.mysql-databases.destroy', $mysqlDatabase) }}" method="POST"
                  class="d-inline" id="delete-db-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash"></i> Delete Database
                </button>
            </form>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-sm btn-secondary ms-1">Back</a>
        </div>
    </h5>
</div>

<div class="card">
    <h5 class="card-header">Database Users</h5>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Host</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mysqlDatabase->mysqlUsers as $user)
                    <tr>
                        <td class="align-middle">{{ $user->username }}</td>
                        <td class="align-middle font-monospace">{{ $user->password }}</td>
                        <td class="align-middle text-muted">localhost</td>
                        <td class="align-middle text-end">
                            <button type="button" class="btn btn-sm btn-info text-white connection-details-btn"
                                    data-username="{{ $user->username }}"
                                    data-password="{{ $user->password }}"
                                    data-database="{{ $mysqlDatabase->name }}"
                                    data-bs-toggle="modal" data-bs-target="#connectionModal">
                                <i class="bi bi-plug"></i>
                            </button>
                            <a href="{{ route('portal.mysql-users.edit', $user) }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-key"></i>
                            </a>
                            <form action="{{ route('portal.mysql-users.destroy', $user) }}" method="POST"
                                  class="d-inline delete-form" data-username="{{ $user->username }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No users yet. Add one above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Connection details modal --}}
<div class="modal fade" id="connectionModal" tabindex="-1" aria-labelledby="connectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="connectionModalLabel">Connection Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted fw-normal w-25">Host</th>
                            <td class="font-monospace">localhost</td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary copy-btn" data-value="localhost"><i class="bi bi-copy"></i></button></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Port</th>
                            <td class="font-monospace">3306</td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary copy-btn" data-value="3306"><i class="bi bi-copy"></i></button></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Database</th>
                            <td class="font-monospace" id="modal-database"></td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary copy-btn" id="copy-database"><i class="bi bi-copy"></i></button></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Username</th>
                            <td class="font-monospace" id="modal-username"></td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary copy-btn" id="copy-username"><i class="bi bi-copy"></i></button></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Password</th>
                            <td class="font-monospace" id="modal-password"></td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary copy-btn" id="copy-password"><i class="bi bi-copy"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('delete-db-form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('Delete database {{ $mysqlDatabase->name }} and all its users? This cannot be undone.')) return;
        this.submit();
    });

    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete user ' + form.dataset.username + '?')) return;
            form.submit();
        });
    });

    document.querySelectorAll('.connection-details-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var db  = this.dataset.database;
            var usr = this.dataset.username;
            var pwd = this.dataset.password;

            document.getElementById('modal-database').textContent = db;
            document.getElementById('modal-username').textContent = usr;
            document.getElementById('modal-password').textContent = pwd;

            document.getElementById('copy-database').dataset.value = db;
            document.getElementById('copy-username').dataset.value = usr;
            document.getElementById('copy-password').dataset.value = pwd;
        });
    });

    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(this.dataset.value).then(() => {
                var icon = this.querySelector('i');
                icon.classList.replace('bi-copy', 'bi-check2');
                setTimeout(() => icon.classList.replace('bi-check2', 'bi-copy'), 1500);
            });
        });
    });
</script>
@endpush
