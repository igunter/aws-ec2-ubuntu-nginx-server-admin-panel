@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Account Details') }}
        <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-primary ms-2 float-end">List Accounts</a>
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-primary ms-2 float-end">Edit Account</a>
    </h5>

    <div class="card-body">
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label fw-bold">Status</label>
            <div class="col">
                <form action="{{ route('accounts.suspend', $account) }}" method="POST" id="suspend-form">
                    @csrf
                    @method('PATCH')
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="suspend-toggle"
                               {{ $account->is_active ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="suspend-toggle">
                            {{ $account->is_active ? 'Active' : 'Suspended' }}
                        </label>
                    </div>
                </form>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label fw-bold">Domain</label>
            <div class="col">
                <p class="form-control-plaintext"><a href="https://{{ $account->domain }}" target="_blank">{{ $account->domain }}</a></p>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label fw-bold">Slug</label>
            <div class="col">
                <p class="form-control-plaintext">{{ $account->slug }}</p>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label fw-bold">Email</label>
            <div class="col">
                <p class="form-control-plaintext">{{ $account->email }}</p>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label fw-bold">SSL</label>
            <div class="col">
                <form action="{{ route('accounts.ssl.toggle', $account) }}" method="POST" id="ssl-form">
                    @csrf
                    @method('PATCH')
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="ssl-toggle"
                               {{ $account->ssl ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="ssl-toggle">Supplied by Let's Encrypt</label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('suspend-toggle').addEventListener('change', function () {
        showOverlay(this.checked ? 'Activating…' : 'Suspending…');
        document.getElementById('suspend-form').submit();
    });

    document.getElementById('ssl-toggle').addEventListener('change', function () {
        showOverlay(this.checked ? 'Enabling SSL…' : 'Disabling SSL…');
        document.getElementById('ssl-form').submit();
    });
</script>
@endpush
