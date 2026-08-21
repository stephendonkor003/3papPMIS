@props([
    'id' => 'dataTable_' . uniqid(),
    'class' => '',
    'striped' => true,
    'hover' => true,
    'bordered' => false,
    'responsive' => true,
    'config' => null
])

@php
    $tableClasses = collect([
        'data-table',
        'table',
        $striped ? 'table-striped' : '',
        $hover ? 'table-hover' : '',
        $bordered ? 'table-bordered' : '',
        $class
    ])->filter()->implode(' ');

    $configJson = $config ? json_encode($config) : null;
@endphp

@if($responsive)
<div class="table-responsive">
@endif

<table
    id="{{ $id }}"
    class="{{ $tableClasses }}"
    @if($configJson)
    data-config='{{ $configJson }}'
    @endif
    {{ $attributes }}
>
    {{ $slot }}
</table>

@if($responsive)
</div>
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        const tableSelector = {{ Illuminate\Support\Js::from('#' . $id) }};

        // Check if DataTable is already initialized to prevent reinitializing
        if (!$.fn.DataTable.isDataTable(tableSelector)) {
            @if($configJson)
                const customConfig = {{ Illuminate\Support\Js::from($config) }};
                $(tableSelector).DataTable($.extend(true, {}, window.dataTableConfig || {}, customConfig));
            @else
                $(tableSelector).DataTable(window.dataTableConfig || {});
            @endif
        }
    });
</script>
@endpush
