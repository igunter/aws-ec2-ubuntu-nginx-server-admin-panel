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
</script>
@endpush
