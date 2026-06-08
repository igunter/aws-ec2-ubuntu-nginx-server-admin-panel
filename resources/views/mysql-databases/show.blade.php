@extends('layouts.app')

@section('content')
<div class="card mb-4">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        {{ $mysqlDatabase->name }}
        <div>
            <a href="{{ route('accounts.show', $mysqlDatabase->account) }}" class="btn btn-sm btn-secondary me-1">
                {{ $mysqlDatabase->account->domain }}
            </a>
            <a href="{{ route('mysql-databases.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </h5>
</div>

<div class="card mb-4">
    <h5 class="card-header">Add User</h5>
    <div class="card-body">
        <form action="{{ route('mysql-databases.users.store', $mysqlDatabase) }}" method="POST">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                           id="username" name="username" value="{{ old('username') }}"
                           placeholder="e.g. appuser">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col">
                    <label for="password" class="form-label">Password</label>
                    <input type="text" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" value="{{ old('password') }}">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <h5 class="card-header">Users</h5>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mysqlDatabase->mysqlUsers as $user)
                    <tr>
                        <td class="align-middle">{{ $user->username }}</td>
                        <td class="align-middle font-monospace">{{ $user->password }}</td>
                        <td class="align-middle text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="align-middle text-end">
                            <form action="{{ route('mysql-databases.users.destroy', [$mysqlDatabase, $user]) }}" method="POST"
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
                        <td colspan="4" class="text-center text-muted py-3">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete user ' + form.dataset.username + '?')) return;
            form.submit();
        });
    });
</script>
@endpush
