@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">{{ __('Dashboard') }}</div>

    <div class="card-body">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (session('pull_results'))
            <div class="mt-3">
                <h6 class="mb-2">Update Results</h6>
                @foreach (session('pull_results') as $result)
                    <div class="card mb-2">
                        <div class="card-header py-1 d-flex align-items-center gap-1 {{ $result['success'] ? 'text-success' : ($result['output'] === 'Skipped.' ? 'text-secondary' : 'text-danger') }}">
                            <i class="bi {{ $result['success'] ? 'bi-check-circle' : ($result['output'] === 'Skipped.' ? 'bi-dash-circle' : 'bi-x-circle') }}"></i>
                            <strong>{{ $result['label'] }}</strong>
                        </div>
                        @if ($result['output'])
                            <div class="card-body py-2 px-3">
                                <pre class="mb-0 small text-secondary" style="white-space: pre-wrap;">{{ $result['output'] }}</pre>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
