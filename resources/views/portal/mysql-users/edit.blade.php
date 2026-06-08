@extends('layouts.portal')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        Change Password
        <a href="{{ route('portal.mysql-databases.show', $mysqlUser->mysqlDatabase) }}" class="btn btn-sm btn-secondary">Back</a>
    </h5>
    <div class="card-body">
        <p class="text-muted mb-4">Updating password for <strong>{{ $mysqlUser->username }}</strong></p>

        <form method="POST" action="{{ route('portal.mysql-users.update', $mysqlUser) }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input id="password" type="text" class="form-control @error('password') is-invalid @enderror"
                       name="password" required autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>
@endsection
