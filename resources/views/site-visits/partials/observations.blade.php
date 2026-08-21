<div class="card mb-3">
    <div class="card-header">
        Observations

        @if ($visit->status === 'draft')
            <a href="{{ route('site-visits.observations.create', $visit) }}" class="btn btn-sm btn-primary float-end">
                Add Observation
            </a>
        @endif
    </div>

    <div class="card-body">
        @forelse($visit->observations as $obs)
            <div class="border p-2 mb-2">
                <strong>{{ $obs->category }}</strong>
                <span class="badge bg-warning">{{ ucfirst($obs->severity) }}</span>
                <p>{{ $obs->description }}</p>
            </div>
        @empty
            <p>No observations added.</p>
        @endforelse
    </div>
</div>
