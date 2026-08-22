<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $direction = config("localization.supported.{$locale}.direction", 'ltr');
@endphp
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings->page_title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root { --ink:#0f172a; --muted:#64748b; --panel:#fff; --line:#dbe4ef; --brand:#0A6ED1; --au-green:#0A6ED1; --au-green-dark:#06152F; --au-green-light:#1687EA; }
        body { margin:0; color:var(--ink); background:linear-gradient(180deg,#F4F7FA 0%,#EFF8FF 48%,#F4F7FA 100%); }
        .hero-world,.summary-grid,.viz-shell,.compare-shell { width:min(1400px,95%); margin-left:auto; margin-right:auto; }
        .hero-world { margin-top:1.1rem; border-radius:18px; padding:1.3rem 1.4rem; color:#eef6ff; background:linear-gradient(130deg,var(--au-green-dark) 0%,var(--au-green) 55%,var(--au-green-light) 100%); box-shadow:0 20px 42px rgba(6,21,47,.30); }
        .hero-world h1 { margin:0 0 .45rem; font-size:clamp(1.3rem,2vw,2rem); }
        .hero-world p { margin:0 0 .85rem; color:rgba(230,243,255,.92); max-width:1000px; line-height:1.45; }
        .hero-meta,.source-pills,.hero-links { display:flex; flex-wrap:wrap; gap:.45rem; align-items:center; }
        .hero-meta { justify-content:space-between; }
        .source-pill,.hero-links a { border:1px solid rgba(255,255,255,.35); border-radius:999px; padding:.28rem .68rem; font-size:.75rem; color:#eef6ff; text-decoration:none; background:rgba(255,255,255,.08); }
        .summary-grid { margin-top:.95rem; display:grid; grid-template-columns:repeat(8,minmax(0,1fr)); gap:.7rem; }
        .summary-card { background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:.72rem .8rem; box-shadow:0 10px 20px rgba(15,23,42,.05); }
        .summary-card .label { font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); font-weight:700; }
        .summary-card .value { margin-top:.15rem; font-size:1.16rem; font-weight:800; }
        .viz-shell { margin-top:.95rem; display:grid; grid-template-columns:1.65fr 1fr; gap:.9rem; align-items:start; }
        .compare-shell { margin-top:.95rem; margin-bottom:2rem; }
        .world-panel { background:var(--panel); border:1px solid var(--line); border-radius:14px; box-shadow:0 14px 28px rgba(15,23,42,.08); overflow:hidden; }
        .world-panel-head { padding:.85rem 1rem; border-bottom:1px solid #e8edf5; background:linear-gradient(180deg,#fff 0%,#f8fbff 100%); }
        .world-panel-head h3 { margin:0; color:var(--brand); font-size:1rem; }
        .world-panel-head p { margin:.25rem 0 0; color:#334155; font-size:.8rem; line-height:1.4; }
        .controls { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.55rem; padding:.8rem 1rem; border-bottom:1px solid #e8edf5; background:#f8fbff; }
        .controls.controls-6 { grid-template-columns:repeat(6,minmax(0,1fr)); }
        .controls .span-2 { grid-column:span 2; } .controls .span-3 { grid-column:span 3; } .controls .span-4 { grid-column:span 4; }
        .controls label { display:block; margin-bottom:.22rem; font-size:.7rem; letter-spacing:.05em; font-weight:700; text-transform:uppercase; color:var(--muted); }
        .controls select,.controls input,.controls button { width:100%; border:1px solid #cfd8e6; border-radius:8px; padding:.43rem .48rem; background:#fff; color:var(--ink); font-size:.85rem; }
        .controls button { border:0; font-weight:700; cursor:pointer; }
        .slider-readout { margin-top:.25rem; font-size:.72rem; color:#475569; }
        .controls .btn-primary { background:linear-gradient(120deg,var(--au-green),var(--au-green-light)); color:#fff; }
        .btn-secondary { background:#e8eef8; color:#0f172a; }
        #worldMap { width:100%; min-height:590px; background:#dbe7f6; }
        .status,.map-status { border:1px solid #DBE4EE; background:#EFF8FF; color:#0A2450; border-radius:10px; padding:.52rem .65rem; font-size:.8rem; line-height:1.35; }
        .status.error,.map-status.error { background:#fff1f2; border-color:#fecdd3; color:#9f1239; }
        .map-meta { padding:.75rem 1rem; border-top:1px solid #e8edf5; background:#f8fbff; }
        .legend { border:1px solid #d9e3f1; border-radius:10px; background:#fff; padding:.6rem .7rem; margin-bottom:.55rem; }
        .legend-title { font-weight:700; font-size:.83rem; margin-bottom:.36rem; }
        .legend-scale { height:10px; border-radius:999px; background:linear-gradient(90deg,#DCEEFF 0%,#2FD0ED 34%,#1687EA 67%,#0756C7 100%); border:1px solid #DBE4EE; }
        .legend-range { margin-top:.3rem; font-size:.75rem; color:#475569; display:flex; justify-content:space-between; gap:.4rem; }
        .data-grid { padding:0 1rem 1rem; display:grid; grid-template-columns:1fr 1fr; gap:.7rem; }
        .data-card { border:1px solid #dbe4ef; border-radius:11px; background:#fff; overflow:hidden; }
        .data-card h4 { margin:0; padding:.58rem .7rem; border-bottom:1px solid #e8edf5; font-size:.84rem; background:#f8fbff; }
        .table-wrap { max-height:280px; overflow:auto; }
        table { width:100%; border-collapse:collapse; font-size:.77rem; }
        th,td { border-bottom:1px solid #edf2f9; padding:.34rem .45rem; text-align:left; vertical-align:top; }
        thead th { position:sticky; top:0; background:#f8fbff; z-index:1; }
        .snapshot-content { padding:.78rem .95rem 1rem; }
        .snapshot-country { margin:0; color:var(--au-green); font-size:1.04rem; }
        .snapshot-hint { margin:.35rem 0 .75rem; color:#475569; font-size:.8rem; }
        .snapshot-highlights { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; margin-bottom:.7rem; }
        .highlight-card { border:1px solid #dbe4ef; border-radius:10px; padding:.55rem .6rem; background:#f8fbff; }
        .highlight-card .k { font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; color:#64748b; font-weight:700; }
        .highlight-card .v { margin-top:.16rem; font-size:.88rem; font-weight:700; }
        .chart-wrap { border:1px solid #dbe4ef; border-radius:10px; background:#fff; padding:.45rem; height:280px; margin-bottom:.65rem; }
        .source-grid { display:grid; gap:.58rem; }
        .metric-source { border:1px solid #dbe4ef; border-radius:10px; background:#f8fbff; padding:.6rem; }
        .metric-source h5 { margin:0; font-size:.86rem; }
        .metric-source .note { margin:.24rem 0 .48rem; color:#64748b; font-size:.74rem; }
        .metric-list { margin:0; padding:0; list-style:none; }
        .metric-list li { border-top:1px dashed #d4deec; padding:.35rem 0; display:flex; justify-content:space-between; gap:.5rem; font-size:.77rem; }
        .compare-grid { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; padding:0 1rem 1rem; }
        .viz-card { border:1px solid #dbe4ef; border-radius:11px; background:#fff; overflow:hidden; }
        .viz-card.wide { grid-column:span 2; }
        .viz-card h4 { margin:0; padding:.56rem .7rem; border-bottom:1px solid #e8edf5; font-size:.84rem; background:#f8fbff; }
        .series-cards { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; padding:.55rem; }
        .series-card { border:1px solid #dbe4ef; border-radius:10px; padding:.52rem; background:#f8fbff; }
        .series-card .name { font-size:.74rem; color:#475569; font-weight:700; }
        .series-card .latest { font-size:.93rem; font-weight:800; margin-top:.2rem; }
        .series-card .change { font-size:.74rem; margin-top:.18rem; }
        .heatmap-wrap { max-height:360px; overflow:auto; }
        .heatmap-wrap th:first-child,.heatmap-wrap td:first-child { position:sticky; left:0; background:#f8fbff; z-index:2; }
        .viz-modal { position:fixed; inset:0; z-index:2200; background:rgba(15,23,42,.72); display:none; align-items:center; justify-content:center; padding:1rem; }
        .viz-modal.open { display:flex; }
        .viz-modal-dialog { width:min(1150px,96vw); max-height:92vh; background:#fff; border:1px solid #dbe4ef; border-radius:14px; overflow:hidden; display:grid; grid-template-rows:auto 1fr; }
        .viz-modal-head { display:flex; align-items:center; justify-content:space-between; gap:.6rem; padding:.75rem .95rem; border-bottom:1px solid #e8edf5; background:#f8fbff; }
        .viz-modal-head h4 { margin:0; font-size:1rem; }
        .viz-modal-head p { margin:.15rem 0 0; color:#64748b; font-size:.78rem; }
        .viz-modal-close { border:0; background:#e2e8f0; width:32px; height:32px; border-radius:8px; font-size:1rem; cursor:pointer; }
        .viz-modal-body { overflow:auto; padding:.85rem .95rem 1rem; }
        .loading-modal { position:fixed; inset:0; z-index:3200; background:rgba(15,23,42,.62); display:none; align-items:center; justify-content:center; padding:1rem; }
        .loading-modal.open { display:flex; }
        .loading-box { width:min(420px,92vw); border:1px solid #dbe4ef; border-radius:14px; background:#fff; box-shadow:0 18px 44px rgba(15,23,42,.28); padding:1rem 1.1rem; text-align:center; }
        .loading-spinner { width:44px; height:44px; border-radius:50%; border:4px solid #DCEEFF; border-top-color:#0A6ED1; margin:0 auto .7rem; animation:loading-spin 1s linear infinite; }
        .loading-title { margin:0; font-size:.95rem; color:#1f2937; }
        .loading-text { margin:.28rem 0 0; font-size:.8rem; color:#64748b; }
        @keyframes loading-spin { to { transform:rotate(360deg); } }
        body.app-loading { cursor:progress; }
        body.app-loading .hero-world,
        body.app-loading .summary-grid,
        body.app-loading .viz-shell,
        body.app-loading .compare-shell,
        body.app-loading .viz-modal { pointer-events:none !important; }
        body.app-loading .world-panel,
        body.app-loading .data-card,
        body.app-loading .table-wrap,
        body.app-loading .heatmap-wrap,
        body.app-loading #worldMap,
        body.app-loading .viz-modal-dialog,
        body.app-loading .continent-map-card { opacity:.58; filter:saturate(.72); transition:opacity .12s ease; }
        body.app-loading #loadingModal,
        body.app-loading #loadingModal * { pointer-events:auto !important; opacity:1; filter:none; }
        body.dashboard-initializing .viz-shell,
        body.dashboard-initializing .compare-shell { pointer-events:none; opacity:.58; transition:opacity .12s ease; }
        .map-compare-dialog { width:min(1320px,98vw); }
        .continent-map-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; margin-bottom:.75rem; }
        .continent-map-card { border:1px solid #dbe4ef; border-radius:12px; background:#fff; overflow:hidden; }
        .continent-map-head { display:flex; justify-content:space-between; align-items:center; gap:.4rem; padding:.6rem .7rem; border-bottom:1px solid #e8edf5; background:#f8fbff; }
        .continent-map-head h5 { margin:0; font-size:.82rem; color:#334155; }
        .continent-map-head span { font-size:.72rem; color:#64748b; }
        .continent-map { min-height:270px; background:#eef4fb; }
        .continent-map-meta { padding:.45rem .65rem .6rem; border-top:1px solid #e8edf5; background:#f8fbff; }
        .continent-map-scale { height:8px; border-radius:999px; background:linear-gradient(90deg,#DCEEFF 0%,#2FD0ED 34%,#1687EA 67%,#0756C7 100%); border:1px solid #DBE4EE; }
        .continent-map-range { margin-top:.24rem; display:flex; justify-content:space-between; gap:.45rem; font-size:.71rem; color:#475569; }
        .continent-mini-table { max-height:170px; overflow:auto; }
        .empty-box { border:1px dashed #9fb4d2; border-radius:10px; background:#f8fbff; color:#475569; font-size:.8rem; padding:.72rem; }
        @media (max-width:1200px){ .summary-grid{grid-template-columns:repeat(4,minmax(0,1fr));} .viz-shell,.data-grid,.compare-grid,.continent-map-grid{grid-template-columns:1fr;} .viz-card.wide{grid-column:span 1;} }
        @media (max-width:900px){ .controls,.controls.controls-6{grid-template-columns:1fr 1fr;} .controls .span-2,.controls .span-3{grid-column:span 2;} }
        @media (max-width:768px){ .summary-grid{grid-template-columns:repeat(2,minmax(0,1fr));} #worldMap{min-height:500px;} }
        @media (max-width:560px){ .summary-grid,.controls,.controls.controls-6{grid-template-columns:1fr;} .controls .span-2,.controls .span-3,.controls .span-4{grid-column:span 1;} .snapshot-highlights,.series-cards{grid-template-columns:1fr;} }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
    @if($direction === 'rtl')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
</head>
<body class="public-mobile-nav pmis-marketing-page" dir="{{ $direction }}">
    @include('partials.public-marketing-header')

    <section class="hero-world" id="main-content" tabindex="-1">
        <h1>{{ $settings->page_title }}</h1>
        <p>{{ $settings->page_intro ?: __('world_indicators.hero.fallback_intro') }}</p>
        <div class="hero-meta">
            <div class="source-pills">
                @forelse ($enabledSources as $source)
                    <span class="source-pill">{{ $source['label'] }}</span>
                @empty
                    <span class="source-pill">{{ __('world_indicators.hero.no_sources') }}</span>
                @endforelse
            </div>
            <div class="hero-links">
                <a href="#geo-analytics">{{ __('world_indicators.hero.geo_analytics') }}</a>
                <a href="#snapshot-lab">{{ __('world_indicators.hero.country_snapshot') }}</a>
                <a href="#worldbank-compare">{{ __('world_indicators.hero.comparison_studio') }}</a>
            </div>
        </div>
    </section>

    <section class="summary-grid">
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.regions') }}</div><div class="value">{{ $summary['regions'] }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.country_shapes') }}</div><div class="value">{{ $summary['countries'] }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.shapefiles') }}</div><div class="value">{{ $summary['shape_files'] }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.default_region') }}</div><div class="value">{{ $regionLabels[$defaultRegion] ?? __('world_indicators.common.auto') }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.wb_topics') }}</div><div class="value">{{ $worldBankSummary['topics'] ?? 0 }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.wb_indicators') }}</div><div class="value">{{ $worldBankSummary['indicators'] ?? 0 }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.wb_countries') }}</div><div class="value">{{ $worldBankSummary['countries'] ?? 0 }}</div></article>
        <article class="summary-card"><div class="label">{{ __('world_indicators.summary.current_year') }}</div><div class="value">{{ now()->year }}</div></article>
    </section>

    <section class="viz-shell" id="geo-analytics">
        <article class="world-panel">
            <div class="world-panel-head">
                <h3>{{ __('world_indicators.map.title') }}</h3>
                <p>{{ __('world_indicators.map.description') }}</p>
            </div>
            <div class="controls controls-6">
                <div><label for="regionSelect">{{ __('world_indicators.common.region') }}</label><select id="regionSelect">@foreach ($enabledRegions as $region)<option value="{{ $region }}" @selected($defaultRegion === $region)>{{ $regionLabels[$region] ?? $region }}</option>@endforeach</select></div>
                <div><label for="mapTopicSelect">{{ __('world_indicators.map.group') }}</label><select id="mapTopicSelect"><option value="">{{ __('world_indicators.common.loading') }}</option></select></div>
                <div><label for="mapIndicatorSelect">{{ __('world_indicators.map.indicator') }}</label><select id="mapIndicatorSelect"><option value="">{{ __('world_indicators.common.select_indicator') }}</option></select></div>
                <div><label for="mapYearInput">{{ __('world_indicators.map.year') }}</label><input id="mapYearInput" type="number" min="1960" max="{{ now()->year }}" step="1"></div>
                <div><label for="mapYearSlider">{{ __('world_indicators.map.year_slider') }}</label><input id="mapYearSlider" type="range" min="1960" max="{{ now()->year }}" step="1"></div>
                <div class="span-2"><label for="mapContinentsSelect">{{ __('world_indicators.map.compare_continents') }}</label><select id="mapContinentsSelect" multiple size="6"></select></div>
                <div><label for="mapRangeMinSlider">{{ __('world_indicators.map.color_min') }}</label><input id="mapRangeMinSlider" type="range" min="0" max="100" step="1" value="0"></div>
                <div><label for="mapRangeMaxSlider">{{ __('world_indicators.map.color_max') }}</label><input id="mapRangeMaxSlider" type="range" min="0" max="100" step="1" value="100"><div class="slider-readout" id="mapRangeLabel">{{ __('world_indicators.map.visible_range', ['min' => 0, 'max' => 100]) }}</div></div>
                <div><label for="runMapVizBtn">{{ __('world_indicators.map.overlay') }}</label><button id="runMapVizBtn" class="btn-primary" type="button">{{ __('world_indicators.map.apply') }}</button></div>
                <div><label for="runContinentCompareBtn">{{ __('world_indicators.map.continent_compare') }}</label><button id="runContinentCompareBtn" class="btn-primary" type="button">{{ __('world_indicators.map.compare_selected') }}</button></div>
                <div><label for="openMapCompareModalBtn">{{ __('world_indicators.map.geo_modal') }}</label><button id="openMapCompareModalBtn" class="btn-secondary" type="button">{{ __('world_indicators.map.open_multi_map') }}</button></div>
                <div><label for="resetMapVizBtn">{{ __('world_indicators.map.reset') }}</label><button id="resetMapVizBtn" class="btn-secondary" type="button">{{ __('world_indicators.map.clear_overlay') }}</button></div>
            </div>
            <div id="worldMap"></div>
            <div class="map-meta">
                <div class="legend">
                    <div class="legend-title" id="mapLegendTitle">{{ __('world_indicators.map.legend_empty') }}</div>
                    <div class="legend-scale"></div>
                    <div class="legend-range" id="mapLegendRange"><span>{{ __('world_indicators.common.no_data') }}</span><span>{{ __('world_indicators.common.no_data') }}</span></div>
                </div>
                <div class="map-status" id="mapStatus">{{ __('world_indicators.map.preparing') }}</div>
            </div>
            <div class="data-grid">
                <div class="data-card"><h4>{{ __('world_indicators.map.top_countries') }}</h4><div class="table-wrap"><table><thead><tr><th>#</th><th>{{ __('world_indicators.common.country') }}</th><th>{{ __('world_indicators.common.value') }}</th></tr></thead><tbody id="mapTopTableBody"><tr><td colspan="3">{{ __('world_indicators.map.no_map_data') }}</td></tr></tbody></table></div></div>
                <div class="data-card"><h4>{{ __('world_indicators.map.regional_sheet') }}</h4><div class="table-wrap"><table><thead><tr><th>{{ __('world_indicators.common.country') }}</th><th>{{ __('world_indicators.common.code') }}</th><th>{{ __('world_indicators.common.value') }}</th></tr></thead><tbody id="mapDataTableBody"><tr><td colspan="3">{{ __('world_indicators.map.no_map_data') }}</td></tr></tbody></table></div></div>
            </div>
        </article>

        <aside class="world-panel" id="snapshot-lab">
            <div class="world-panel-head">
                <h3>{{ __('world_indicators.snapshot.title') }}</h3>
                <p>{{ __('world_indicators.snapshot.description') }}</p>
            </div>
            <div class="controls">
                <div><label for="countrySelect">{{ __('world_indicators.common.country') }}</label><select id="countrySelect"><option value="">{{ __('world_indicators.common.select_country') }}</option></select></div>
                <div><label for="snapshotIndicatorSelect">{{ __('world_indicators.snapshot.trend_indicator') }}</label><select id="snapshotIndicatorSelect"><option value="">{{ __('world_indicators.common.select_indicator') }}</option></select></div>
                <div><label for="snapshotYearFrom">{{ __('world_indicators.common.year_from') }}</label><input id="snapshotYearFrom" type="number" min="1960" step="1"></div>
                <div><label for="snapshotYearTo">{{ __('world_indicators.common.year_to') }}</label><input id="snapshotYearTo" type="number" min="1960" step="1"></div>
                <div><label for="runSnapshotBtn">{{ __('world_indicators.snapshot.trend') }}</label><button id="runSnapshotBtn" class="btn-primary" type="button">{{ __('world_indicators.snapshot.load_trend') }}</button></div>
                <div><label for="openSnapshotModalBtn">{{ __('world_indicators.common.expand_view') }}</label><button id="openSnapshotModalBtn" class="btn-secondary" type="button">{{ __('world_indicators.snapshot.open_modal') }}</button></div>
            </div>
            <div class="snapshot-content">
                <h4 class="snapshot-country" id="snapshotCountry">{{ __('world_indicators.snapshot.no_country') }}</h4>
                <p class="snapshot-hint" id="snapshotHint">{{ __('world_indicators.snapshot.select_hint') }}</p>
                <div id="snapshotHighlights" class="snapshot-highlights"><div class="empty-box">{{ __('world_indicators.snapshot.no_highlights_yet') }}</div></div>
                <div class="chart-wrap"><canvas id="snapshotChart"></canvas></div>
                <div class="data-card" style="margin-bottom:0.65rem;"><div class="table-wrap"><table><thead><tr><th>{{ __('world_indicators.common.year') }}</th><th style="text-align:right;">{{ __('world_indicators.common.value') }}</th></tr></thead><tbody id="snapshotTableBody"><tr><td colspan="2">{{ __('world_indicators.snapshot.no_trend') }}</td></tr></tbody></table></div></div>
                <div id="snapshotMetrics" class="source-grid"><div class="empty-box">{{ __('world_indicators.snapshot.metrics_here') }}</div></div>
            </div>
        </aside>
    </section>

    <section class="compare-shell" id="worldbank-compare">
        <article class="world-panel">
            <div class="world-panel-head">
                <h3>{{ __('world_indicators.compare.title') }}</h3>
                <p>{{ __('world_indicators.compare.description') }}</p>
            </div>
            <div class="controls controls-6">
                <div><label for="compareTopicSelect">{{ __('world_indicators.compare.indicator_group') }}</label><select id="compareTopicSelect"><option value="">{{ __('world_indicators.common.loading') }}</option></select></div>
                <div class="span-2"><label for="compareIndicatorSelect">{{ __('world_indicators.common.indicator') }}</label><select id="compareIndicatorSelect"><option value="">{{ __('world_indicators.common.select_indicator') }}</option></select></div>
                <div><label for="compareModeSelect">{{ __('world_indicators.compare.compare_by') }}</label><select id="compareModeSelect"><option value="country" selected>{{ __('world_indicators.common.country') }}</option><option value="continent">{{ __('world_indicators.common.continent') }}</option></select></div>
                <div><label for="compareAggregationSelect">{{ __('world_indicators.compare.aggregation') }}</label><select id="compareAggregationSelect"><option value="avg" selected>{{ __('world_indicators.compare.average') }}</option><option value="sum">{{ __('world_indicators.compare.sum') }}</option></select></div>
                <div><label for="compareChartTypeSelect">{{ __('world_indicators.compare.chart_type') }}</label><select id="compareChartTypeSelect"><option value="line" selected>{{ __('world_indicators.compare.line') }}</option><option value="bar">{{ __('world_indicators.compare.bar') }}</option></select></div>
                <div><label for="compareYearFrom">{{ __('world_indicators.common.year_from') }}</label><input id="compareYearFrom" type="number" min="1960" step="1"></div>
                <div><label for="compareYearTo">{{ __('world_indicators.common.year_to') }}</label><input id="compareYearTo" type="number" min="1960" step="1"></div>
                <div><label for="compareRangeMinSlider">{{ __('world_indicators.compare.heat_min') }}</label><input id="compareRangeMinSlider" type="range" min="0" max="100" step="1" value="0"></div>
                <div><label for="compareRangeMaxSlider">{{ __('world_indicators.compare.heat_max') }}</label><input id="compareRangeMaxSlider" type="range" min="0" max="100" step="1" value="100"><div class="slider-readout" id="compareRangeLabel">{{ __('world_indicators.compare.color_window', ['min' => 0, 'max' => 100]) }}</div></div>
                <div class="span-3" id="compareCountriesWrap"><label for="compareCountriesSelect">{{ __('world_indicators.compare.countries') }}</label><select id="compareCountriesSelect" multiple size="7"></select></div>
                <div class="span-3" id="compareContinentsWrap" style="display:none;"><label for="compareContinentsSelect">{{ __('world_indicators.compare.continents') }}</label><select id="compareContinentsSelect" multiple size="7"></select></div>
                <div><label for="runCompareBtn">{{ __('world_indicators.compare.compute') }}</label><button id="runCompareBtn" class="btn-primary" type="button">{{ __('world_indicators.compare.run') }}</button></div>
                <div><label for="openCompareModalBtn">{{ __('world_indicators.common.expand') }}</label><button id="openCompareModalBtn" class="btn-secondary" type="button">{{ __('world_indicators.compare.open_modal') }}</button></div>
            </div>
            <div style="padding:0.7rem 1rem 0.75rem;"><div class="status" id="compareStatus">{{ __('world_indicators.compare.initial_status') }}</div></div>
            <div class="compare-grid">
                <div class="viz-card wide"><h4>{{ __('world_indicators.compare.time_series') }}</h4><div class="chart-wrap"><canvas id="compareChart"></canvas></div></div>
                <div class="viz-card"><h4>{{ __('world_indicators.compare.latest_year') }}</h4><div class="chart-wrap"><canvas id="compareLatestChart"></canvas></div></div>
                <div class="viz-card"><h4>{{ __('world_indicators.compare.series_cards') }}</h4><div id="compareSeriesCards" class="series-cards"><div class="empty-box">{{ __('world_indicators.compare.run_for_cards') }}</div></div></div>
            </div>
            <div class="compare-grid">
                <div class="viz-card"><h4>{{ __('world_indicators.compare.data_sheet') }}</h4><div class="table-wrap"><table><thead><tr id="compareTableHeadRow"><th>{{ __('world_indicators.common.year') }}</th></tr></thead><tbody id="compareTableBody"><tr><td>{{ __('world_indicators.compare.none_loaded') }}</td></tr></tbody></table></div></div>
                <div class="viz-card"><h4>{{ __('world_indicators.compare.heat_matrix') }}</h4><div id="compareHeatmap" class="heatmap-wrap"><table><tbody><tr><td style="padding:0.6rem;">{{ __('world_indicators.compare.run_for_heatmap') }}</td></tr></tbody></table></div></div>
            </div>
        </article>
    </section>

    <!-- ====== GALLERY STRIP ====== -->
    @include('partials.gallery-strip')

    @include('partials.public-marketing-footer')

    <div id="vizModal" class="viz-modal" aria-hidden="true">
        <div class="viz-modal-dialog">
            <div class="viz-modal-head"><div><h4 id="vizModalTitle">{{ __('world_indicators.modal.visualization') }}</h4><p id="vizModalSubtitle">{{ __('world_indicators.modal.details') }}</p></div><button class="viz-modal-close" id="vizModalCloseBtn" type="button" aria-label="{{ __('world_indicators.modal.close') }}">x</button></div>
            <div class="viz-modal-body"><div class="chart-wrap"><canvas id="vizModalChart"></canvas></div><div class="data-card"><div class="table-wrap"><table><thead><tr id="vizModalTableHeadRow"><th>{{ __('world_indicators.common.year') }}</th></tr></thead><tbody id="vizModalTableBody"><tr><td>{{ __('world_indicators.common.no_data_available') }}</td></tr></tbody></table></div></div></div>
        </div>
    </div>

    <div id="mapCompareModal" class="viz-modal" aria-hidden="true">
        <div class="viz-modal-dialog map-compare-dialog">
            <div class="viz-modal-head"><div><h4 id="mapCompareModalTitle">{{ __('world_indicators.modal.multi_continent') }}</h4><p id="mapCompareModalSubtitle">{{ __('world_indicators.modal.multi_description') }}</p></div><button class="viz-modal-close" id="mapCompareModalCloseBtn" type="button" aria-label="{{ __('world_indicators.modal.close_geo') }}">x</button></div>
            <div class="viz-modal-body">
                <div id="mapComparePanels" class="continent-map-grid"><div class="empty-box">{{ __('world_indicators.modal.run_multi') }}</div></div>
                <div class="data-card">
                    <h4>{{ __('world_indicators.modal.regional_sheet') }}</h4>
                    <div class="table-wrap"><table><thead><tr><th>{{ __('world_indicators.common.country') }}</th><th>{{ __('world_indicators.common.code') }}</th><th>{{ __('world_indicators.common.value') }}</th></tr></thead><tbody id="mapCompareCountryTableBody"><tr><td colspan="3">{{ __('world_indicators.modal.no_map_comparison') }}</td></tr></tbody></table></div>
                </div>
                <div class="data-card" style="margin-top:0.7rem;">
                    <h4>{{ __('world_indicators.modal.global_comparison') }}</h4>
                    <div class="table-wrap"><table><thead><tr><th>{{ __('world_indicators.common.metric') }}</th><th>{{ __('world_indicators.common.value') }}</th></tr></thead><tbody id="mapCompareGlobalTableBody"><tr><td colspan="2">{{ __('world_indicators.modal.no_global_comparison') }}</td></tr></tbody></table></div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingModal" class="loading-modal" aria-hidden="true" aria-live="polite">
        <div class="loading-box">
            <div class="loading-spinner" aria-hidden="true"></div>
            <h4 class="loading-title">{{ __('world_indicators.common.loading_title') }}</h4>
            <p class="loading-text" id="loadingModalMessage">{{ __('world_indicators.common.loading_wait') }}</p>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/shpjs@6.2.0/dist/shp.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const shapeFilesByRegion = @json($shapeFilesByRegion);
        const countriesByRegion = @json($countriesByRegion);
        const regionLabels = @json($regionLabels);
        const defaultRegion = @json($defaultRegion);
        const appBaseUrl = @json(rtrim(request()->getBaseUrl(), '/'));
        const countryMetricsUrl = @json(route('world.indicators.country-metrics'));
        const worldBankTopicsUrl = @json(route('world.indicators.topics'));
        const worldBankIndicatorsUrl = @json(route('world.indicators.indicators'));
        const worldBankCountriesUrl = @json(route('world.indicators.countries'));
        const worldBankContinentsUrl = @json(route('world.indicators.continents'));
        const worldBankCompareUrl = @json(route('world.indicators.compare'));
        const translations = {{ \Illuminate\Support\Js::from(__('world_indicators.js')) }};

        function t(key, replacements = {}) {
            let value = String(translations[key] ?? key);
            Object.entries(replacements).forEach(([name, replacement]) => {
                value = value.replaceAll(`:${name}`, String(replacement));
            });
            return value;
        }

        const regionSelect = document.getElementById('regionSelect');
        const mapTopicSelect = document.getElementById('mapTopicSelect');
        const mapIndicatorSelect = document.getElementById('mapIndicatorSelect');
        const mapYearInput = document.getElementById('mapYearInput');
        const mapYearSlider = document.getElementById('mapYearSlider');
        const mapContinentsSelect = document.getElementById('mapContinentsSelect');
        const mapRangeMinSlider = document.getElementById('mapRangeMinSlider');
        const mapRangeMaxSlider = document.getElementById('mapRangeMaxSlider');
        const mapRangeLabel = document.getElementById('mapRangeLabel');
        const runMapVizBtn = document.getElementById('runMapVizBtn');
        const runContinentCompareBtn = document.getElementById('runContinentCompareBtn');
        const openMapCompareModalBtn = document.getElementById('openMapCompareModalBtn');
        const resetMapVizBtn = document.getElementById('resetMapVizBtn');
        const mapStatus = document.getElementById('mapStatus');
        const mapLegendTitle = document.getElementById('mapLegendTitle');
        const mapLegendRange = document.getElementById('mapLegendRange');
        const mapTopTableBody = document.getElementById('mapTopTableBody');
        const mapDataTableBody = document.getElementById('mapDataTableBody');

        const countrySelect = document.getElementById('countrySelect');
        const snapshotIndicatorSelect = document.getElementById('snapshotIndicatorSelect');
        const snapshotYearFrom = document.getElementById('snapshotYearFrom');
        const snapshotYearTo = document.getElementById('snapshotYearTo');
        const runSnapshotBtn = document.getElementById('runSnapshotBtn');
        const openSnapshotModalBtn = document.getElementById('openSnapshotModalBtn');
        const snapshotCountry = document.getElementById('snapshotCountry');
        const snapshotHint = document.getElementById('snapshotHint');
        const snapshotHighlights = document.getElementById('snapshotHighlights');
        const snapshotMetrics = document.getElementById('snapshotMetrics');
        const snapshotTableBody = document.getElementById('snapshotTableBody');
        const snapshotChartCanvas = document.getElementById('snapshotChart');

        const compareTopicSelect = document.getElementById('compareTopicSelect');
        const compareIndicatorSelect = document.getElementById('compareIndicatorSelect');
        const compareModeSelect = document.getElementById('compareModeSelect');
        const compareAggregationSelect = document.getElementById('compareAggregationSelect');
        const compareChartTypeSelect = document.getElementById('compareChartTypeSelect');
        const compareYearFromInput = document.getElementById('compareYearFrom');
        const compareYearToInput = document.getElementById('compareYearTo');
        const compareRangeMinSlider = document.getElementById('compareRangeMinSlider');
        const compareRangeMaxSlider = document.getElementById('compareRangeMaxSlider');
        const compareRangeLabel = document.getElementById('compareRangeLabel');
        const compareCountriesWrap = document.getElementById('compareCountriesWrap');
        const compareCountriesSelect = document.getElementById('compareCountriesSelect');
        const compareContinentsWrap = document.getElementById('compareContinentsWrap');
        const compareContinentsSelect = document.getElementById('compareContinentsSelect');
        const runCompareBtn = document.getElementById('runCompareBtn');
        const openCompareModalBtn = document.getElementById('openCompareModalBtn');
        const compareStatus = document.getElementById('compareStatus');
        const compareChartCanvas = document.getElementById('compareChart');
        const compareLatestChartCanvas = document.getElementById('compareLatestChart');
        const compareSeriesCards = document.getElementById('compareSeriesCards');
        const compareTableHeadRow = document.getElementById('compareTableHeadRow');
        const compareTableBody = document.getElementById('compareTableBody');
        const compareHeatmap = document.getElementById('compareHeatmap');

        const vizModal = document.getElementById('vizModal');
        const vizModalCloseBtn = document.getElementById('vizModalCloseBtn');
        const vizModalTitle = document.getElementById('vizModalTitle');
        const vizModalSubtitle = document.getElementById('vizModalSubtitle');
        const vizModalChartCanvas = document.getElementById('vizModalChart');
        const vizModalTableHeadRow = document.getElementById('vizModalTableHeadRow');
        const vizModalTableBody = document.getElementById('vizModalTableBody');
        const mapCompareModal = document.getElementById('mapCompareModal');
        const mapCompareModalCloseBtn = document.getElementById('mapCompareModalCloseBtn');
        const mapCompareModalTitle = document.getElementById('mapCompareModalTitle');
        const mapCompareModalSubtitle = document.getElementById('mapCompareModalSubtitle');
        const mapComparePanels = document.getElementById('mapComparePanels');
        const mapCompareCountryTableBody = document.getElementById('mapCompareCountryTableBody');
        const mapCompareGlobalTableBody = document.getElementById('mapCompareGlobalTableBody');
        const loadingModal = document.getElementById('loadingModal');
        const loadingModalMessage = document.getElementById('loadingModalMessage');

        const state = {
            activeLayers: [],
            featureLayers: [],
            worldBankCountries: [],
            countryByNormName: new Map(),
            countryByIso2: new Map(),
            countryByIso3: new Map(),
            continentByIso2: new Map(),
            iso2ByContinent: new Map(),
            mapValuesByIso2: new Map(),
            mapValuesByNormName: new Map(),
            mapRows: [],
            mapRange: { min: null, max: null },
            mapIndicatorMeta: { label: t('indicator'), unit: '' },
            mapVisibleRangePercent: { min: 0, max: 100 },
            selectedCountryIso2: null,
            selectedCountryName: null,
            comparePayload: null,
            snapshotPayload: null,
            compareVisibleRangePercent: { min: 0, max: 100 },
            mapComparePayloadByContinent: new Map(),
            mapCompareContext: null,
            mapCompareModalMaps: [],
            mapCompareRenderTimer: null,
            mapCompareRenderToken: 0,
            loadingDepth: 0,
            interactionLocked: false,
            lockedControls: [],
            compareChart: null,
            compareLatestChart: null,
            snapshotChart: null,
            modalChart: null,
        };

        const manualCountryAliases = {
            'cape verde': 'CV', 'cabo verde': 'CV', 'cote divoire': 'CI', 'ivory coast': 'CI',
            'dr congo': 'CD', 'democratic republic of the congo': 'CD', 'congo republic': 'CG',
            'swaziland': 'SZ', 'eswatini': 'SZ', 'south korea': 'KR', 'north korea': 'KP',
            'laos': 'LA', 'russia': 'RU', 'turkiye': 'TR', 'vietnam': 'VN',
            'united states of america': 'US', 'iran': 'IR', 'bahamas': 'BS', 'venezuela': 'VE'
        };

        const map = L.map('worldMap', { center: [18, 0], zoom: 2, minZoom: 2, maxZoom: 9, worldCopyJump: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

        function syncNavbarOffset() {
            document.body.style.removeProperty('padding-top');
        }

        function normalizeCountryName(value) {
            return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
        }
        function escapeHtml(value) { const div = document.createElement('div'); div.textContent = String(value ?? ''); return div.innerHTML; }
        function formatValue(value, decimals = 2) { return (value === null || value === undefined || Number.isNaN(Number(value))) ? t('not_available') : Number(value).toLocaleString(@json($locale), { maximumFractionDigits: decimals }); }
        function formatCompact(value) { return (value === null || value === undefined || Number.isNaN(Number(value))) ? t('not_available') : Number(value).toLocaleString(@json($locale), { notation: 'compact', maximumFractionDigits: 2 }); }
        function getSelectedValues(selectEl) { return selectEl ? Array.from(selectEl.selectedOptions).map((option) => option.value).filter(Boolean) : []; }
        function setMapStatus(message, isError = false) { mapStatus.textContent = message; mapStatus.classList.toggle('error', Boolean(isError)); }
        function setCompareStatus(message, isError = false) { compareStatus.textContent = message; compareStatus.classList.toggle('error', Boolean(isError)); }
        const nonBlueScale = ['#DCEEFF', '#2FD0ED', '#1687EA', '#0756C7'];
        const PERF_LIMITS = {
            maxCountrySeriesRequest: 60,
            maxCountryCellsRequest: 6000,
            maxComparisonCellsRender: 1800,
            maxRenderYears: 20,
            maxRenderSeries: 24,
            maxMapCompareContinents: 2,
            maxMapCompareTableRows: 260,
        };

        function getPointValueForYear(seriesItem, year) {
            const points = Array.isArray(seriesItem?.points) ? seriesItem.points : [];
            const point = points.find((entry) => Number(entry?.year) === Number(year)) || null;
            return point?.value ?? null;
        }

        function getSafeComparisonSlice(payload) {
            const years = Array.isArray(payload?.years) ? payload.years : [];
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const cellCount = years.length * series.length;
            if (!years.length || !series.length) {
                return { years, series, reduced: false, cellCount };
            }
            if (cellCount <= PERF_LIMITS.maxComparisonCellsRender) {
                return { years, series, reduced: false, cellCount };
            }
            const slicedYears = years.slice(-Math.min(PERF_LIMITS.maxRenderYears, years.length));
            const rankedSeries = [...series]
                .map((item) => {
                    const latest = getPointValueForYear(item, slicedYears[slicedYears.length - 1]);
                    const latestScore = latest !== null && latest !== undefined && Number.isFinite(Number(latest)) ? Number(latest) : Number.NEGATIVE_INFINITY;
                    return { item, latestScore };
                })
                .sort((a, b) => b.latestScore - a.latestScore)
                .slice(0, Math.min(PERF_LIMITS.maxRenderSeries, series.length))
                .map((entry) => entry.item);
            return {
                years: slicedYears,
                series: rankedSeries,
                reduced: true,
                cellCount,
                fullSeries: series.length,
                fullYears: years.length,
            };
        }

        function getComparisonRenderNote(slice) {
            if (!slice?.reduced) return '';
            return t('large_dataset', { series: slice.fullSeries, years: slice.fullYears, shown_series: slice.series.length, shown_years: slice.years.length });
        }

        function buildPayloadFromSlice(payload, slice) {
            if (!slice?.reduced) return payload;
            const years = Array.isArray(slice.years) ? slice.years : [];
            const series = Array.isArray(slice.series) ? slice.series : [];
            return {
                ...payload,
                years,
                series: series.map((item) => ({
                    ...item,
                    points: years.map((year) => ({
                        year,
                        value: getPointValueForYear(item, year),
                    })),
                })),
            };
        }

        function clampPercent(value) {
            return Math.max(0, Math.min(100, Number(value) || 0));
        }

        function getValueWindowFromPercent(min, max, minPercent, maxPercent) {
            if (min === null || max === null || !Number.isFinite(Number(min)) || !Number.isFinite(Number(max))) {
                return { min: null, max: null };
            }
            const lowerPercent = Math.min(clampPercent(minPercent), clampPercent(maxPercent));
            const upperPercent = Math.max(clampPercent(minPercent), clampPercent(maxPercent));
            if (min === max) return { min, max };
            const distance = max - min;
            return {
                min: min + ((distance * lowerPercent) / 100),
                max: min + ((distance * upperPercent) / 100),
            };
        }

        function valueIsInWindow(value, windowMin, windowMax) {
            if (value === null || value === undefined || !Number.isFinite(Number(value))) return false;
            if (windowMin === null || windowMax === null) return true;
            return Number(value) >= windowMin && Number(value) <= windowMax;
        }

        function getColorByRatio(ratio) {
            if (!Number.isFinite(Number(ratio))) return '#dbe4ef';
            if (ratio < 0.25) return nonBlueScale[0];
            if (ratio < 0.5) return nonBlueScale[1];
            if (ratio < 0.75) return nonBlueScale[2];
            return nonBlueScale[3];
        }

        function showLoadingModal(message = t('loading_wait')) {
            state.loadingDepth += 1;
            loadingModalMessage.textContent = message;
            loadingModal.classList.add('open');
            loadingModal.setAttribute('aria-hidden', 'false');
            syncGlobalLoadingState();
        }

        function setDashboardInitializing(initializing) {
            document.body.classList.toggle('dashboard-initializing', initializing);
            document.querySelectorAll('.viz-shell, .compare-shell').forEach((section) => {
                section.setAttribute('aria-busy', initializing ? 'true' : 'false');
            });
        }

        function hideLoadingModal() {
            state.loadingDepth = Math.max(0, state.loadingDepth - 1);
            syncGlobalLoadingState();
        }

        function syncGlobalLoadingState() {
            const isLoading = state.loadingDepth > 0;
            document.body.classList.toggle('app-loading', isLoading);
            document.body.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            if (isLoading) {
                lockAllInteractiveControls();
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
                return;
            }
            unlockAllInteractiveControls();
            loadingModal.classList.remove('open');
            loadingModal.setAttribute('aria-hidden', 'true');
            loadingModalMessage.textContent = t('loading_wait');
        }

        function setMapInteractionLocked(locked) {
            if (!map) return;
            if (locked) {
                map.dragging.disable();
                map.scrollWheelZoom.disable();
                map.doubleClickZoom.disable();
                map.boxZoom.disable();
                map.keyboard.disable();
                if (map.tap) map.tap.disable();
                return;
            }
            map.dragging.enable();
            map.scrollWheelZoom.enable();
            map.doubleClickZoom.enable();
            map.boxZoom.enable();
            map.keyboard.enable();
            if (map.tap) map.tap.enable();
        }

        function lockAllInteractiveControls() {
            if (state.interactionLocked) return;
            state.lockedControls = [];
            const controls = document.querySelectorAll('button, input, select, textarea');
            controls.forEach((control) => {
                if (loadingModal.contains(control)) return;
                state.lockedControls.push({
                    el: control,
                    disabled: Boolean(control.disabled),
                });
                control.disabled = true;
                control.setAttribute('aria-disabled', 'true');
            });
            setMapInteractionLocked(true);
            state.interactionLocked = true;
        }

        function unlockAllInteractiveControls() {
            if (!state.interactionLocked) return;
            state.lockedControls.forEach((entry) => {
                if (!entry?.el || !entry.el.isConnected) return;
                entry.el.disabled = Boolean(entry.disabled);
                if (!entry.disabled) entry.el.removeAttribute('aria-disabled');
            });
            state.lockedControls = [];
            setMapInteractionLocked(false);
            state.interactionLocked = false;
        }

        function blockUiEventWhileLoading(event) {
            if (state.loadingDepth <= 0) return;
            if (loadingModal.contains(event.target)) return;
            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
        }

        function buildCountryLookups(countries) {
            state.worldBankCountries = countries;
            state.countryByNormName.clear(); state.countryByIso2.clear(); state.countryByIso3.clear();
            state.continentByIso2.clear(); state.iso2ByContinent.clear();
            countries.forEach((country) => {
                const iso2 = String(country.iso2 || '').toUpperCase();
                const iso3 = String(country.iso3 || '').toUpperCase();
                if (iso2.length === 2) {
                    state.countryByNormName.set(normalizeCountryName(country.name), iso2);
                    state.countryByIso2.set(iso2, country);
                    if (iso3.length === 3) state.countryByIso3.set(iso3, iso2);
                    const continent = String(country.continent || '').trim();
                    if (continent) {
                        state.continentByIso2.set(iso2, continent);
                        if (!state.iso2ByContinent.has(continent)) state.iso2ByContinent.set(continent, []);
                        state.iso2ByContinent.get(continent).push(iso2);
                    }
                }
            });
            Object.entries(manualCountryAliases).forEach(([name, iso2]) => state.countryByNormName.set(normalizeCountryName(name), iso2));
        }

        function resolveIso2ByName(countryName) { return state.countryByNormName.get(normalizeCountryName(countryName)) || null; }
        function resolveIso2FromFeature(feature, fallbackCountryName = '') {
            const props = feature?.properties || {};
            const iso2Candidates = [props.ISO_A2, props.iso_a2, props.ISO2, props.iso2, props.WB_A2, props.wb_a2, props.CNTR_ID];
            for (const candidate of iso2Candidates) { const code = String(candidate || '').trim().toUpperCase(); if (code.length === 2) return code; }
            const iso3Candidates = [props.ISO_A3, props.iso_a3, props.ADM0_A3, props.adm0_a3, props.WB_A3, props.wb_a3];
            for (const candidate of iso3Candidates) { const code = String(candidate || '').trim().toUpperCase(); if (code.length === 3 && state.countryByIso3.has(code)) return state.countryByIso3.get(code); }
            return resolveIso2ByName(fallbackCountryName);
        }

        function getCountryName(feature, shapeFile = '') {
            const props = feature?.properties || {};
            const directName = props.NAME || props.name || props.COUNTRY || props.Country || props.ADMIN || props.NAME_EN || props.SOVEREIGNT;
            if (directName) return String(directName);
            const filename = decodeURIComponent((shapeFile.split('/').pop() || '').replace(/\.shp$/i, ''));
            return filename || t('unknown');
        }

        function toFeatureCollection(raw) {
            if (!raw) return { type: 'FeatureCollection', features: [] };
            if (raw.type === 'FeatureCollection') return raw;
            if (raw.type === 'Feature') return { type: 'FeatureCollection', features: [raw] };
            if (Array.isArray(raw)) return { type: 'FeatureCollection', features: raw.flatMap((item) => toFeatureCollection(item).features) };
            if (typeof raw === 'object') return { type: 'FeatureCollection', features: Object.values(raw).flatMap((item) => toFeatureCollection(item).features) };
            return { type: 'FeatureCollection', features: [] };
        }

        function normalizeAssetUrl(url) {
            try {
                const parsed = new URL(url, window.location.href);
                let normalizedPath = `${parsed.pathname}${parsed.search}${parsed.hash}`;
                if (appBaseUrl && normalizedPath.startsWith('/assets/')) normalizedPath = `${appBaseUrl}${normalizedPath}`;
                return new URL(normalizedPath, window.location.origin).toString();
            } catch (error) {
                const raw = String(url || '');
                return raw.startsWith('/') ? `${window.location.origin}${raw}` : raw;
            }
        }

        function resolveShapeFileUrl(shapeUrl) {
            const raw = String(shapeUrl || '').trim(); if (!raw) return '';
            try {
                const resolved = new URL(raw, window.location.href);
                if (/\/assets\/Worldshapes\/.+\.shp$/i.test(resolved.pathname) && resolved.host !== window.location.host) return new URL(`${resolved.pathname}${resolved.search}${resolved.hash}`, window.location.origin).toString();
                if ((resolved.protocol === 'http:' || resolved.protocol === 'https:') && resolved.protocol !== window.location.protocol) resolved.protocol = window.location.protocol;
                return resolved.toString();
            } catch (error) {
                return raw.startsWith('/') ? `${window.location.origin}${raw}` : raw;
            }
        }

        async function loadGeoJsonFromShape(shapeFile) {
            const resolved = normalizeAssetUrl(resolveShapeFileUrl(shapeFile));
            try {
                return await shp(resolved);
            } catch (error) {
                const shpResponse = await fetch(resolved);
                if (!shpResponse.ok) throw new Error(`Could not fetch SHP (${shpResponse.status})`);
                const payload = { shp: await shpResponse.arrayBuffer() };
                for (const ext of ['dbf', 'prj', 'cpg']) {
                    try {
                        const response = await fetch(normalizeAssetUrl(resolved.replace(/\.shp$/i, `.${ext}`)));
                        if (!response.ok) continue;
                        payload[ext] = ext === 'dbf' ? await response.arrayBuffer() : await response.text();
                    } catch (ignored) {}
                }
                return await shp(payload);
            }
        }

        function clearMapLayers() { state.activeLayers.forEach((layer) => map.removeLayer(layer)); state.activeLayers = []; state.featureLayers = []; }
        function getMapValueForLayer(layer) {
            const iso2 = String(layer.__countryIso2 || '').toUpperCase();
            if (iso2 && state.mapValuesByIso2.has(iso2)) return state.mapValuesByIso2.get(iso2);
            const normalizedName = normalizeCountryName(layer.__countryName || '');
            if (normalizedName && state.mapValuesByNormName.has(normalizedName)) return state.mapValuesByNormName.get(normalizedName);
            return null;
        }
        function getChoroplethColor(value, min, max, windowMin = min, windowMax = max) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) return '#dbe4ef';
            if (windowMin !== null && windowMax !== null && !valueIsInWindow(value, windowMin, windowMax)) return '#f1f5f9';
            if (min === null || max === null || min === max) return nonBlueScale[2];
            const ratio = (Number(value) - min) / (max - min);
            return getColorByRatio(ratio);
        }

        function syncSliderPairState(minSlider, maxSlider, changedBy = 'min') {
            let minValue = clampPercent(minSlider.value);
            let maxValue = clampPercent(maxSlider.value);
            if (minValue > maxValue) {
                if (changedBy === 'min') maxValue = minValue;
                else minValue = maxValue;
            }
            minSlider.value = String(minValue);
            maxSlider.value = String(maxValue);
            return { min: minValue, max: maxValue };
        }

        function getCurrentMapValueWindow() {
            return getValueWindowFromPercent(
                state.mapRange.min,
                state.mapRange.max,
                state.mapVisibleRangePercent.min,
                state.mapVisibleRangePercent.max
            );
        }

        function renderMapRangeLabel() {
            const visibleRange = getCurrentMapValueWindow();
            if (state.mapRange.min === null || state.mapRange.max === null || visibleRange.min === null || visibleRange.max === null) {
                mapRangeLabel.textContent = t('visible_range', { min: state.mapVisibleRangePercent.min, max: state.mapVisibleRangePercent.max });
                return;
            }
            mapRangeLabel.textContent = t('visible_range_values', { min: state.mapVisibleRangePercent.min, max: state.mapVisibleRangePercent.max, low: formatCompact(visibleRange.min), high: formatCompact(visibleRange.max) });
        }

        function refreshMapStyles() {
            const comparedIso2 = new Set(getSelectedValues(compareCountriesSelect).map((v) => String(v).toUpperCase()));
            const visibleRange = getCurrentMapValueWindow();
            state.featureLayers.forEach((layer) => {
                const iso2 = String(layer.__countryIso2 || '').toUpperCase();
                const value = getMapValueForLayer(layer);
                const inWindow = valueIsInWindow(value, visibleRange.min, visibleRange.max);
                const fillColor = getChoroplethColor(value, state.mapRange.min, state.mapRange.max, visibleRange.min, visibleRange.max);
                const isSelected = state.selectedCountryIso2 && iso2 && state.selectedCountryIso2 === iso2;
                const isCompared = comparedIso2.has(iso2);
                layer.setStyle({
                    color: isSelected ? '#06152F' : (isCompared ? '#0756C7' : '#334155'),
                    weight: isSelected ? 2.2 : (isCompared ? 1.6 : 0.9),
                    fillColor,
                    fillOpacity: value === null ? 0.2 : (inWindow ? 0.78 : 0.25),
                });
                const valueLabel = value === null ? t('no_data') : formatValue(value);
                const filtered = value !== null && !inWindow ? ` (${t('outside_slider')})` : '';
                layer.bindTooltip(`${escapeHtml(layer.__countryName || t('unknown'))}<br><strong>${escapeHtml(valueLabel + filtered)}</strong>`, { direction: 'auto', sticky: true });
            });
        }

        function renderMapLegend(indicatorLabel, unit, min, max) {
            const visibleRange = getCurrentMapValueWindow();
            if (min === null || max === null) { mapLegendTitle.textContent = t('no_mapped_values', { indicator: indicatorLabel || t('indicator') }); mapLegendRange.innerHTML = `<span>${escapeHtml(t('no_data'))}</span><span>${escapeHtml(t('no_data'))}</span>`; return; }
            mapLegendTitle.textContent = `${indicatorLabel || t('indicator')} ${unit ? `(${unit})` : ''}`;
            mapLegendRange.innerHTML = `<span>${escapeHtml(t('global_range', { min: formatValue(min), max: formatValue(max) }))}</span><span>${escapeHtml(t('slider_range', { min: formatValue(visibleRange.min), max: formatValue(visibleRange.max) }))}</span>`;
        }

        function renderMapTables(rows) {
            const visibleRange = getCurrentMapValueWindow();
            const sortedRows = [...rows].sort((a, b) => { if (a.value === null) return 1; if (b.value === null) return -1; return b.value - a.value; });
            const visibleRows = sortedRows.filter((row) => valueIsInWindow(row.value, visibleRange.min, visibleRange.max));
            const topRows = visibleRows.slice(0, 12);
            mapTopTableBody.innerHTML = topRows.length ? topRows.map((row, index) => `<tr><td>${index + 1}</td><td>${escapeHtml(row.label)}</td><td>${escapeHtml(formatCompact(row.value))}</td></tr>`).join('') : `<tr><td colspan="3">${escapeHtml(t('no_countries_in_range'))}</td></tr>`;
            mapDataTableBody.innerHTML = sortedRows.length ? sortedRows.map((row) => {
                const suffix = row.value !== null && !valueIsInWindow(row.value, visibleRange.min, visibleRange.max) ? ` (${t('filtered')})` : '';
                return `<tr><td>${escapeHtml(row.label)}</td><td>${escapeHtml(row.key || '-')}</td><td>${escapeHtml(formatValue(row.value))}${escapeHtml(suffix)}</td></tr>`;
            }).join('') : `<tr><td colspan="3">${escapeHtml(t('no_data_year_indicator'))}</td></tr>`;
        }

        function applyMapValuesFromPayload(payload, targetYear, sourceLabel = t('map_overlay')) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const byIso2 = new Map(); const byName = new Map(); const rows = [];
            series.forEach((item) => {
                const points = Array.isArray(item?.points) ? item.points : [];
                const pointForYear = points.find((point) => Number(point?.year) === Number(targetYear));
                const value = pointForYear && pointForYear.value !== undefined && pointForYear.value !== null ? Number(pointForYear.value) : null;
                const key = String(item?.key || '').toUpperCase(); const label = String(item?.label || key);
                if (key.length === 2) byIso2.set(key, value);
                byName.set(normalizeCountryName(label), value); rows.push({ key, label, value });
            });
            const values = rows.map((row) => row.value).filter((value) => value !== null && Number.isFinite(value));
            state.mapValuesByIso2 = byIso2;
            state.mapValuesByNormName = byName;
            state.mapRows = rows;
            state.mapIndicatorMeta = {
                label: payload?.indicator?.name || t('indicator'),
                unit: payload?.indicator?.unit || '',
            };
            state.mapRange = { min: values.length ? Math.min(...values) : null, max: values.length ? Math.max(...values) : null };
            renderMapRangeLabel();
            refreshMapStyles();
            renderMapTables(rows);
            renderMapLegend(state.mapIndicatorMeta.label, state.mapIndicatorMeta.unit, state.mapRange.min, state.mapRange.max);
            setMapStatus(t('mapped_countries', { source: sourceLabel, count: rows.length, year: targetYear, values: values.length }));
        }

        function applyRegionCountries(regions) {
            const mergedCountries = regions.flatMap((region) => countriesByRegion[region] || []);
            countrySelect.innerHTML = `<option value="">${escapeHtml(t('select_country'))}</option>`;
            Array.from(new Set(mergedCountries)).sort().forEach((countryName) => {
                const option = document.createElement('option');
                option.value = countryName;
                option.textContent = countryName;
                countrySelect.appendChild(option);
            });
        }

        function normalizeRegionMatch(value) {
            return normalizeCountryName(value).replace(/\s+/g, ' ').trim();
        }

        function getRegionKeyForContinent(continent) {
            const normalizedContinent = normalizeRegionMatch(continent);
            const aliases = {
                'oceania': 'oceanica',
                'antarctica': 'antartica',
            };
            const target = aliases[normalizedContinent] || normalizedContinent;
            return Object.keys(shapeFilesByRegion).find((regionKey) => {
                const regionLabel = regionLabels[regionKey] || regionKey;
                return normalizeRegionMatch(regionLabel) === target || normalizeRegionMatch(regionKey) === target;
            }) || null;
        }

        async function loadRegions(regions, statusLabel = t('selected_regions')) {
            const uniqueRegions = Array.from(new Set((regions || []).filter((region) => String(region || '').trim() !== '')));
            clearMapLayers();
            applyRegionCountries(uniqueRegions);
            setMapStatus(t('loading_shapefiles', { region: statusLabel }));
            const shapeFiles = uniqueRegions.flatMap((region) => shapeFilesByRegion[region] || []);
            if (!shapeFiles.length) { setMapStatus(t('no_shapefiles'), true); return; }
            let loadedCount = 0; let failedCount = 0; let lastError = null;
            for (const shapeFile of shapeFiles) {
                try {
                    const geojson = await loadGeoJsonFromShape(shapeFile);
                    const featureCollection = toFeatureCollection(geojson);
                    if (!featureCollection.features.length) continue;
                    const layerGroup = L.geoJSON(featureCollection, {
                        onEachFeature: (feature, leafletLayer) => {
                            const countryName = getCountryName(feature, shapeFile);
                            const countryIso2 = resolveIso2FromFeature(feature, countryName);
                            leafletLayer.__countryName = countryName;
                            leafletLayer.__countryIso2 = countryIso2;
                            leafletLayer.on('click', () => selectCountry(countryName, countryIso2, true));
                            state.featureLayers.push(leafletLayer);
                        },
                    }).addTo(map);
                    state.activeLayers.push(layerGroup);
                    loadedCount++;
                } catch (error) {
                    failedCount++;
                    lastError = error;
                    console.error('Could not load shapefile:', shapeFile, error);
                }
            }
            if (!loadedCount) {
                const reason = lastError && lastError.message ? t('last_error', { error: lastError.message }) : '';
                setMapStatus(t('all_shapefiles_failed', { reason }), true);
                return;
            }
            refreshMapStyles();
            const boundsLayer = L.featureGroup(state.activeLayers);
            if (boundsLayer.getBounds().isValid()) map.fitBounds(boundsLayer.getBounds(), { padding: [20, 20] });
            setMapStatus(failedCount ? t('shapefiles_partial', { loaded: loadedCount, failed: failedCount }) : t('shapefiles_loaded', { loaded: loadedCount }));
        }

        async function loadRegion(region) {
            await loadRegions([region], regionLabels[region] || region);
        }
        async function fetchComparisonPayload(params) {
            const url = new URL(worldBankCompareUrl, window.location.origin);
            url.searchParams.set('indicator_id', params.indicatorId);
            url.searchParams.set('compare_mode', params.compareMode);
            url.searchParams.set('year_from', String(params.yearFrom));
            url.searchParams.set('year_to', String(params.yearTo));
            if (params.compareMode === 'country') {
                params.countries.forEach((iso2) => url.searchParams.append('countries[]', iso2));
            } else {
                params.continents.forEach((continent) => url.searchParams.append('continents[]', continent));
                url.searchParams.set('aggregation', params.aggregation || 'avg');
            }
            const response = await fetch(url.toString());
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        }

        function getIso2CodesForRegion(region) {
            const countryNames = countriesByRegion[region] || [];
            const iso2Codes = countryNames.map((name) => resolveIso2ByName(name)).filter(Boolean).map((iso2) => String(iso2).toUpperCase());
            return Array.from(new Set(iso2Codes));
        }

        function getIso2CodesForContinents(continents) {
            const iso2Codes = continents.flatMap((continent) => state.iso2ByContinent.get(continent) || []);
            return Array.from(new Set(iso2Codes.map((iso2) => String(iso2).toUpperCase())));
        }

        function getRegionKeysForContinents(continents) {
            return Array.from(new Set(continents.map((continent) => getRegionKeyForContinent(continent)).filter(Boolean)));
        }

        function buildContinentPayloadFromMapPayload(payload, continent) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const continentSeries = series.filter((item) => state.continentByIso2.get(String(item?.key || '').toUpperCase()) === continent);
            return {
                ...payload,
                compare_mode: 'country',
                series: continentSeries,
            };
        }

        async function runMapVisualization(options = {}) {
            if (state.loadingDepth > 0 && !options.allowWhileLoading) return;
            const indicatorId = String(mapIndicatorSelect.value || '').trim();
            const region = String(regionSelect.value || '').trim();
            const selectedContinents = getSelectedValues(mapContinentsSelect);
            const year = parseInt(mapYearInput.value || '', 10);
            if (!indicatorId) { setMapStatus(t('select_map_indicator'), true); return; }
            if (!Number.isFinite(year)) { setMapStatus(t('valid_map_year'), true); return; }
            if (selectedContinents.length > PERF_LIMITS.maxMapCompareContinents) {
                setMapStatus(t('continent_limit', { count: PERF_LIMITS.maxMapCompareContinents }), true);
                return;
            }
            mapYearSlider.value = String(year);
            runMapVizBtn.disabled = true;
            runContinentCompareBtn.disabled = true;
            openMapCompareModalBtn.disabled = true;
            const useGlobalLoading = options.showGlobalLoading !== false;
            if (useGlobalLoading) showLoadingModal(t('loading_map_indicator'));
            let countryCodes = [];
            let sourceLabel = t('geo_map');
            try {
                if (selectedContinents.length >= 2) {
                    const regionKeys = getRegionKeysForContinents(selectedContinents);
                    if (regionKeys.length < 2) { setMapStatus(t('continents_missing_shapes'), true); return; }
                    await loadRegions(regionKeys, selectedContinents.join(` ${t('versus')} `));
                    countryCodes = getIso2CodesForContinents(selectedContinents);
                    sourceLabel = `${t('geo_map')} (${selectedContinents.join(` ${t('versus')} `)})`;
                } else {
                    countryCodes = getIso2CodesForRegion(region);
                }
                if (countryCodes.length < 2) { setMapStatus(t('not_enough_mapped_countries'), true); return; }
                setMapStatus(t('loading_map_values'));
                const payload = await fetchComparisonPayload({ indicatorId, compareMode: 'country', countries: countryCodes, continents: [], yearFrom: year, yearTo: year, aggregation: 'avg' });
                applyMapValuesFromPayload(payload, year, sourceLabel);
                if (selectedContinents.length >= 2) {
                    state.mapComparePayloadByContinent = new Map();
                    selectedContinents.forEach((continent) => {
                        state.mapComparePayloadByContinent.set(continent, buildContinentPayloadFromMapPayload(payload, continent));
                    });
                    state.mapCompareContext = { continents: selectedContinents, year, indicatorName: payload?.indicator?.name || indicatorId, indicatorUnit: payload?.indicator?.unit || '' };
                    await renderMapCompareModalContent();
                } else {
                    state.mapComparePayloadByContinent = new Map();
                    state.mapCompareContext = null;
                    if (mapCompareModal.classList.contains('open')) closeMapCompareModal();
                }
            } catch (error) {
                console.error('Map visualization failed', error);
                setMapStatus(t('map_values_failed'), true);
            } finally {
                if (useGlobalLoading) hideLoadingModal();
                runMapVizBtn.disabled = false;
                runContinentCompareBtn.disabled = false;
                openMapCompareModalBtn.disabled = false;
            }
        }

        async function runContinentComparison(options = {}) {
            if (state.loadingDepth > 0 && !options.allowWhileLoading) return false;
            const indicatorId = String(options.indicatorId || mapIndicatorSelect.value || '').trim();
            const year = Number.isFinite(Number(options.year)) ? Number(options.year) : parseInt(mapYearInput.value || '', 10);
            const continents = Array.isArray(options.continents) && options.continents.length ? options.continents : getSelectedValues(mapContinentsSelect);
            Array.from(mapContinentsSelect.options).forEach((option) => { option.selected = continents.includes(option.value); });
            if (!indicatorId) { setMapStatus(t('select_map_indicator'), true); return false; }
            if (!Number.isFinite(year)) { setMapStatus(t('valid_map_year'), true); return false; }
            if (continents.length < 2) { setMapStatus(t('select_two_continents_geo'), true); return false; }
            if (continents.length > PERF_LIMITS.maxMapCompareContinents) { setMapStatus(t('continent_limit', { count: PERF_LIMITS.maxMapCompareContinents }), true); return false; }
            const regionKeys = getRegionKeysForContinents(continents);
            if (regionKeys.length < 2) { setMapStatus(t('continents_missing_shapes'), true); return false; }
            const countryCodes = getIso2CodesForContinents(continents);
            if (countryCodes.length < 2) { setMapStatus(t('no_country_mappings'), true); return false; }

            mapYearInput.value = String(year);
            mapYearSlider.value = String(year);
            runMapVizBtn.disabled = true;
            runContinentCompareBtn.disabled = true;
            openMapCompareModalBtn.disabled = true;
            showLoadingModal(t('building_multi_maps'));

            try {
                await loadRegions(regionKeys, continents.join(` ${t('versus')} `));
                const payload = await fetchComparisonPayload({
                    indicatorId,
                    compareMode: 'country',
                    countries: countryCodes,
                    continents: [],
                    yearFrom: year,
                    yearTo: year,
                    aggregation: 'avg',
                });
                applyMapValuesFromPayload(payload, year, `${t('geo_map')} (${continents.join(` ${t('versus')} `)})`);
                state.mapComparePayloadByContinent = new Map();
                continents.forEach((continent) => {
                    state.mapComparePayloadByContinent.set(continent, buildContinentPayloadFromMapPayload(payload, continent));
                });
                state.mapCompareContext = { continents, year, indicatorName: payload?.indicator?.name || indicatorId, indicatorUnit: payload?.indicator?.unit || '' };
                if (options.openModal) openMapCompareModal();
                await renderMapCompareModalContent();
                return true;
            } catch (error) {
                console.error('Continent comparison failed', error);
                setMapStatus(t('multi_map_failed'), true);
                return false;
            } finally {
                hideLoadingModal();
                runMapVizBtn.disabled = false;
                runContinentCompareBtn.disabled = false;
                openMapCompareModalBtn.disabled = false;
            }
        }

        function renderSnapshotSources(payload) {
            const sources = Array.isArray(payload?.sources) ? payload.sources : [];
            if (!sources.length) {
                snapshotMetrics.innerHTML = `<div class="empty-box">${escapeHtml(t('no_source_data'))}</div>`;
                snapshotHighlights.innerHTML = `<div class="empty-box">${escapeHtml(t('no_source_highlights'))}</div>`;
                return;
            }
            const highlights = [];
            sources.forEach((source) => (source.metrics || []).slice(0, 2).forEach((metric) => highlights.push({ label: metric.label, value: metric.value })));
            snapshotHighlights.innerHTML = highlights.length ? highlights.slice(0, 6).map((item) => `<div class="highlight-card"><div class="k">${escapeHtml(item.label)}</div><div class="v">${escapeHtml(item.value)}</div></div>`).join('') : `<div class="empty-box">${escapeHtml(t('no_highlights'))}</div>`;
            snapshotMetrics.innerHTML = sources.map((source) => `
                <div class="metric-source">
                    <h5>${escapeHtml(source.label || t('source'))}</h5>
                    <p class="note">${escapeHtml(source.note || '')}</p>
                    <ul class="metric-list">${(source.metrics || []).map((metric) => `<li><span>${escapeHtml(metric.label)}</span><strong>${escapeHtml(metric.value)}</strong></li>`).join('')}</ul>
                </div>
            `).join('');
        }

        function destroyChartInstance(chartRefName) {
            if (state[chartRefName]) {
                state[chartRefName].destroy();
                state[chartRefName] = null;
            }
        }

        function renderTrendChart(canvas, chartRefName, payload, chartType = 'line') {
            if (!canvas || typeof Chart === 'undefined') return;
            const years = Array.isArray(payload?.years) ? payload.years.map((year) => String(year)) : [];
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const palette = ['#06152F', '#0A2450', '#0A3479', '#0756C7', '#0A6ED1', '#1687EA', '#2FD0ED', '#DCEEFF'];
            const datasets = series.map((item, index) => {
                const color = palette[index % palette.length];
                return {
                    label: item.label,
                    data: Array.isArray(item.points) ? item.points.map((point) => point?.value ?? null) : [],
                    borderColor: color,
                    backgroundColor: chartType === 'line' ? `${color}33` : color,
                    borderWidth: 2,
                    tension: 0.25,
                    spanGaps: true,
                    fill: chartType === 'line',
                };
            });
            destroyChartInstance(chartRefName);
            state[chartRefName] = new Chart(canvas, {
                type: chartType,
                data: { labels: years, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'nearest', intersect: false },
                    scales: { y: { beginAtZero: false } },
                },
            });
        }

        function renderSnapshotTable(payload, countryIso2) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const targetSeries = series.find((item) => String(item.key || '').toUpperCase() === String(countryIso2 || '').toUpperCase()) || series[0];
            const points = Array.isArray(targetSeries?.points) ? targetSeries.points : [];
            snapshotTableBody.innerHTML = points.length ? points.map((point) => `<tr><td>${escapeHtml(point.year)}</td><td style="text-align:right;">${escapeHtml(formatValue(point.value))}</td></tr>`).join('') : `<tr><td colspan="2">${escapeHtml(t('no_trend_points'))}</td></tr>`;
        }

        async function runSnapshotTrend() {
            if (state.loadingDepth > 0) return;
            const selectedCountryName = state.selectedCountryName || countrySelect.value;
            if (!selectedCountryName) { snapshotHint.textContent = t('select_country_first'); return; }
            const iso2 = state.selectedCountryIso2 || resolveIso2ByName(selectedCountryName);
            if (!iso2) { snapshotHint.textContent = t('country_code_failed'); return; }
            const indicatorId = String(snapshotIndicatorSelect.value || '').trim();
            if (!indicatorId) { snapshotHint.textContent = t('select_trend_indicator'); return; }
            const yearFrom = parseInt(snapshotYearFrom.value || '', 10);
            const yearTo = parseInt(snapshotYearTo.value || '', 10);
            if (!Number.isFinite(yearFrom) || !Number.isFinite(yearTo)) { snapshotHint.textContent = t('valid_snapshot_years'); return; }
            snapshotHint.textContent = t('loading_snapshot_trend');
            runSnapshotBtn.disabled = true;
            showLoadingModal(t('loading_country_trend'));
            try {
                const payload = await fetchComparisonPayload({ indicatorId, compareMode: 'country', countries: [iso2], continents: [], yearFrom, yearTo, aggregation: 'avg' });
                state.snapshotPayload = payload;
                renderTrendChart(snapshotChartCanvas, 'snapshotChart', payload, 'line');
                renderSnapshotTable(payload, iso2);
                snapshotHint.textContent = t('snapshot_loaded', { country: selectedCountryName, indicator: payload?.indicator?.name || indicatorId });
            } catch (error) {
                console.error('Snapshot trend failed', error);
                snapshotHint.textContent = t('snapshot_failed');
            } finally {
                hideLoadingModal();
                runSnapshotBtn.disabled = false;
            }
        }

        async function selectCountry(countryName, iso2Hint = null, pinToCompare = false) {
            if (state.loadingDepth > 0) return;
            if (!countryName) return;
            const iso2 = iso2Hint || resolveIso2ByName(countryName);
            state.selectedCountryName = countryName;
            state.selectedCountryIso2 = iso2 || null;
            snapshotCountry.textContent = countryName;
            snapshotHint.textContent = t('loading_source_metrics');
            refreshMapStyles();

            if (countrySelect.value !== countryName) {
                const existingOption = Array.from(countrySelect.options).find((option) => option.value === countryName);
                if (!existingOption) {
                    const option = document.createElement('option');
                    option.value = countryName;
                    option.textContent = countryName;
                    countrySelect.appendChild(option);
                }
                countrySelect.value = countryName;
            }

            if (pinToCompare && iso2 && compareCountriesSelect) {
                const targetOption = Array.from(compareCountriesSelect.options).find((option) => option.value === iso2);
                if (targetOption) targetOption.selected = true;
            }

            showLoadingModal(t('loading_profile_metrics'));
            try {
                const response = await fetch(`${countryMetricsUrl}?country=${encodeURIComponent(countryName)}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const payload = await response.json();
                renderSnapshotSources(payload);
                snapshotHint.textContent = t('source_metrics_loaded');
            } catch (error) {
                console.error('Country metrics load failed', error);
                snapshotMetrics.innerHTML = `<div class="empty-box">${escapeHtml(t('country_metrics_failed'))}</div>`;
                snapshotHint.textContent = t('source_metrics_failed');
            } finally {
                hideLoadingModal();
            }
            await runSnapshotTrend();
        }

        function getSeriesRowsForYear(payload, year) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            return series.map((item) => {
                const points = Array.isArray(item?.points) ? item.points : [];
                const targetPoint = points.find((point) => Number(point?.year) === Number(year)) || null;
                return {
                    key: String(item?.key || '').toUpperCase(),
                    label: String(item?.label || item?.key || t('unknown')),
                    value: targetPoint && targetPoint.value !== undefined && targetPoint.value !== null ? Number(targetPoint.value) : null,
                };
            });
        }

        function getCompareValueWindow(payload) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const allValues = series.flatMap((item) => (item.points || []).map((point) => point?.value)).filter((value) => value !== null && value !== undefined && Number.isFinite(Number(value)));
            const min = allValues.length ? Math.min(...allValues) : null;
            const max = allValues.length ? Math.max(...allValues) : null;
            const visible = getValueWindowFromPercent(min, max, state.compareVisibleRangePercent.min, state.compareVisibleRangePercent.max);
            return { min, max, visibleMin: visible.min, visibleMax: visible.max };
        }

        function renderCompareRangeLabel(payload = state.comparePayload) {
            if (!payload) {
                compareRangeLabel.textContent = t('color_window', { min: state.compareVisibleRangePercent.min, max: state.compareVisibleRangePercent.max });
                return;
            }
            const range = getCompareValueWindow(payload);
            if (range.min === null || range.max === null || range.visibleMin === null || range.visibleMax === null) {
                compareRangeLabel.textContent = t('color_window', { min: state.compareVisibleRangePercent.min, max: state.compareVisibleRangePercent.max });
                return;
            }
            compareRangeLabel.textContent = t('color_window_values', { min: state.compareVisibleRangePercent.min, max: state.compareVisibleRangePercent.max, low: formatCompact(range.visibleMin), high: formatCompact(range.visibleMax) });
        }

        function destroyMapCompareModalMaps() {
            state.mapCompareModalMaps.forEach((miniMap) => {
                try {
                    miniMap.remove();
                } catch (error) {
                    console.warn('Could not dispose map instance', error);
                }
            });
            state.mapCompareModalMaps = [];
        }

        async function drawContinentMiniMap(mapContainerId, regionKey, rows, min, max, windowMin, windowMax) {
            const mapContainer = document.getElementById(mapContainerId);
            if (!mapContainer) return;
            const continentMap = L.map(mapContainer, {
                center: [18, 0],
                zoom: 2,
                minZoom: 2,
                maxZoom: 8,
                zoomControl: false,
                attributionControl: false,
                worldCopyJump: true,
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(continentMap);

            const valueByIso2 = new Map();
            const valueByName = new Map();
            rows.forEach((row) => {
                const iso2 = String(row.key || '').toUpperCase();
                if (iso2.length === 2) valueByIso2.set(iso2, row.value);
                valueByName.set(normalizeCountryName(row.label), row.value);
            });

            const layers = [];
            const shapeFiles = shapeFilesByRegion[regionKey] || [];
            for (const shapeFile of shapeFiles) {
                try {
                    const geojson = await loadGeoJsonFromShape(shapeFile);
                    const featureCollection = toFeatureCollection(geojson);
                    if (!featureCollection.features.length) continue;
                    const layer = L.geoJSON(featureCollection, {
                        style: (feature) => {
                            const countryName = getCountryName(feature, shapeFile);
                            const iso2 = resolveIso2FromFeature(feature, countryName);
                            const value = iso2 && valueByIso2.has(iso2) ? valueByIso2.get(iso2) : valueByName.get(normalizeCountryName(countryName));
                            return {
                                color: '#334155',
                                weight: 0.7,
                                fillColor: getChoroplethColor(value, min, max, windowMin, windowMax),
                                fillOpacity: value === null ? 0.18 : (valueIsInWindow(value, windowMin, windowMax) ? 0.78 : 0.23),
                            };
                        },
                        onEachFeature: (feature, layerRef) => {
                            const countryName = getCountryName(feature, shapeFile);
                            const iso2 = resolveIso2FromFeature(feature, countryName);
                            const value = iso2 && valueByIso2.has(iso2) ? valueByIso2.get(iso2) : valueByName.get(normalizeCountryName(countryName));
                            const valueLabel = value === null || value === undefined ? t('no_data') : formatValue(value);
                            layerRef.bindTooltip(`${escapeHtml(countryName)}<br><strong>${escapeHtml(valueLabel)}</strong>`, { sticky: true });
                        },
                    }).addTo(continentMap);
                    layers.push(layer);
                } catch (error) {
                    console.error('Could not load mini map shapefile', shapeFile, error);
                }
            }

            if (!layers.length) {
                continentMap.remove();
                mapContainer.innerHTML = `<div class="empty-box" style="margin:0.6rem;">${escapeHtml(t('no_continent_shapes'))}</div>`;
                return;
            }

            const bounds = L.featureGroup(layers).getBounds();
            continentMap.__autoBounds = bounds && bounds.isValid() ? bounds : null;
            if (continentMap.__autoBounds) {
                continentMap.fitBounds(continentMap.__autoBounds, { padding: [10, 10], animate: false });
            }
            state.mapCompareModalMaps.push(continentMap);
        }

        async function renderMapCompareModalContent() {
            destroyMapCompareModalMaps();
            const context = state.mapCompareContext;
            if (!context || !Array.isArray(context.continents) || !context.continents.length) {
                mapCompareModalTitle.textContent = t('multi_continent_title');
                mapCompareModalSubtitle.textContent = t('multi_continent_description');
                mapComparePanels.innerHTML = `<div class="empty-box">${escapeHtml(t('run_multi_continent'))}</div>`;
                mapCompareCountryTableBody.innerHTML = `<tr><td colspan="3">${escapeHtml(t('no_map_comparison'))}</td></tr>`;
                mapCompareGlobalTableBody.innerHTML = `<tr><td colspan="2">${escapeHtml(t('no_global_comparison'))}</td></tr>`;
                return;
            }

            const allRows = context.continents.flatMap((continent) => {
                const payload = state.mapComparePayloadByContinent.get(continent);
                return getSeriesRowsForYear(payload, context.year).map((row) => ({ ...row, continent }));
            });
            const finiteValues = allRows.map((row) => row.value).filter((value) => value !== null && Number.isFinite(Number(value)));
            const globalMin = finiteValues.length ? Math.min(...finiteValues) : null;
            const globalMax = finiteValues.length ? Math.max(...finiteValues) : null;
            const mapWindow = getCurrentMapValueWindow();

            mapCompareModalTitle.textContent = t('continent_maps_title', { indicator: context.indicatorName || t('indicator') });
            mapCompareModalSubtitle.textContent = t('continent_maps_subtitle', { continents: context.continents.join(` ${t('versus')} `), year: context.year, unit: context.indicatorUnit ? t('unit_suffix', { unit: context.indicatorUnit }) : '' });

            mapComparePanels.innerHTML = context.continents.map((continent, index) => `
                <article class="continent-map-card">
                    <div class="continent-map-head"><h5>${escapeHtml(continent)}</h5><span>${escapeHtml(t('year_value', { year: context.year }))}</span></div>
                    <div id="continentMiniMap_${index}" class="continent-map"></div>
                    <div class="continent-map-meta">
                        <div class="continent-map-scale"></div>
                        <div class="continent-map-range"><span>${escapeHtml(t('global_range', { min: formatValue(globalMin), max: formatValue(globalMax) }))}</span><span>${escapeHtml(t('slider_range', { min: formatValue(mapWindow.min), max: formatValue(mapWindow.max) }))}</span></div>
                    </div>
                    <div class="table-wrap continent-mini-table"><table><thead><tr><th>${escapeHtml(t('country'))}</th><th>${escapeHtml(t('value'))}</th></tr></thead><tbody id="continentMiniRows_${index}"><tr><td colspan="2">${escapeHtml(t('loading'))}</td></tr></tbody></table></div>
                </article>
            `).join('');

            const sortedRows = [...allRows].sort((a, b) => {
                if (a.value === null) return 1;
                if (b.value === null) return -1;
                return Number(b.value) - Number(a.value);
            });
            const truncatedRows = sortedRows.slice(0, PERF_LIMITS.maxMapCompareTableRows);
            const truncated = sortedRows.length > truncatedRows.length;
            const truncateNote = truncated ? `<tr><td colspan="3" style="background:#EFF8FF;color:#0A2450;font-weight:700;">${escapeHtml(t('large_result_set', { shown: truncatedRows.length, total: sortedRows.length }))}</td></tr>` : '';
            mapCompareCountryTableBody.innerHTML = truncatedRows.length
                ? `${truncateNote}${truncatedRows.map((row) => `<tr><td>${escapeHtml(row.label)}</td><td>${escapeHtml(row.key || '-')}</td><td>${escapeHtml(formatValue(row.value))}</td></tr>`).join('')}`
                : `<tr><td colspan="3">${escapeHtml(t('no_selected_continent_data'))}</td></tr>`;

            const validRows = allRows.filter((row) => row.value !== null && Number.isFinite(Number(row.value)));
            const sum = validRows.reduce((acc, row) => acc + Number(row.value), 0);
            const avg = validRows.length ? (sum / validRows.length) : null;
            const topRow = validRows.length ? [...validRows].sort((a, b) => Number(b.value) - Number(a.value))[0] : null;
            const bottomRow = validRows.length ? [...validRows].sort((a, b) => Number(a.value) - Number(b.value))[0] : null;
            const continentStats = context.continents.map((continent) => {
                const rows = validRows.filter((row) => row.continent === continent);
                const total = rows.reduce((acc, row) => acc + Number(row.value), 0);
                return {
                    continent,
                    count: rows.length,
                    avg: rows.length ? (total / rows.length) : null,
                };
            });
            const rankedContinentStats = continentStats.filter((row) => row.avg !== null).sort((a, b) => Number(b.avg) - Number(a.avg));
            const bestContinent = rankedContinentStats[0] || null;
            const worstContinent = rankedContinentStats[rankedContinentStats.length - 1] || null;
            mapCompareGlobalTableBody.innerHTML = `
                <tr><td>${escapeHtml(t('compared_continents'))}</td><td>${escapeHtml(context.continents.join(', '))}</td></tr>
                <tr><td>${escapeHtml(t('selected_year'))}</td><td>${escapeHtml(context.year)}</td></tr>
                <tr><td>${escapeHtml(t('countries_in_sheet'))}</td><td>${escapeHtml(allRows.length)}</td></tr>
                <tr><td>${escapeHtml(t('countries_with_values'))}</td><td>${escapeHtml(validRows.length)}</td></tr>
                <tr><td>${escapeHtml(t('global_min'))}</td><td>${escapeHtml(formatValue(globalMin))}</td></tr>
                <tr><td>${escapeHtml(t('global_max'))}</td><td>${escapeHtml(formatValue(globalMax))}</td></tr>
                <tr><td>${escapeHtml(t('global_average'))}</td><td>${escapeHtml(formatValue(avg))}</td></tr>
                <tr><td>${escapeHtml(t('top_country'))}</td><td>${escapeHtml(topRow ? `${topRow.label} (${topRow.continent}) - ${formatValue(topRow.value)}` : t('not_available'))}</td></tr>
                <tr><td>${escapeHtml(t('lowest_country'))}</td><td>${escapeHtml(bottomRow ? `${bottomRow.label} (${bottomRow.continent}) - ${formatValue(bottomRow.value)}` : t('not_available'))}</td></tr>
                <tr><td>${escapeHtml(t('best_continent'))}</td><td>${escapeHtml(bestContinent ? `${bestContinent.continent} - ${formatValue(bestContinent.avg)}` : t('not_available'))}</td></tr>
                <tr><td>${escapeHtml(t('lowest_continent'))}</td><td>${escapeHtml(worstContinent ? `${worstContinent.continent} - ${formatValue(worstContinent.avg)}` : t('not_available'))}</td></tr>
                <tr><td>${escapeHtml(t('slider_window'))}</td><td>${escapeHtml(t('range_values', { min: formatValue(mapWindow.min), max: formatValue(mapWindow.max) }))}</td></tr>
            `;

            const drawJobs = context.continents.map(async (continent, index) => {
                const payload = state.mapComparePayloadByContinent.get(continent);
                const rows = getSeriesRowsForYear(payload, context.year).filter((row) => row.value !== null && Number.isFinite(Number(row.value))).sort((a, b) => b.value - a.value);
                const miniRowsBody = document.getElementById(`continentMiniRows_${index}`);
                miniRowsBody.innerHTML = rows.length ? rows.slice(0, 12).map((row) => `<tr><td>${escapeHtml(row.label)}</td><td>${escapeHtml(formatCompact(row.value))}</td></tr>`).join('') : `<tr><td colspan="2">${escapeHtml(t('no_values_year'))}</td></tr>`;
                const regionKey = getRegionKeyForContinent(continent);
                if (!regionKey) {
                    const mapContainer = document.getElementById(`continentMiniMap_${index}`);
                    if (mapContainer) mapContainer.innerHTML = `<div class="empty-box" style="margin:0.6rem;">${escapeHtml(t('no_matching_shapefile'))}</div>`;
                    return;
                }
                await drawContinentMiniMap(`continentMiniMap_${index}`, regionKey, rows, globalMin, globalMax, mapWindow.min, mapWindow.max);
            });
            await Promise.all(drawJobs);
            if (mapCompareModal.classList.contains('open')) refreshMapCompareModalMaps();
        }

        function refreshMapCompareModalMaps() {
            if (!state.mapCompareModalMaps.length) return;
            [0, 90, 220, 460].forEach((delayMs) => {
                setTimeout(() => {
                    state.mapCompareModalMaps.forEach((miniMap) => {
                        try {
                            miniMap.invalidateSize(true);
                            if (miniMap.__autoBounds && miniMap.__autoBounds.isValid()) {
                                miniMap.fitBounds(miniMap.__autoBounds, { padding: [10, 10], animate: false });
                            }
                        } catch (error) {
                            console.warn('Could not refresh modal map viewport', error);
                        }
                    });
                }, delayMs);
            });
        }

        function openMapCompareModal() {
            mapCompareModal.classList.add('open');
            mapCompareModal.setAttribute('aria-hidden', 'false');
            refreshMapCompareModalMaps();
        }

        function closeMapCompareModal() {
            mapCompareModal.classList.remove('open');
            mapCompareModal.setAttribute('aria-hidden', 'true');
            destroyMapCompareModalMaps();
            if (state.mapCompareRenderTimer) {
                clearTimeout(state.mapCompareRenderTimer);
                state.mapCompareRenderTimer = null;
            }
            state.mapCompareRenderToken += 1;
        }

        function scheduleMapCompareModalRender(delayMs = 180) {
            if (!mapCompareModal.classList.contains('open') || !state.mapCompareContext) return;
            state.mapCompareRenderToken += 1;
            const token = state.mapCompareRenderToken;
            if (state.mapCompareRenderTimer) clearTimeout(state.mapCompareRenderTimer);
            state.mapCompareRenderTimer = setTimeout(async () => {
                state.mapCompareRenderTimer = null;
                if (token !== state.mapCompareRenderToken) return;
                if (state.loadingDepth > 0) return;
                showLoadingModal(t('refreshing_map_comparison'));
                try {
                    await renderMapCompareModalContent();
                } catch (error) {
                    console.error('Could not refresh map comparison modal', error);
                } finally {
                    hideLoadingModal();
                }
            }, delayMs);
        }

        function syncCompareMode() {
            const mode = compareModeSelect.value || 'country';
            const isContinent = mode === 'continent';
            compareCountriesWrap.style.display = isContinent ? 'none' : 'block';
            compareContinentsWrap.style.display = isContinent ? 'block' : 'none';
            compareAggregationSelect.disabled = !isContinent;
        }

        function renderCompareTable(payload) {
            const slice = getSafeComparisonSlice(payload);
            const years = slice.years;
            const series = slice.series;
            compareTableHeadRow.innerHTML = `<th>${escapeHtml(t('year'))}</th>`;
            series.forEach((item) => {
                const th = document.createElement('th');
                th.textContent = item.label;
                compareTableHeadRow.appendChild(th);
            });
            if (!(years.length && series.length)) {
                compareTableBody.innerHTML = `<tr><td>${escapeHtml(t('no_comparison_data'))}</td></tr>`;
                return;
            }
            const note = getComparisonRenderNote(slice);
            const noteRow = note ? `<tr><td colspan="${series.length + 1}" style="background:#EFF8FF;color:#0A2450;font-weight:700;">${escapeHtml(note)}</td></tr>` : '';
            compareTableBody.innerHTML = `
                ${noteRow}
                ${years.map((year) => `
                    <tr><td>${escapeHtml(year)}</td>${series.map((item) => `<td>${escapeHtml(formatValue(getPointValueForYear(item, year)))}</td>`).join('')}</tr>
                `).join('')}
            `;
        }

        function getLatestSeriesValues(payload) {
            const years = Array.isArray(payload?.years) ? payload.years : [];
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const latestYear = years.length ? years[years.length - 1] : null;
            return series.map((item) => {
                const points = Array.isArray(item.points) ? item.points : [];
                const latestPoint = points.find((point) => Number(point?.year) === Number(latestYear)) || points[points.length - 1] || null;
                const firstNonNull = points.find((point) => point?.value !== null && point?.value !== undefined) || null;
                return { key: item.key, label: item.label, latest: latestPoint?.value ?? null, first: firstNonNull?.value ?? null };
            });
        }

        function renderCompareLatestChart(payload) {
            if (!compareLatestChartCanvas || typeof Chart === 'undefined') return;
            const latestRows = getLatestSeriesValues(payload).filter((row) => row.latest !== null && Number.isFinite(Number(row.latest)));
            const valueWindow = getCompareValueWindow(payload);
            const filteredRows = latestRows.filter((row) => valueIsInWindow(row.latest, valueWindow.visibleMin, valueWindow.visibleMax));
            const rowsForChart = filteredRows.length ? filteredRows : latestRows;
            destroyChartInstance('compareLatestChart');
            state.compareLatestChart = new Chart(compareLatestChartCanvas, {
                type: 'bar',
                data: {
                    labels: rowsForChart.map((row) => row.label),
                    datasets: [{
                        label: t('latest'),
                        data: rowsForChart.map((row) => row.latest),
                        backgroundColor: rowsForChart.map((row) => getChoroplethColor(row.latest, valueWindow.min, valueWindow.max, valueWindow.visibleMin, valueWindow.visibleMax)),
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
            });
        }

        function renderCompareSeriesCards(payload) {
            const latestRows = getLatestSeriesValues(payload);
            const valueWindow = getCompareValueWindow(payload);
            compareSeriesCards.innerHTML = latestRows.length ? latestRows.map((row) => {
                const change = row.latest !== null && row.first !== null ? row.latest - row.first : null;
                const changeLabel = t('change_value', { value: change === null ? t('not_available') : formatCompact(change) });
                const inWindow = valueIsInWindow(row.latest, valueWindow.visibleMin, valueWindow.visibleMax);
                const tone = row.latest === null ? '#cbd5e1' : getChoroplethColor(row.latest, valueWindow.min, valueWindow.max, valueWindow.visibleMin, valueWindow.visibleMax);
                const bg = row.latest === null ? '#f8fbff' : `${tone}2E`;
                const hint = row.latest !== null && !inWindow ? t('outside_slider') : t('inside_slider');
                return `<div class="series-card" style="background:${escapeHtml(bg)}; border-color:${escapeHtml(tone)};"><div class="name">${escapeHtml(row.label)}</div><div class="latest">${escapeHtml(formatCompact(row.latest))}</div><div class="change">${escapeHtml(changeLabel)}</div><div class="change">${escapeHtml(hint)}</div></div>`;
            }).join('') : `<div class="empty-box">${escapeHtml(t('no_series'))}</div>`;
        }

        function renderCompareHeatmap(payload) {
            const slice = getSafeComparisonSlice(payload);
            const years = slice.years;
            const series = slice.series;
            if (!years.length || !series.length) {
                compareHeatmap.innerHTML = `<table><tbody><tr><td style="padding:0.6rem;">${escapeHtml(t('no_data_available'))}</td></tr></tbody></table>`;
                return;
            }
            const valueWindow = getCompareValueWindow(payload);
            const cellStyle = (value) => {
                if (value === null || value === undefined || !Number.isFinite(Number(value)) || valueWindow.min === null || valueWindow.max === null) return 'background:#f8fafc;';
                if (!valueIsInWindow(value, valueWindow.visibleMin, valueWindow.visibleMax)) return 'background:#f8fafc;color:#94a3b8;';
                const ratio = valueWindow.min === valueWindow.max ? 1 : ((Number(value) - valueWindow.min) / (valueWindow.max - valueWindow.min));
                const color = getChoroplethColor(value, valueWindow.min, valueWindow.max, valueWindow.visibleMin, valueWindow.visibleMax);
                const textColor = ratio > 0.72 ? '#f8fafc' : '#1f2937';
                return `background:${color};color:${textColor};`;
            };
            const note = getComparisonRenderNote(slice);
            compareHeatmap.innerHTML = `
                <table>
                    <thead><tr><th>${escapeHtml(t('series'))}</th>${years.map((year) => `<th>${escapeHtml(year)}</th>`).join('')}</tr></thead>
                    <tbody>
                        ${note ? `<tr><td colspan="${years.length + 1}" style="background:#EFF8FF;color:#0A2450;font-weight:700;">${escapeHtml(note)}</td></tr>` : ''}
                        ${series.map((item) => `<tr><td>${escapeHtml(item.label)}</td>${years.map((year) => {
                            const cellValue = getPointValueForYear(item, year);
                            return `<td style="${cellStyle(cellValue)}">${escapeHtml(formatValue(cellValue))}</td>`;
                        }).join('')}</tr>`).join('')}
                    </tbody>
                </table>
            `;
        }

        function renderComparisonVisuals(payload) {
            const slice = getSafeComparisonSlice(payload);
            const renderPayload = buildPayloadFromSlice(payload, slice);
            renderTrendChart(compareChartCanvas, 'compareChart', renderPayload, compareChartTypeSelect.value || 'line');
            renderCompareLatestChart(renderPayload);
            renderCompareSeriesCards(renderPayload);
            renderCompareTable(renderPayload);
            renderCompareHeatmap(renderPayload);
            renderCompareRangeLabel(renderPayload);
        }

        async function runComparison() {
            if (state.loadingDepth > 0) return;
            const indicatorId = String(compareIndicatorSelect.value || '').trim();
            const mode = String(compareModeSelect.value || 'country');
            const yearFrom = parseInt(compareYearFromInput.value || '', 10);
            const yearTo = parseInt(compareYearToInput.value || '', 10);
            const countries = getSelectedValues(compareCountriesSelect);
            const continents = getSelectedValues(compareContinentsSelect);
            const aggregation = String(compareAggregationSelect.value || 'avg');
            if (!indicatorId) { setCompareStatus(t('select_comparison_indicator'), true); return; }
            if (!Number.isFinite(yearFrom) || !Number.isFinite(yearTo)) { setCompareStatus(t('valid_comparison_years'), true); return; }
            if (mode === 'country' && countries.length < 2) { setCompareStatus(t('select_two_countries'), true); return; }
            if (mode === 'continent' && continents.length < 2) { setCompareStatus(t('select_two_continents'), true); return; }
            const requestedYears = Math.abs(yearTo - yearFrom) + 1;
            if (mode === 'country' && countries.length > PERF_LIMITS.maxCountrySeriesRequest) {
                setCompareStatus(t('too_many_countries', { selected: countries.length, max: PERF_LIMITS.maxCountrySeriesRequest }), true);
                return;
            }
            if (mode === 'country' && (countries.length * requestedYears) > PERF_LIMITS.maxCountryCellsRequest) {
                setCompareStatus(t('selection_too_large', { countries: countries.length, years: requestedYears }), true);
                return;
            }
            setCompareStatus(t('running_comparison'));
            runCompareBtn.disabled = true;
            showLoadingModal(t('preparing_visualizations'));
            try {
                const payload = await fetchComparisonPayload({ indicatorId, compareMode: mode, countries, continents, yearFrom, yearTo, aggregation });
                state.comparePayload = payload;
                renderComparisonVisuals(payload);
                const slice = getSafeComparisonSlice(payload);
                const reductionNote = getComparisonRenderNote(slice);
                setCompareStatus(t('series_loaded', { count: (payload?.series || []).length, indicator: payload?.indicator?.name || indicatorId, note: reductionNote ? ` ${reductionNote}` : '' }));
                if (mode === 'country') {
                    mapYearInput.value = String(yearTo);
                    mapYearSlider.value = String(yearTo);
                    if (Array.from(mapIndicatorSelect.options).some((option) => option.value === indicatorId)) {
                        mapIndicatorSelect.value = indicatorId;
                    }
                    applyMapValuesFromPayload(payload, yearTo, t('comparison_overlay'));
                } else if (mode === 'continent') {
                    mapYearInput.value = String(yearTo);
                    mapYearSlider.value = String(yearTo);
                    Array.from(mapContinentsSelect.options).forEach((option) => {
                        option.selected = continents.includes(option.value);
                    });
                    if (Array.from(mapIndicatorSelect.options).some((option) => option.value === indicatorId)) {
                        mapIndicatorSelect.value = indicatorId;
                    }
                    await runContinentComparison({ indicatorId, year: yearTo, continents, allowWhileLoading: true });
                }
            } catch (error) {
                console.error('Comparison failed', error);
                setCompareStatus(t('comparison_failed'), true);
            } finally {
                hideLoadingModal();
                runCompareBtn.disabled = false;
                refreshMapStyles();
            }
        }

        function renderModalFromPayload(payload, title, subtitle) {
            if (!payload) return;
            vizModalTitle.textContent = title;
            const slice = getSafeComparisonSlice(payload);
            const modalPayload = buildPayloadFromSlice(payload, slice);
            const renderNote = getComparisonRenderNote(slice);
            vizModalSubtitle.textContent = renderNote ? `${subtitle}. ${renderNote}` : subtitle;
            vizModal.classList.add('open');
            vizModal.setAttribute('aria-hidden', 'false');
            renderTrendChart(vizModalChartCanvas, 'modalChart', modalPayload, compareChartTypeSelect.value || 'line');
            const years = Array.isArray(modalPayload?.years) ? modalPayload.years : [];
            const series = Array.isArray(modalPayload?.series) ? modalPayload.series : [];
            vizModalTableHeadRow.innerHTML = `<th>${escapeHtml(t('year'))}</th>`;
            series.forEach((item) => { const th = document.createElement('th'); th.textContent = item.label; vizModalTableHeadRow.appendChild(th); });
            if (!(years.length && series.length)) {
                vizModalTableBody.innerHTML = `<tr><td>${escapeHtml(t('no_data_available'))}</td></tr>`;
                return;
            }
            const noteRow = renderNote ? `<tr><td colspan="${series.length + 1}" style="background:#EFF8FF;color:#0A2450;font-weight:700;">${escapeHtml(renderNote)}</td></tr>` : '';
            vizModalTableBody.innerHTML = `
                ${noteRow}
                ${years.map((year) => `<tr><td>${escapeHtml(year)}</td>${series.map((item) => `<td>${escapeHtml(formatValue(getPointValueForYear(item, year)))}</td>`).join('')}</tr>`).join('')}
            `;
        }

        function closeVizModal() { vizModal.classList.remove('open'); vizModal.setAttribute('aria-hidden', 'true'); destroyChartInstance('modalChart'); }

        async function loadWorldBankCountries() {
            const response = await fetch(worldBankCountriesUrl);
            if (!response.ok) throw new Error(`Could not load countries: HTTP ${response.status}`);
            const payload = await response.json();
            const countries = Array.isArray(payload?.data) ? payload.data : [];
            buildCountryLookups(countries);
            compareCountriesSelect.innerHTML = '';
            countries.forEach((country) => { const option = document.createElement('option'); option.value = String(country.iso2); option.textContent = `${country.name} (${country.iso2})`; compareCountriesSelect.appendChild(option); });
            if (compareCountriesSelect.options.length >= 2) { compareCountriesSelect.options[0].selected = true; compareCountriesSelect.options[1].selected = true; }
        }

        async function loadContinents() {
            const response = await fetch(worldBankContinentsUrl);
            if (!response.ok) throw new Error(`Could not load continents: HTTP ${response.status}`);
            const payload = await response.json();
            const continents = Array.isArray(payload?.data) ? payload.data : [];
            compareContinentsSelect.innerHTML = '';
            mapContinentsSelect.innerHTML = '';
            continents.forEach((continent) => {
                const value = String(continent);
                const optionA = document.createElement('option');
                optionA.value = value;
                optionA.textContent = value;
                compareContinentsSelect.appendChild(optionA);
                const optionB = document.createElement('option');
                optionB.value = value;
                optionB.textContent = value;
                mapContinentsSelect.appendChild(optionB);
            });
            if (compareContinentsSelect.options.length >= 2) {
                compareContinentsSelect.options[0].selected = true;
                compareContinentsSelect.options[1].selected = true;
            }
            Array.from(mapContinentsSelect.options).forEach((option) => { option.selected = false; });
        }

        async function fetchIndicatorsByTopic(topicId) {
            const url = new URL(worldBankIndicatorsUrl, window.location.origin);
            if (topicId) url.searchParams.set('topic_id', topicId);
            url.searchParams.set('limit', '1200');
            const response = await fetch(url.toString());
            if (!response.ok) throw new Error(`Could not load indicators: HTTP ${response.status}`);
            const payload = await response.json();
            return Array.isArray(payload?.data) ? payload.data : [];
        }

        function fillIndicatorSelect(selectEl, indicators, previous = '') {
            selectEl.innerHTML = '';
            indicators.forEach((indicator) => {
                const option = document.createElement('option');
                option.value = String(indicator.id);
                option.textContent = `${indicator.name} [${indicator.id}]`;
                selectEl.appendChild(option);
            });
            if (previous && indicators.some((indicator) => String(indicator.id) === previous)) {
                selectEl.value = previous;
            } else if (indicators.length) {
                selectEl.value = String(indicators[0].id);
            }
        }

        async function loadMapIndicators() {
            const indicators = await fetchIndicatorsByTopic(String(mapTopicSelect.value || ''));
            if (!indicators.length) {
                mapIndicatorSelect.innerHTML = `<option value="">${escapeHtml(t('no_indicators'))}</option>`;
                snapshotIndicatorSelect.innerHTML = `<option value="">${escapeHtml(t('no_indicators'))}</option>`;
                return;
            }
            fillIndicatorSelect(mapIndicatorSelect, indicators, String(mapIndicatorSelect.value || ''));
            fillIndicatorSelect(snapshotIndicatorSelect, indicators, String(snapshotIndicatorSelect.value || mapIndicatorSelect.value || ''));
        }

        async function loadCompareIndicators() {
            const indicators = await fetchIndicatorsByTopic(String(compareTopicSelect.value || ''));
            if (!indicators.length) {
                compareIndicatorSelect.innerHTML = `<option value="">${escapeHtml(t('no_indicators'))}</option>`;
                return;
            }
            fillIndicatorSelect(compareIndicatorSelect, indicators, String(compareIndicatorSelect.value || ''));
        }

        async function loadTopicsAndPrimeIndicators() {
            const response = await fetch(worldBankTopicsUrl);
            if (!response.ok) throw new Error(`Could not load topics: HTTP ${response.status}`);
            const payload = await response.json();
            const topics = Array.isArray(payload?.data) ? payload.data : [];
            mapTopicSelect.innerHTML = ''; compareTopicSelect.innerHTML = '';
            topics.forEach((topic) => {
                const optionA = document.createElement('option');
                optionA.value = String(topic.id);
                optionA.textContent = `${topic.name} (${topic.indicator_count ?? 0})`;
                mapTopicSelect.appendChild(optionA);
                compareTopicSelect.appendChild(optionA.cloneNode(true));
            });
            if (topics.length) { mapTopicSelect.value = String(topics[0].id); compareTopicSelect.value = String(topics[0].id); }
            await Promise.all([loadMapIndicators(), loadCompareIndicators()]);
        }

        function initializeYearInputs() {
            const currentYear = new Date().getFullYear();
            const defaultMapYear = Math.max(1960, currentYear - 1);
            mapYearInput.value = String(defaultMapYear);
            mapYearSlider.min = '1960';
            mapYearSlider.max = String(currentYear);
            mapYearSlider.value = String(defaultMapYear);
            compareYearFromInput.value = String(Math.max(1960, currentYear - 12));
            compareYearToInput.value = String(currentYear);
            snapshotYearFrom.value = String(Math.max(1960, currentYear - 12));
            snapshotYearTo.value = String(currentYear);
            state.mapVisibleRangePercent = { min: 0, max: 100 };
            mapRangeMinSlider.value = '0';
            mapRangeMaxSlider.value = '100';
            state.compareVisibleRangePercent = { min: 0, max: 100 };
            compareRangeMinSlider.value = '0';
            compareRangeMaxSlider.value = '100';
            renderMapRangeLabel();
            renderCompareRangeLabel();
        }

        function wireEvents() {
            window.addEventListener('load', syncNavbarOffset);
            window.addEventListener('resize', () => {
                syncNavbarOffset();
                if (mapCompareModal.classList.contains('open')) refreshMapCompareModalMaps();
            });
            ['click', 'dblclick', 'pointerdown', 'submit', 'change'].forEach((eventName) => {
                document.addEventListener(eventName, blockUiEventWhileLoading, true);
            });
            regionSelect.addEventListener('change', async () => {
                if (state.loadingDepth > 0) return;
                showLoadingModal(t('loading_region_data'));
                try {
                    await loadRegion(regionSelect.value);
                    if (mapIndicatorSelect.value) await runMapVisualization({ allowWhileLoading: true });
                } finally {
                    hideLoadingModal();
                }
            });
            mapTopicSelect.addEventListener('change', async () => {
                if (state.loadingDepth > 0) return;
                showLoadingModal(t('loading_map_indicators'));
                try {
                    await loadMapIndicators();
                    if (mapIndicatorSelect.value) await runMapVisualization({ allowWhileLoading: true });
                } finally {
                    hideLoadingModal();
                }
            });
            mapIndicatorSelect.addEventListener('change', () => { if (!snapshotIndicatorSelect.value) snapshotIndicatorSelect.value = mapIndicatorSelect.value; });
            mapYearInput.addEventListener('input', () => {
                const year = parseInt(mapYearInput.value || '', 10);
                if (Number.isFinite(year)) mapYearSlider.value = String(year);
            });
            mapYearSlider.addEventListener('input', () => {
                mapYearInput.value = mapYearSlider.value;
            });
            mapRangeMinSlider.addEventListener('input', async () => {
                state.mapVisibleRangePercent = syncSliderPairState(mapRangeMinSlider, mapRangeMaxSlider, 'min');
                renderMapRangeLabel();
                refreshMapStyles();
                renderMapTables(state.mapRows);
                renderMapLegend(state.mapIndicatorMeta.label, state.mapIndicatorMeta.unit, state.mapRange.min, state.mapRange.max);
                scheduleMapCompareModalRender();
            });
            mapRangeMaxSlider.addEventListener('input', async () => {
                state.mapVisibleRangePercent = syncSliderPairState(mapRangeMinSlider, mapRangeMaxSlider, 'max');
                renderMapRangeLabel();
                refreshMapStyles();
                renderMapTables(state.mapRows);
                renderMapLegend(state.mapIndicatorMeta.label, state.mapIndicatorMeta.unit, state.mapRange.min, state.mapRange.max);
                scheduleMapCompareModalRender();
            });
            runMapVizBtn.addEventListener('click', runMapVisualization);
            runContinentCompareBtn.addEventListener('click', async () => { await runContinentComparison({ openModal: false }); });
            openMapCompareModalBtn.addEventListener('click', async () => {
                if (state.loadingDepth > 0) return;
                if (!state.mapCompareContext) {
                    await runContinentComparison({ openModal: true });
                    return;
                }
                showLoadingModal(t('preparing_multi_map'));
                try {
                    openMapCompareModal();
                    await renderMapCompareModalContent();
                } finally {
                    hideLoadingModal();
                }
            });
            resetMapVizBtn.addEventListener('click', () => {
                state.mapValuesByIso2.clear();
                state.mapValuesByNormName.clear();
                state.mapRows = [];
                state.mapRange = { min: null, max: null };
                state.mapIndicatorMeta = { label: t('indicator'), unit: '' };
                state.mapVisibleRangePercent = { min: 0, max: 100 };
                mapRangeMinSlider.value = '0';
                mapRangeMaxSlider.value = '100';
                state.mapComparePayloadByContinent = new Map();
                state.mapCompareContext = null;
                renderMapRangeLabel();
                refreshMapStyles();
                renderMapLegend(t('indicator'), '', null, null);
                mapTopTableBody.innerHTML = `<tr><td colspan="3">${escapeHtml(t('overlay_cleared'))}</td></tr>`;
                mapDataTableBody.innerHTML = `<tr><td colspan="3">${escapeHtml(t('overlay_cleared'))}</td></tr>`;
                mapComparePanels.innerHTML = `<div class="empty-box">${escapeHtml(t('run_multi_continent'))}</div>`;
                mapCompareCountryTableBody.innerHTML = `<tr><td colspan="3">${escapeHtml(t('no_map_comparison'))}</td></tr>`;
                mapCompareGlobalTableBody.innerHTML = `<tr><td colspan="2">${escapeHtml(t('no_global_comparison'))}</td></tr>`;
                setMapStatus(t('map_overlay_cleared'));
            });
            countrySelect.addEventListener('change', async () => { const countryName = String(countrySelect.value || '').trim(); if (countryName) await selectCountry(countryName, resolveIso2ByName(countryName), false); });
            runSnapshotBtn.addEventListener('click', runSnapshotTrend);
            compareTopicSelect.addEventListener('change', async () => {
                if (state.loadingDepth > 0) return;
                showLoadingModal(t('loading_comparison_indicators'));
                try {
                    await loadCompareIndicators();
                } finally {
                    hideLoadingModal();
                }
            });
            compareModeSelect.addEventListener('change', syncCompareMode);
            compareCountriesSelect.addEventListener('change', refreshMapStyles);
            compareRangeMinSlider.addEventListener('input', () => {
                state.compareVisibleRangePercent = syncSliderPairState(compareRangeMinSlider, compareRangeMaxSlider, 'min');
                renderCompareRangeLabel();
                if (state.comparePayload) {
                    renderCompareLatestChart(state.comparePayload);
                    renderCompareSeriesCards(state.comparePayload);
                    renderCompareHeatmap(state.comparePayload);
                }
            });
            compareRangeMaxSlider.addEventListener('input', () => {
                state.compareVisibleRangePercent = syncSliderPairState(compareRangeMinSlider, compareRangeMaxSlider, 'max');
                renderCompareRangeLabel();
                if (state.comparePayload) {
                    renderCompareLatestChart(state.comparePayload);
                    renderCompareSeriesCards(state.comparePayload);
                    renderCompareHeatmap(state.comparePayload);
                }
            });
            runCompareBtn.addEventListener('click', runComparison);
            openCompareModalBtn.addEventListener('click', () => {
                if (state.loadingDepth > 0) return;
                if (!state.comparePayload) { setCompareStatus(t('run_comparison_first'), true); return; }
                renderModalFromPayload(state.comparePayload, t('comparison_modal'), state.comparePayload?.indicator?.name || t('indicator_comparison'));
            });
            openSnapshotModalBtn.addEventListener('click', () => {
                if (state.loadingDepth > 0) return;
                if (!state.snapshotPayload) { snapshotHint.textContent = t('load_snapshot_first'); return; }
                renderModalFromPayload(state.snapshotPayload, t('snapshot_modal'), state.snapshotPayload?.indicator?.name || t('country_trend'));
            });
            vizModalCloseBtn.addEventListener('click', closeVizModal);
            vizModal.addEventListener('click', (event) => { if (event.target === vizModal) closeVizModal(); });
            mapCompareModalCloseBtn.addEventListener('click', closeMapCompareModal);
            mapCompareModal.addEventListener('click', (event) => { if (event.target === mapCompareModal) closeMapCompareModal(); });
            document.addEventListener('keydown', (event) => {
                if (state.loadingDepth > 0) {
                    if (event.key === 'F5' || ((event.ctrlKey || event.metaKey) && String(event.key).toLowerCase() === 'r')) return;
                    event.preventDefault();
                    event.stopPropagation();
                    if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
                    return;
                }
                if (event.key !== 'Escape') return;
                if (vizModal.classList.contains('open')) closeVizModal();
                if (mapCompareModal.classList.contains('open')) closeMapCompareModal();
            });
        }

        async function initializeDashboard() {
            syncNavbarOffset(); initializeYearInputs(); wireEvents(); syncCompareMode();
            setMapStatus(t('loading_metadata'));
            setCompareStatus(t('loading_catalog'));
            setDashboardInitializing(true);
            try {
                await Promise.all([loadWorldBankCountries(), loadContinents(), loadTopicsAndPrimeIndicators()]);
                const initialRegion = (defaultRegion && shapeFilesByRegion[defaultRegion]) ? defaultRegion : (regionSelect.options.length ? regionSelect.options[0].value : null);
                if (initialRegion) { regionSelect.value = initialRegion; await loadRegion(initialRegion); } else { setMapStatus(t('no_region_shapes_configured'), true); }
                if (mapIndicatorSelect.value) await runMapVisualization({ allowWhileLoading: true, showGlobalLoading: false });
                setCompareStatus(t('ready_compare'));
            } catch (error) {
                console.error('Initialization failed', error);
                setMapStatus(t('dashboard_init_failed'), true);
                setCompareStatus(t('comparison_init_failed'), true);
            } finally {
                setDashboardInitializing(false);
            }
        }

        initializeDashboard();
    </script>

    @include('partials.public-marketing-scripts')
</body>
</html>
