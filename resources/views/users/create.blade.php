@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Create User') }}
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary float-end">List Users</a>
    </h5>

    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="name" class="col-sm-2 col-form-label">Name</label>
                <div class="col">
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <label for="email" class="col-sm-2 col-form-label">Email</label>
                <div class="col">
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <label for="password" class="col-sm-2 col-form-label">Password</label>
                <div class="col">
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row mb-3">
                <label for="password_confirmation" class="col-sm-2 col-form-label">Confirm Password</label>
                <div class="col">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>
            </div>
            <div class="row">
                <div class="col offset-sm-2">
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
