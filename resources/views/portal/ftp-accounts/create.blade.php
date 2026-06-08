@extends('layouts.portal')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        Add FTP Account
        <a href="{{ route('portal.dashboard') }}" class="btn btn-sm btn-secondary">Back</a>
    </h5>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.ftp-accounts.store') }}">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                           name="username" value="{{ old('username') }}" required>
                    <span class="input-group-text">@{{ $account->domain }}</span>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-text">The FTP username will be <strong>your-name@{{ $account->domain }}</strong></div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                       name="password" required autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="root_directory" class="form-label">Root Directory</label>
                <input id="root_directory" type="text" class="form-control @error('root_directory') is-invalid @enderror"
                       name="root_directory" value="{{ old('root_directory', '/') }}" required>
                <div class="form-text">Path relative to your web root (e.g. <code>/</code> or <code>/public</code>)</div>
                @error('root_directory')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create FTP Account</button>
        </form>
    </div>
</div>
@endsection
