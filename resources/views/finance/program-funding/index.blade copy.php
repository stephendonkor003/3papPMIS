@extends('layouts.app')

@section('content')
    <style>
        /* ---------- Responsive helpers ---------- */
        .dropdown-panel {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 260px;
            overflow-y: auto;
            z-index: 50;
            display: none;
        }

        .dropdown-panel label {
            display: flex;
            gap: 8px;
            padding: 8px 12px;
            cursor: pointer;
        }

        .dropdown-panel label:hover {
            background: #f5f7fa;
        }

        .badge-count {
            background: #0d6efd;
            color: #fff;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
        }

        .heatmap-wrapper {
            overflow-x: auto;
        }

        .heat-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            min-width: max-content;
        }

        .heat-cell {
            width: 22px;
            height: 16px;
            border-radius: 3px;
        }
    </style>

    <div class="nxl-container">

        {{-- HEADER --}}
        {{-- HEADER --}}
        <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Financial Execution Intelligence</h4>
                <p class="text-muted mb-0">
                    Project · Program · Sector monitoring with burn-rate & variance
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('finance.program-funding.create') }}" class="btn btn-primary">
                    <i class="feather-plus-circle me-1"></i>
                    Create Program Funding
                </a>
            </div>
        </div>


        {{-- FILTERS --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body row g-3">

                <div class="col-lg-3 col-md-4 col-sm-12">
                    <label class="form-label fw-semibold">View Level</label>
                    <select id="levelSelect" class="form-select">
                        <option value="project">Project</option>
                        <option value="program">Program</option>
                        <option value="sector">Sector</option>
                    </select>
                </div>

                <div class="col-lg-6 col-md-8 col-sm-12 position-relative">
                    <label class="form-label fw-semibold">
                        Entities <span id="entityCount" class="badge-count">0</span>
                    </label>

                    <div id="entityTrigger" class="form-control d-flex justify-content-between align-items-center"
                        style="cursor:pointer">
                        <span>Select entities</span>
                        <i class="feather-chevron-down"></i>
                    </div>

                    <div id="entityPanel" class="dropdown-panel"></div>
                </div>

                <div class="col-lg-3 col-md-12 col-sm-12">
                    <label class="form-label fw-semibold">Metrics</label>
                    <div class="d-flex flex-wrap gap-2">
                        <label><input type="checkbox" class="metric" value="funding" checked> Funding</label>
                        <label><input type="checkbox" class="metric" value="allocated" checked> Allocation</label>
                        <label><input type="checkbox" class="metric" value="committed" checked> Commitment</label>
                        <label><input type="checkbox" class="metric" value="remaining" checked> Remaining</label>
                    </div>
                </div>

            </div>
        </div>

        {{-- BURN RATE --}}
        <div id="burnRate" class="alert alert-info py-2 small mb-3"></div>

        {{-- CHART --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <canvas id="chart" height="120"></canvas>
            </div>
        </div>

        {{-- HEATMAP --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Variance Heatmap</h6>
                <div class="heatmap-wrapper">
                    <div id="heatmap"></div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /* =========================
           DATA
        ========================= */
        const raw = @json($projectChartData);
        let chart = null;
        let currentData = [];

        /* =========================
           HELPERS
        ========================= */
        const yearsOf = p =>
            Array.from({
                length: p.end_year - p.start_year + 1
            }, (_, i) => p.start_year + i);

        const slope = arr => {
            let n = arr.length,
                sx = 0,
                sy = 0,
                sxy = 0,
                sx2 = 0;
            arr.forEach((y, i) => {
                sx += i;
                sy += y;
                sxy += i * y;
                sx2 += i * i
            });
            return (n * sxy - sx * sy) / (n * sx2 - sx * sx || 1);
        };

        const varianceColor = v =>
            v < 0 ? '#198754' :
            v < 0.1 ? '#ffc107' : '#dc3545';

        /* =========================
           GROUPING
        ========================= */
        function groupBy(level) {
            let map = {};

            raw.forEach(p => {
                const key =
                    level === 'project' ? p.project :
                    level === 'program' ? p.program :
                    p.sector;

                if (!map[key]) {
                    map[key] = {
                        label: key,
                        start_year: p.start_year,
                        end_year: p.end_year,
                        funding: 0,
                        allocated_by_year: {},
                        committed_by_year: {}
                    };
                }

                map[key].funding += p.funding;

                for (let y in p.allocated_by_year)
                    map[key].allocated_by_year[y] =
                    (map[key].allocated_by_year[y] || 0) + p.allocated_by_year[y];

                for (let y in p.committed_by_year)
                    map[key].committed_by_year[y] =
                    (map[key].committed_by_year[y] || 0) + p.committed_by_year[y];
            });

            return Object.values(map);
        }

        /* =========================
           ENTITY DROPDOWN
        ========================= */
        const panel = document.getElementById('entityPanel');
        const trigger = document.getElementById('entityTrigger');
        const countBadge = document.getElementById('entityCount');

        trigger.onclick = () =>
            panel.style.display = panel.style.display === 'block' ? 'none' : 'block';

        document.addEventListener('click', e => {
            if (!e.target.closest('.position-relative')) panel.style.display = 'none';
        });

        function renderEntities(data) {
            panel.innerHTML = '';
            data.forEach((d, i) => {
                panel.innerHTML += `
            <label>
                <input type="checkbox" class="entity" value="${i}">
                ${d.label}
            </label>`;
            });

            // Auto-select first entity
            const first = panel.querySelector('.entity');
            if (first) first.checked = true;

            panel.querySelectorAll('.entity').forEach(cb =>
                cb.addEventListener('change', update)
            );

            updateCount();
        }

        function updateCount() {
            countBadge.textContent =
                document.querySelectorAll('.entity:checked').length;
        }

        /* =========================
           RENDER
        ========================= */
        function render() {

            const selected = [...document.querySelectorAll('.entity:checked')]
                .map(cb => currentData[cb.value]);

            if (!selected.length) return;

            const years = yearsOf(selected[0]);
            let datasets = [];
            let slopes = [];

            selected.forEach(p => {

                let cum = 0;
                let committedCum = years.map(y => cum += (p.committed_by_year[y] || 0));
                slopes.push(slope(committedCum));

                if (metricOn('funding'))
                    datasets.push(line(`${p.label} Funding`, years.map(() => p.funding), '#0d6efd', [5, 5]));

                if (metricOn('allocated'))
                    datasets.push(line(`${p.label} Allocation`, years.map(y => p.allocated_by_year[y] || 0),
                        '#fd7e14'));

                if (metricOn('committed'))
                    datasets.push(line(`${p.label} Commitment`, years.map(y => p.committed_by_year[y] || 0),
                        '#dc3545'));

                if (metricOn('remaining'))
                    datasets.push(line(`${p.label} Remaining`,
                        committedCum.map(v => Math.max(p.funding - v, 0)), '#198754', [2, 2]));
            });

            if (chart) chart.destroy();

            chart = new Chart(document.getElementById('chart'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            burnRate.textContent =
                slopes.some(s => s > 0.5) ? '🔥 Accelerating burn rate detected' :
                slopes.some(s => s > 0.1) ? '⚠️ Moderate burn rate' :
                '✅ Stable burn rate';

            renderHeatmap(selected, years);
        }

        function line(label, data, color, dash = []) {
            return {
                label,
                data,
                borderColor: color,
                borderDash: dash,
                tension: .3
            };
        }

        function metricOn(v) {
            return document.querySelector(`.metric[value="${v}"]`).checked;
        }

        /* =========================
           HEATMAP
        ========================= */
        function renderHeatmap(data, years) {
            heatmap.innerHTML = '';
            data.forEach(p => {
                let row = document.createElement('div');
                row.className = 'heat-row';

                row.innerHTML = `<small style="width:140px">${p.label}</small>`;

                let cum = 0;
                years.forEach(y => {
                    cum += p.committed_by_year[y] || 0;
                    let v = (cum - p.funding) / p.funding;
                    row.innerHTML +=
                        `<div class="heat-cell" style="background:${varianceColor(v)}"
                 title="${y}"></div>`;
                });

                heatmap.appendChild(row);
            });
        }

        /* =========================
           EVENTS
        ========================= */
        levelSelect.onchange = () => {
            currentData = groupBy(levelSelect.value);
            renderEntities(currentData);
            render();
        };

        document.querySelectorAll('.metric').forEach(m => m.onchange = render);

        /* =========================
           INIT
        ========================= */
        currentData = groupBy('project');
        renderEntities(currentData);
        render();
    </script>
@endsection
