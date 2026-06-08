@extends('layouts.portal')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        Add Database User
        <a href="{{ route('portal.mysql-databases.show', $mysqlDatabase) }}" class="btn btn-sm btn-secondary">Back</a>
    </h5>
    <div class="card-body">
        <p class="text-muted mb-4">Adding user to <strong>{{ $mysqlDatabase->name }}</strong></p>

        <form method="POST" action="{{ route('portal.mysql-databases.users.store', $mysqlDatabase) }}">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                       name="username" value="{{ old('username') }}" required placeholder="e.g. appuser">
                <div class="form-text">Lowercase letters, numbers and underscores only.</div>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="text" class="form-control @error('password') is-invalid @enderror"
                       name="password" required autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>
</div>
@endsection
