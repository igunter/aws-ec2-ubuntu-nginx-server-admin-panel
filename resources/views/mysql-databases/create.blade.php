@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        Create MySQL Database
        <a href="{{ route('mysql-databases.index') }}" class="btn btn-sm btn-secondary">Back</a>
    </h5>
    <div class="card-body">
        <form action="{{ route('mysql-databases.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="account_id" class="col-sm-2 col-form-label">Account</label>
                <div class="col">
                    <select class="form-select @error('account_id') is-invalid @enderror" id="account_id" name="account_id" required>
                        <option value="">— Select account —</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->domain }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mb-3">
                <label for="name" class="col-sm-2 col-form-label">Database Name</label>
                <div class="col">
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}"
                           placeholder="e.g. myapp" required>
                    <div class="form-text">Lowercase letters, numbers and underscores only. Will be prefixed with the account slug.</div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mb-3">
                <div class="col offset-sm-2">
                    <button type="submit" class="btn btn-primary">Create Database</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
