@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

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
</script>
@endpush
