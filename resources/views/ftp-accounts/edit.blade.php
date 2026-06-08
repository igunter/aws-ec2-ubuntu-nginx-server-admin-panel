@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Edit Account') }}
        <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-primary ms-2 float-end">List Accounts</a>
        <a href="{{ route('accounts.show', $account) }}" class="btn btn-sm btn-primary ms-2 float-end">Account Details</a>
    </h5>

    <div class="card-body">
        <form action="{{ route('accounts.update', $account->id) }}" method="POST">
            @method('PATCH')
            @csrf
            <div class="row mb-3">
                <label for="domain" class="col-sm-2 col-form-label">Domain</label>
                <div class="col input-group">
                    <span class="input-group-text">https://</span>
                    <input type="text" class="form-control" id="domain" name="domain" placeholder="Account Domain" value="{{ old('domain', $account->domain) }}">
                </div>
            </div>
            <div class="row mb-3">
                <label for="slug" class="col-sm-2 col-form-label">Slug</label>
                <div class="col">
                    <input type="text" class="form-control" id="slug" name="slug" placeholder="account-slug" value="{{ old('slug', $account->slug) }}">
                </div>
            </div>
            <div class="row mb-3">
                <label for="email" class="col-sm-2 col-form-label">Email</label>
                <div class="col">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="{{ old('email', $account->email) }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col text-end offset-sm-2 text-sm-start">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
        showOverlay('Saving changes…');
    });
</script>
@endpush
