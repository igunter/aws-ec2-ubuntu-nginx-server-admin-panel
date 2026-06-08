@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('FTP Accounts') }}
        <a href="{{ route('ftp-accounts.create') }}" class="btn btn-sm btn-primary float-end">Create FTP Account</a>
    </h5>

    <div class="card-body">
        <table id="accounts-table" class="table table-hover">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ftpAccounts as $ftpAccount)
                    <tr>
                        <td>{{ $ftpAccount->username }}</td>
                        <td>
                            <form action="{{ route('ftp-accounts.suspend', $ftpAccount) }}" method="POST" class="suspend-form">
                                @csrf
                                @method('PATCH')
                                <div class="form-check form-switch">
                                    <input class="form-check-input suspend-toggle" type="checkbox" role="switch"
                                           {{ $ftpAccount->is_active ? 'checked' : '' }}>
                                </div>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('ftp-accounts.show', $ftpAccount) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('ftp-accounts.edit', $ftpAccount) }}" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>

                            <form action="{{ route('ftp-accounts.destroy', $ftpAccount) }}" method="POST"
                                  class="d-inline delete-form"
                                  data-username="{{ $ftpAccount->username }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $('#accounts-table').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, searchable: false, targets: [1, -1] },
            { className: 'text-end', targets: -1 }
        ]
    });

    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete ' + form.dataset.username)) return;
            showOverlay('Deleting…');
            form.submit();
        });
    });

    document.querySelectorAll('.suspend-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var label = this.checked ? 'Activating…' : 'Suspending…';
            showOverlay(label);
            this.closest('.suspend-form').submit();
        });
    });
</script>
@endpush
