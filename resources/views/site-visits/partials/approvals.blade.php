<div class="card">
    <div class="card-header">Approval</div>
    <div class="card-body">

        @foreach ($visit->approvals as $approval)
            <p>
                {{ $approval->reviewer->name }} —
                <strong>{{ ucfirst($approval->status) }}</strong><br>
                {{ $approval->remarks }}
            </p>
        @endforeach

        @if ($visit->status === 'submitted')
            <form method="POST" action="{{ route('site-visits.approve', $visit) }}">
                @csrf

                <select name="status" class="form-control mb-2">
                    <option value="approved">Approve</option>
                    <option value="rejected">Reject</option>
                </select>

                <textarea name="remarks" class="form-control mb-2"></textarea>

                <button class="btn btn-success">Submit Decision</button>
            </form>
        @endif
    </div>
</div>
