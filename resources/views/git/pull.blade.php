@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>Pull Updates</span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">Fetches the latest code from GitHub, resets to <code>origin/main</code>, runs <code>composer install</code>, and applies any pending database migrations.</p>

        <div class="my-3 text-center">
            <form id="git-pull-form" action="{{ route('git.pull') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-cloud-download"></i> Pull Now
                </button>
            </form>
        </div>

        @if (session('pull_results'))
            <div class="mt-3">
                <h6 class="mb-2">Results</h6>
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
