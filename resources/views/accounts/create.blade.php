@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Accounts') }}
        <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-primary float-end">List Accounts</a>
    </h5>

    <div class="card-body">
        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="domain" class="col-sm-2 col-form-label">Domain</label>
                <div class="col input-group">
                    <span class="input-group-text">https://</span>
                    <input type="text" class="form-control" id="domain" name="domain" placeholder="Account Domain">
                </div>
            </div>
            <div class="row mb-3">
                <label for="slug" class="col-sm-2 col-form-label">Slug</label>
                <div class="col">
                    <input type="text" class="form-control" id="slug" name="slug" placeholder="account-slug">
                </div>
            </div>
            <div class="row mb-3">
                <label for="email" class="col-sm-2 col-form-label">Email</label>
                <div class="col">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address">
                </div>
            </div>
            <div class="row mb-3">
                <label for="ssl" class="col-sm-2 col-form-label">SSL</label>
                <div class="col">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="ssl" name="ssl" {{ old('ssl') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="ssl">Supplied by Let's Encrypt</label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <label for="laravel" class="col-sm-2 col-form-label">Laravel</label>
                <div class="col">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="laravel" name="laravel" {{ old('laravel') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="laravel">Create a new Laravel Application</label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col text-end offset-sm-2 text-sm-start">
                    <button type="submit" class="btn btn-primary">Create Account</button>
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
