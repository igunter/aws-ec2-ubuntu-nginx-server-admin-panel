@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="card">
    <h5 class="card-header">
        {{ __('Users') }}
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary float-end">Create User</a>
    </h5>

    <div class="card-body">
        <table id="users-table" class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                  class="d-inline delete-user-form"
                                  data-name="{{ $user->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        {{ $user->id === auth()->id() ? 'disabled title=Cannot delete your own account' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
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
    $('#users-table').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, searchable: false, targets: -1 },
            { className: 'text-end', targets: -1 }
        ]
    });

    document.addEventListener('submit', function (e) {
        if (!e.target.classList.contains('delete-user-form')) return;
        e.preventDefault();
        if (!confirm('Delete ' + e.target.dataset.name + '?')) return;
        showOverlay('Deleting…');
        e.target.submit();
    });
</script>
@endpush
