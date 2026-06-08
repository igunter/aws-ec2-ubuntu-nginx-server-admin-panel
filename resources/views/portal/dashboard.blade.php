@extends('layouts.portal')

@section('content')

<div class="card mb-4">
    <h5 class="card-header">Account Details</h5>
    <div class="card-body">
        <div class="row mb-2">
            <label class="col-sm-3 col-form-label fw-bold">Domain</label>
            <div class="col">
                <p class="form-control-plaintext">
                    <a href="http{{ $account->ssl ? 's' : '' }}://{{ $account->domain }}" target="_blank">
                        {{ $account->domain }}
                    </a>
                </p>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-3 col-form-label fw-bold">Email</label>
            <div class="col">
                <p class="form-control-plaintext">{{ $account->email }}</p>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-3 col-form-label fw-bold">Status</label>
            <div class="col">
                <p class="form-control-plaintext">
                    @if ($account->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Suspended</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-3 col-form-label fw-bold">SSL</label>
            <div class="col">
                <p class="form-control-plaintext">
                    @if ($account->ssl)
                        <span class="badge bg-success">Enabled</span>
                    @else
                        <span class="badge bg-secondary">Disabled</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        FTP Accounts
        <a href="{{ route('portal.ftp-accounts.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Add FTP Account
        </a>
    </h5>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Root Directory</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($account->ftpAccounts as $ftp)
                    <tr>
                        <td class="align-middle">{{ $ftp->username }}</td>
                        <td class="align-middle">{{ $ftp->root_directory }}</td>
                        <td class="align-middle">
                            <form action="{{ route('portal.ftp-accounts.suspend', $ftp) }}" method="POST" class="suspend-form">
                                @csrf
                                @method('PATCH')
                                <div class="form-check form-switch">
                                    <input class="form-check-input suspend-toggle" type="checkbox" role="switch"
                                           {{ $ftp->is_active ? 'checked' : '' }}>
                                </div>
                            </form>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('portal.ftp-accounts.edit', $ftp) }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if ($ftp->id !== $ftpAccount->id)
                                <form action="{{ route('portal.ftp-accounts.destroy', $ftp) }}" method="POST"
                                      class="d-inline delete-form" data-username="{{ $ftp->username }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No FTP accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.suspend-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            this.closest('.suspend-form').submit();
        });
    });

    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete ' + form.dataset.username + '?')) return;
            form.submit();
        });
    });
</script>
@endpush
