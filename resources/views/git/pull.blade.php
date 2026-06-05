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

<div id="pull-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center flex-column gap-3"
     style="background:rgba(0,0,0,0.6);z-index:9999;">
    <div class="spinner-border text-light" style="width:3rem;height:3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="text-white fw-semibold mb-0">Pulling updates&hellip;</p>
</div>

<script>
    (function () {
        var form = document.getElementById('git-pull-form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Pull latest changes from GitHub and run migrations?')) return;
            var overlay = document.getElementById('pull-overlay');
            overlay.classList.remove('d-none');
            overlay.classList.add('d-flex');
            form.submit();
        });
    })();
</script>
@endsection
