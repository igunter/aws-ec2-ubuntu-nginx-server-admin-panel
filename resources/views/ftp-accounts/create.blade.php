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
                        <option value="/" selected>/</option>
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
    function loadDirectories(accountId) {
        var select = document.getElementById('root_directory');
        select.innerHTML = '<option disabled>Loading…</option>';

        fetch('{{ route('ftp-accounts.directories') }}?account_id=' + encodeURIComponent(accountId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (dirs) {
            select.innerHTML = dirs.map(function (d) {
                return '<option value="' + d + '">' + d + '</option>';
            }).join('');
        })
        .catch(function () {
            select.innerHTML = '<option value="/">/</option>';
        });
    }

    var accountSelect = document.getElementById('account_id');
    if (accountSelect.value) loadDirectories(accountSelect.value);
    accountSelect.addEventListener('change', function () { loadDirectories(this.value); });

    document.querySelector('form').addEventListener('submit', function () {
        showOverlay('Creating FTP account…');
    });
</script>
@endpush
