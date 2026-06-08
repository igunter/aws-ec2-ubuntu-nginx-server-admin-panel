@extends('layouts.portal')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        Create MySQL Database
        <a href="{{ route('portal.dashboard') }}" class="btn btn-sm btn-secondary">Back</a>
    </h5>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.mysql-databases.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Database Name</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name') }}" required placeholder="e.g. myapp">
                <div class="form-text">Lowercase letters, numbers and underscores only.</div>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create Database</button>
        </form>
    </div>
</div>
@endsection
