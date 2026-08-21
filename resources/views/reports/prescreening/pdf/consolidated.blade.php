<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prescreening Consolidated Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1, h2 { margin: 0 0 8px 0; color: #0f172a; }
        .section { margin: 18px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        .muted { color: #666; }
        .header {
            background: #0f172a;
            color: #fff;
            padding: 14px 18px;
            border-bottom: 4px solid #22c55e;
        }
        .header .title { font-size: 18px; font-weight: bold; }
        .header .meta { font-size: 11px; margin-top: 6px; color: #e2e8f0; }
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 42px;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 10px;
            padding: 10px 16px;
            border-top: 3px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }} — Prescreening Consolidated Report</div>
        <div class="meta">
            All procurements overview | Generated: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <div class="section">
        <h2>Summary</h2>
        <table>
            <tr>
                <th>Total</th>
                <th>Passed</th>
                <th>Failed</th>
                <th>Pending</th>
            </tr>
            <tr>
                <td>{{ $summary['total'] }}</td>
                <td>{{ $summary['passed'] }}</td>
                <td>{{ $summary['failed'] }}</td>
                <td>{{ $summary['pending'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Evaluator Breakdown</h2>
        <table>
            <tr>
                <th>Evaluator</th>
                <th>Total</th>
                <th>Passed</th>
                <th>Failed</th>
            </tr>
            @forelse ($evaluatorBreakdown as $name => $stats)
                <tr>
                    <td>{{ $name }}</td>
                    <td>{{ $stats['total'] }}</td>
                    <td>{{ $stats['passed'] }}</td>
                    <td>{{ $stats['failed'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No evaluations yet.</td>
                </tr>
            @endforelse
        </table>
    </div>

    <div class="section">
        <h2>Submissions</h2>
        <table>
            <tr>
                <th>Procurement</th>
                <th>Submission Code</th>
                <th>Status</th>
                <th>Evaluator</th>
                <th>Evaluated At</th>
            </tr>
            @forelse ($submissions as $submission)
                <tr>
                    <td>{{ $submission->procurement->title ?? '—' }}</td>
                    <td>{{ $submission->procurement_submission_code }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $submission->status ?? 'pending')) }}</td>
                    <td>{{ $submission->prescreeningResult?->evaluator?->name ?? '—' }}</td>
                    <td>{{ $submission->prescreeningResult?->evaluated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No submissions found.</td>
                </tr>
            @endforelse
        </table>
    </div>
    <div class="footer">
        {{ config('app.name') }} — Confidential Prescreening Report — Generated: {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
