@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Accounts') }}
        <a href="{{ route('accounts.create') }}" class="btn btn-sm btn-primary float-end">Create Account</a>
    </h5>

    <div class="card-body">
        <table id="accounts-table" class="table table-hover">
            <thead>
                <tr>
                    <th>Domain</th>
                    <th class="d-none d-md-table-cell">Slug</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $account)
                    <tr>
                        <td>{{ $account->domain }}</td>
                        <td class="d-none d-md-table-cell">{{ $account->slug }}</td>
                        <td>
                            <form action="{{ route('accounts.suspend', $account) }}" method="POST" class="suspend-form">
                                @csrf
                                @method('PATCH')
                                <div class="form-check form-switch">
                                    <input class="form-check-input suspend-toggle" type="checkbox" role="switch"
                                           {{ $account->is_active ? 'checked' : '' }}>
                                </div>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('accounts.show', $account) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
                            
                            <form action="{{ route('accounts.destroy', $account) }}" method="POST"
                                  class="d-inline delete-form"
                                  data-domain="{{ $account->domain }}">
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
            { orderable: false, searchable: false, targets: [2, -1] },
            { className: 'text-end', targets: -1 }
        ]
    });

    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete ' + form.dataset.domain + ' and remove all server files?')) return;
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
