<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Site Visit Evaluation Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        h2,
        h4 {
            color: #1256A0;
            margin-bottom: 5px;
        }

        .section {
            margin-bottom: 15px;
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
            background-color: #f5f5f5;
        }

        .score {
            text-align: center;
            font-weight: bold;
            color: #0a7c3c;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #777;
            margin-top: 30px;
        }

        .muted {
            color: #777;
        }

        .criterion {
            font-weight: 600;
        }

        h4 {
            background: #1256A0;
            color: white;
            padding: 6px;
            font-size: 13px;
            margin-top: 25px;
        }

        .section-comment {
            background: #f8f9fa;
            border-left: 4px solid #1256A0;
            padding: 6px 10px;
            font-style: italic;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <h2>Site Visit Evaluation Report</h2>

    <div class="section">
        <strong>Consortium:</strong> {{ $evaluation->consortium->think_tank_name ?? 'N/A' }} <br>
        <strong>Team:</strong> {{ $evaluation->team->name ?? 'N/A' }} <br>
        <strong>Leader:</strong> {{ $evaluation->leader->name ?? 'N/A' }} <br>

        <strong>Team Members:</strong>
        @if ($evaluation->team && $evaluation->team->members && $evaluation->team->members->count() > 0)
            {{ $evaluation->team->members->map(fn($m) => $m->user->name)->implode(', ') }}
        @else
            <span class="muted">None</span>
        @endif
        <br>

        <strong>Date:</strong>
        {{ $evaluation->evaluation_date ? \Carbon\Carbon::parse($evaluation->evaluation_date)->format('F d, Y') : '—' }}
    </div>

    @php
        $sections = [
            1 => ['title' => 'Organizational Capacity', 'max' => 10, 'subs' => 4],
            2 => ['title' => 'Technical Capability', 'max' => 5, 'subs' => 3],
            3 => ['title' => 'Partnerships and Collaboration', 'max' => 5, 'subs' => 3],
            4 => ['title' => 'Innovation and Impact', 'max' => 5, 'subs' => 3],
            5 => ['title' => 'Sustainability', 'max' => 5, 'subs' => 3],
            6 => ['title' => 'Facility and Resource Adequacy', 'max' => 5, 'subs' => 3],
        ];
    @endphp

    {{-- ===================== LOOP THROUGH ALL SECTIONS ===================== --}}
    @foreach ($sections as $s => $meta)
        <h4>{{ $s }}. {{ $meta['title'] }} ({{ $meta['max'] }} points)</h4>
        <table>
            <thead>
                <tr>
                    <th width="35%">Criterion</th>
                    <th width="10%">Score</th>
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
                    @if (!empty($evaluation->$score) || !empty($evaluation->$str) || !empty($evaluation->$weak))
                        <tr>
                            <td class="criterion">{{ $s }}.{{ $i }}</td>
                            <td class="score">{{ $evaluation->$score ?? '—' }}</td>
                            <td>{{ $evaluation->$str ?? '—' }}</td>
                            <td>{{ $evaluation->$weak ?? '—' }}</td>
                        </tr>
                    @endif
                @endfor
            </tbody>
        </table>

        @php $commentField = "s{$s}_comments"; @endphp
        @if (!empty($evaluation->$commentField))
            <div class="section-comment">
                <strong>Evaluator’s Comment:</strong> {{ $evaluation->$commentField }}
            </div>
        @endif
    @endforeach

    {{-- ===================== GENERAL OBSERVATIONS ===================== --}}
    @if (!empty($evaluation->general_observations))
        <div class="section">
            <strong>General Observations:</strong> {{ $evaluation->general_observations }}
        </div>
    @endif

    {{-- ===================== TOTAL AND SUMMARY ===================== --}}
    <h4>Total Score: <span style="color:#0a7c3c;">{{ $evaluation->total_score }}/35</span></h4>

    <div class="section">
        <strong>Overall Strengths:</strong> {{ $evaluation->overall_strength ?? '—' }} <br>
        <strong>Overall Weaknesses:</strong> {{ $evaluation->overall_weakness ?? '—' }} <br>
        <strong>Additional Comments:</strong> {{ $evaluation->additional_comments ?? '—' }}
    </div>

    <div class="section">
        <strong>Evaluator:</strong> {{ $evaluation->evaluator_name ?? '—' }} <br>
        <strong>Signature:</strong> {{ $evaluation->evaluator_signature ?? '—' }}
    </div>

    <div class="footer">
        Generated on {{ now()->format('F d, Y') }} — Africa Think Tank Evaluation System
    </div>
</body>

</html>
