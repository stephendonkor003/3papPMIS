<div class="card mb-3">
    <div class="card-header">Assignment</div>
    <div class="card-body">

        @if ($visit->assignment_type === 'individual')
            <p>
                Assigned To:
                {{ optional($visit->assignment->user)->name }}
            </p>
        @else
            <p><strong>Group:</strong> {{ $visit->group->group_name }}</p>
            <p><strong>Leader:</strong> {{ $visit->group->leader->name }}</p>

            <ul>
                @foreach ($visit->group->members as $member)
                    <li>{{ $member->user->name }} ({{ $member->role }})</li>
                @endforeach
            </ul>
        @endif

    </div>
</div>
