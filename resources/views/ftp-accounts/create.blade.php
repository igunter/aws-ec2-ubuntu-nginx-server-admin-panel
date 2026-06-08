@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('FTP Accounts') }}
        <a href="{{ route('ftp-accounts.index') }}" class="btn btn-sm btn-primary float-end">List FTP Accounts</a>
    </h5>

    <div class="card-body">
        <form action="{{ route('ftp-accounts.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="domain" class="col-sm-2 col-form-label">Username</label>
                <div class="col input-group">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                    <select class="form-select" id="account_id" name="account_id">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ '@' . $account->domain }}</option>
                        @endforeach
                    </select>
                </div>
                @error('username')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="row mb-3">
                <label for="domain" class="col-sm-2 col-form-label">Password</label>
                <div class="col input-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="row mb-3">
                <label for="laravel" class="col-sm-2 col-form-label">Root Directory</label>
                <div class="col">
                    <select class="form-select" id="root_directory" name="root_directory">
                        <option>/</option>
                    </select>
                </div>
                @error('root_directory')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="row mb-3">
                <div class="col text-end offset-sm-2 text-sm-start">
                    <button type="submit" class="btn btn-primary">Create FTP Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    bindSlugAutofill('domain', 'slug');

    document.querySelector('form').addEventListener('submit', function () {
        var ssl = document.getElementById('ssl').checked;
        var laravel = document.getElementById('laravel').checked;
        var msg = 'Creating account…';
        if (laravel) msg = 'Creating account and installing Laravel…';
        else if (ssl) msg = 'Creating account and provisioning SSL…';
        showOverlay(msg);
    });
</script>
@endpush
