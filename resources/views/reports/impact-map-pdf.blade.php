<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'ATTP Impact Report' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            background: linear-gradient(135deg, #532934 0%, #7d4656 100%);
            color: white;
            padding: 20px 30px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 22pt;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10pt;
            opacity: 0.9;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #f8f9fa;
            border-left: 4px solid #532934;
            padding: 10px 15px;
            font-size: 12pt;
            font-weight: bold;
            color: #532934;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .summary-card .value {
            font-size: 18pt;
            font-weight: bold;
            color: #532934;
        }

        .summary-card .label {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 5px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        table.data-table th {
            background: #532934;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }

        table.data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.data-table tr:nth-child(even) {
            background: #f9fafb;
        }

        table.data-table .amount {
            text-align: right;
            font-weight: bold;
            color: #059669;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-primary { background: #532934; color: white; }
        .badge-success { background: #059669; color: white; }
        .badge-info { background: #0284c7; color: white; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #6b7280;
        }

        .footer .page-number {
            text-align: right;
        }

        .chart-placeholder {
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            margin-bottom: 15px;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .two-column .column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }

        .two-column .column:first-child {
            padding-left: 0;
        }

        .two-column .column:last-child {
            padding-right: 0;
        }

        .progress-bar {
            background: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-bar .fill {
            height: 100%;
            background: linear-gradient(90deg, #532934, #7d4656);
        }

        .highlight-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .highlight-box strong {
            color: #92400e;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $title ?? 'ATTP Impact Report' }}</h1>
        <p>Africa Think Tank Platform - Program Funding Impact Analysis</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>

    {{-- Executive Summary --}}
    <div class="section">
        <div class="section-title">Executive Summary</div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="value">USD {{ number_format($summary['total_funding'] / 1000000, 1) }}M</div>
                <div class="label">Total Funding</div>
            </div>
            <div class="summary-card">
                <div class="value">{{ $summary['total_programs'] }}</div>
                <div class="label">Programs</div>
            </div>
            <div class="summary-card">
                <div class="value">{{ $summary['total_partners'] }}</div>
                <div class="label">Funding Partners</div>
            </div>
            <div class="summary-card">
                <div class="value">{{ $summary['total_countries'] }}</div>
                <div class="label">Countries Reached</div>
            </div>
        </div>

        @if($summary['continental_programs'] > 0)
        <div class="highlight-box">
            <strong>{{ $summary['continental_programs'] }} Continental Initiative(s)</strong> -
            Programs designed to benefit all 55 AU member states across the continent.
        </div>
        @endif
    </div>

    {{-- Funding by Partner --}}
    @if(count($fundingByPartner) > 0)
    <div class="section">
        <div class="section-title">Funding by Partner</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="25%">Funding Partner</th>
                    <th width="15%">Total Funding</th>
                    <th width="10%">Programs</th>
                    <th width="10%">Countries</th>
                    <th width="20%">Regions</th>
                    <th width="20%">Aspirations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fundingByPartner as $partner)
                <tr>
                    <td><strong>{{ $partner['name'] }}</strong></td>
                    <td class="amount">USD {{ number_format($partner['total_funding'], 0) }}</td>
                    <td>{{ $partner['program_count'] }}</td>
                    <td>{{ $partner['country_count'] }}{{ $partner['has_continental'] ? '*' : '' }}</td>
                    <td>{{ implode(', ', $partner['regions']) ?: '-' }}</td>
                    <td>
                        @foreach($partner['aspirations'] as $asp)
                            <span class="badge badge-primary">Asp. {{ $asp }}</span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p style="font-size: 8pt; color: #6b7280;">* Includes Continental Initiative(s) covering all AU member states</p>
    </div>
    @endif

    {{-- Funding by Region --}}
    @if(count($fundingByRegion) > 0)
    <div class="section">
        <div class="section-title">Funding by Regional Block</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="20%">Regional Block</th>
                    <th width="20%">Total Funding</th>
                    <th width="15%">Programs</th>
                    <th width="15%">Partners</th>
                    <th width="30%">Member Countries</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fundingByRegion as $region)
                <tr>
                    <td>
                        <strong>{{ $region['abbreviation'] }}</strong><br>
                        <span style="font-size: 8pt; color: #6b7280;">{{ $region['name'] }}</span>
                    </td>
                    <td class="amount">USD {{ number_format($region['total_funding'], 0) }}</td>
                    <td>{{ $region['program_count'] }}</td>
                    <td>{{ $region['partner_count'] }}</td>
                    <td style="font-size: 8pt;">{{ implode(', ', array_slice($region['countries'], 0, 5)) }}{{ count($region['countries']) > 5 ? '...' : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Top Countries --}}
    @if(count($fundingByCountry) > 0)
    <div class="section">
        <div class="section-title">Top Beneficiary Countries</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="25%">Country</th>
                    <th width="20%">Direct Funding</th>
                    <th width="20%">Continental Share</th>
                    <th width="15%">Total Programs</th>
                    <th width="20%">Regions</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($fundingByCountry, 0, 15) as $country)
                <tr>
                    <td>
                        <strong>{{ $country['name'] }}</strong>
                        <span class="badge badge-info">{{ $country['code'] }}</span>
                    </td>
                    <td class="amount">USD {{ number_format($country['direct_funding'], 0) }}</td>
                    <td style="color: #6b7280;">USD {{ number_format($country['continental_funding'], 0) }}</td>
                    <td>{{ $country['total_programs'] }}</td>
                    <td>{{ implode(', ', $country['regions']) ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Agenda 2063 Alignment --}}
    @if(count($fundingByAspiration) > 0)
    <div class="section">
        <div class="section-title">Agenda 2063 Alignment</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="10%">Aspiration</th>
                    <th width="35%">Title</th>
                    <th width="20%">Total Funding</th>
                    <th width="15%">Programs</th>
                    <th width="20%">Goals Addressed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fundingByAspiration as $aspiration)
                <tr>
                    <td><span class="badge badge-primary">{{ $aspiration['number'] }}</span></td>
                    <td>{{ $aspiration['title'] }}</td>
                    <td class="amount">USD {{ number_format($aspiration['total_funding'], 0) }}</td>
                    <td>{{ $aspiration['program_count'] }}</td>
                    <td>
                        @foreach($aspiration['goals'] as $goal)
                            <span class="badge badge-success">G{{ $goal }}</span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Program Details --}}
    @if(count($programs) > 0)
    <div class="section">
        <div class="section-title">Program Details</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="25%">Program Name</th>
                    <th width="15%">Funder</th>
                    <th width="15%">Amount</th>
                    <th width="10%">Period</th>
                    <th width="10%">Type</th>
                    <th width="25%">Coverage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programs as $program)
                <tr>
                    <td><strong>{{ $program['name'] }}</strong></td>
                    <td>{{ $program['funder'] ?? 'N/A' }}</td>
                    <td class="amount">{{ $program['currency'] }} {{ number_format($program['amount'], 0) }}</td>
                    <td>{{ $program['period'] }}</td>
                    <td>{{ $program['type'] }}</td>
                    <td style="font-size: 8pt;">
                        @if($program['is_continental'])
                            <span class="badge badge-success">Continental</span>
                        @else
                            {{ implode(', ', array_slice($program['countries'], 0, 3)) }}{{ count($program['countries']) > 3 ? '...' : '' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td>Africa Think Tank Platform (ATTP) - Impact Analytics Report</td>
                <td style="text-align: center;">Confidential</td>
                <td style="text-align: right;">{{ $generated_at }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
