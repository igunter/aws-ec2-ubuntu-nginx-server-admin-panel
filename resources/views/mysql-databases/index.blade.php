@extends('layouts.app')

@section('content')
<div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
        MySQL Databases
        <a href="{{ route('mysql-databases.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Add Database
        </a>
    </h5>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Database</th>
                    <th>Account</th>
                    <th>Users</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($databases as $db)
                    <tr>
                        <td class="align-middle">{{ $db->name }}</td>
                        <td class="align-middle">
                            <a href="{{ route('accounts.show', $db->account) }}">{{ $db->account->domain }}</a>
                        </td>
                        <td class="align-middle">{{ $db->mysqlUsers->count() }}</td>
                        <td class="align-middle text-end">
                            <a href="{{ route('mysql-databases.show', $db) }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-people"></i> Users
                            </a>
                            <form action="{{ route('mysql-databases.destroy', $db) }}" method="POST"
                                  class="d-inline delete-form" data-name="{{ $db->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No databases found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete database ' + form.dataset.name + ' and all its users?')) return;
            showOverlay('Deleting…');
            form.submit();
        });
    });
</script>
@endpush
