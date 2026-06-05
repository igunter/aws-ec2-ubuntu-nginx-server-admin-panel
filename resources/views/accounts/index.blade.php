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
                    <th>Slug</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $account)
                    <tr>
                        <td>{{ $account->domain }}</td>
                        <td>{{ $account->slug }}</td>
                        <td>{{ $account->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <a href="{{ route('accounts.show', $account) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#accounts-table').DataTable({
        responsive: true,
        order: [[ 0, "asc" ]],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: 'lBfrtip',
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: -1 },
            { searchable: false, targets: -1 },
            { className: 'text-end', targets: -1 }
        ]
    });
</script>
@endpush
