@extends('layouts.portal')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        Edit FTP Account
        <a href="{{ route('portal.dashboard') }}" class="btn btn-sm btn-secondary">Back</a>
    </h5>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.ftp-accounts.update', $ftpAccount) }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="{{ $ftpAccount->username }}" disabled>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                       name="password" autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="root_directory" class="form-label">Root Directory</label>
                <input id="root_directory" type="text" class="form-control @error('root_directory') is-invalid @enderror"
                       name="root_directory" value="{{ old('root_directory', $ftpAccount->root_directory) }}" required>
                <div class="form-text">Path relative to your web root (e.g. <code>/</code> or <code>/public</code>)</div>
                @error('root_directory')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                           name="is_active" value="1" {{ $ftpAccount->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
@endsection
