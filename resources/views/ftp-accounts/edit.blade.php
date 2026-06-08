@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Edit FTP Account') }}
        <a href="{{ route('ftp-accounts.index') }}" class="btn btn-sm btn-primary float-end">List FTP Accounts</a>
    </h5>

    <div class="card-body">
        <form action="{{ route('ftp-accounts.update', $ftpAccount) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Username</label>
                <div class="col input-group">
                    <input type="text" class="form-control" value="{{ $ftpAccount->username }}" disabled>
                </div>
            </div>
            <div class="row mb-3">
                <label for="password" class="col-sm-2 col-form-label">Password</label>
                <div class="col">
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" placeholder="Leave blank to keep current password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mb-3">
                <label for="root_directory" class="col-sm-2 col-form-label">Root Directory</label>
                <div class="col">
                    <select class="form-select @error('root_directory') is-invalid @enderror"
                            id="root_directory" name="root_directory">
                        <option value="{{ $ftpAccount->root_directory }}" selected>{{ $ftpAccount->root_directory }}</option>
                    </select>
                    @error('root_directory')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Active</label>
                <div class="col">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_active" name="is_active" value="1"
                               {{ $ftpAccount->is_active ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col offset-sm-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var selected = '{{ $ftpAccount->root_directory }}';

        fetch('{{ route('ftp-accounts.directories') }}?account_id={{ $ftpAccount->account_id }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (dirs) {
            var select = document.getElementById('root_directory');
            select.innerHTML = dirs.map(function (d) {
                return '<option value="' + d + '"' + (d === selected ? ' selected' : '') + '>' + d + '</option>';
            }).join('');
        })
        .catch(function () {});

        document.querySelector('form').addEventListener('submit', function () {
            showOverlay('Saving changes…');
        });
    })();
</script>
@endpush
