<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>📋 Consolidated Site Visit Evaluation Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #1256A0;
            margin-bottom: 20px;
        }

        h4 {
            color: #0a5fa8;
            border-bottom: 2px solid #1256A0;
            padding-bottom: 3px;
            margin-top: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f4f6f8;
        }

        .score {
            text-align: center;
            font-weight: bold;
            color: #0a7c3c;
        }

        .muted {
            color: #888;
        }

        .summary {
            background: #1256A0;
            color: white;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            margin-top: 25px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #777;
            margin-top: 40px;
        }

        .criteria-table {
            margin-bottom: 15px;
        }

        .section-comment {
            background: #f9f9f9;
            border-left: 4px solid #1256A0;
            padding: 6px 10px;
            margin-top: 8px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <h2>📋 Consolidated Site Visit Evaluation Report</h2>

    @foreach ($evaluations as $index => $eval)
        <h4>{{ $index + 1 }}. {{ $eval->consortium->think_tank_name ?? 'N/A' }}</h4>

        <p>
            <strong>Team:</strong> {{ $eval->team->name ?? 'N/A' }} <br>
            <strong>Leader:</strong> {{ $eval->leader->name ?? 'N/A' }} <br>
            <strong>Team Members:</strong>
            @if ($eval->team && $eval->team->members && $eval->team->members->count() > 0)
                {{ $eval->team->members->map(fn($m) => $m->user->name)->implode(', ') }}
            @else
                <span class="muted">—</span>
            @endif
            <br>
            <strong>Date:</strong>
            {{ $eval->evaluation_date ? \Carbon\Carbon::parse($eval->evaluation_date)->format('M d, Y') : '—' }}
        </p>

        {{-- Repeatable Section Template --}}
        @php
            $sections = [
                1 => ['title' => 'Organizational Capacity', 'max' => 10, 'subs' => 4],
                2 => ['title' => 'Technical Capability', 'max' => 5, 'subs' => 3],
                3 => ['title' => 'Partnerships & Collaboration', 'max' => 5, 'subs' => 3],
                4 => ['title' => 'Innovation & Impact', 'max' => 5, 'subs' => 3],
                5 => ['title' => 'Sustainability', 'max' => 5, 'subs' => 3],
                6 => ['title' => 'Facility & Resource Adequacy', 'max' => 5, 'subs' => 3],
            ];
        @endphp

        @foreach ($sections as $s => $meta)
            <h4>{{ $s }}. {{ $meta['title'] }} ({{ $meta['max'] }} points)</h4>
            <table class="criteria-table">
                <thead>
                    <tr>
                        <th width="35%">Criterion</th>
                        <th width="8%">Score</th>
                        <th>Strength</th>
                        <th>Weakness</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= $meta['subs']; $i++)
                        @php
                            $score = "s{$s}_{$i}_score";
                            $str = "s{$s}_{$i}_strength";
                            $weak = "s{$s}_{$i}_weakness";
                        @endphp
                        @if (!empty($eval->$score) || !empty($eval->$str) || !empty($eval->$weak))
                            <tr>
                                <td>{{ $s }}.{{ $i }}</td>
                                <td class="score">{{ $eval->$score ?? '' }}</td>
                                <td>{{ $eval->$str ?? '' }}</td>
                                <td>{{ $eval->$weak ?? '' }}</td>
                            </tr>
                        @endif
                    @endfor
                </tbody>
            </table>

            @php $commentField = "s{$s}_comments"; @endphp
            @if (!empty($eval->$commentField))
                <div class="section-comment">
                    <strong>Evaluator’s Comment:</strong> {{ $eval->$commentField }}
                </div>
            @endif
        @endforeach

        {{-- General + Summary --}}
        @if (!empty($eval->general_observations))
            <p><strong>General Observations:</strong> {{ $eval->general_observations }}</p>
        @endif

        <div class="summary">
            Total Score: {{ $eval->total_score ?? 0 }}/35
        </div>

        <p><strong>Overall Strength:</strong> {{ $eval->overall_strength ?? '—' }}</p>
        <p><strong>Overall Weakness:</strong> {{ $eval->overall_weakness ?? '—' }}</p>
        <p><strong>Additional Comments:</strong> {{ $eval->additional_comments ?? '—' }}</p>
        <p><strong>Evaluator:</strong> {{ $eval->evaluator_name ?? '—' }}
            | <strong>Signature:</strong> {{ $eval->evaluator_signature ?? '—' }}
        </p>

        <hr style="margin:30px 0; border:0; border-top:2px dashed #aaa;">
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('F d, Y') }} — Africa Think Tank Evaluation System
    </div>
</body>

</html>
