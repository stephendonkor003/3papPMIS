/**
 * Global DataTable Configuration
 * Provides consistent styling and export options for all tables
 */

// Default DataTable configuration
const dataTableConfig = {
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
         '<"row"<"col-sm-12 col-md-12"B>>' +
         '<"row"<"col-sm-12"tr>>' +
         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    buttons: [
        {
            extend: 'copy',
            className: 'btn btn-sm btn-primary me-1',
            text: '<i class="feather-copy me-1"></i>Copy',
            exportOptions: {
                columns: ':visible:not(.no-export)'
            }
        },
        {
            extend: 'csv',
            className: 'btn btn-sm btn-success me-1',
            text: '<i class="feather-file-text me-1"></i>CSV',
            exportOptions: {
                columns: ':visible:not(.no-export)'
            }
        },
        {
            extend: 'excel',
            className: 'btn btn-sm btn-info me-1',
            text: '<i class="feather-file me-1"></i>Excel',
            exportOptions: {
                columns: ':visible:not(.no-export)'
            }
        },
        {
            extend: 'pdf',
            className: 'btn btn-sm btn-danger me-1',
            text: '<i class="feather-file-text me-1"></i>PDF',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
                columns: ':visible:not(.no-export)'
            },
            customize: function(doc) {
                // Use AU green theme for partner portal, maroon for admin
                const isPartnerPortal = document.body.classList.contains('partner-portal-body');
                const headerColor = isPartnerPortal ? '#007144' : '#532934';

                doc.styles.tableHeader = {
                    bold: true,
                    fontSize: 11,
                    color: 'white',
                    fillColor: headerColor
                };
                doc.styles.tableBodyOdd = {
                    fillColor: '#f8f9fa'
                };
                doc.styles.tableBodyEven = {
                    fillColor: '#ffffff'
                };

                // Add footer with organization name for partner portal
                if (isPartnerPortal) {
                    doc.footer = function(currentPage, pageCount) {
                        return {
                            columns: [
                                {
                                    text: 'Africa Think Tank Platform',
                                    alignment: 'left',
                                    fontSize: 8,
                                    margin: [40, 0]
                                },
                                {
                                    text: 'Page ' + currentPage.toString() + ' of ' + pageCount,
                                    alignment: 'right',
                                    fontSize: 8,
                                    margin: [0, 0, 40, 0]
                                }
                            ]
                        };
                    };
                }
            }
        },
        {
            extend: 'print',
            className: 'btn btn-sm btn-secondary me-1',
            text: '<i class="feather-printer me-1"></i>Print',
            exportOptions: {
                columns: ':visible:not(.no-export)'
            },
            customize: function(win) {
                $(win.document.body)
                    .css('font-size', '10pt')
                    .prepend(
                        '<div style="text-align:center; margin-bottom: 20px;">' +
                        '<h2>Data Export</h2>' +
                        '<p>Generated on: ' + new Date().toLocaleString() + '</p>' +
                        '</div>'
                    );

                $(win.document.body).find('table')
                    .addClass('table-bordered')
                    .css('font-size', 'inherit');
            }
        },
        {
            extend: 'colvis',
            className: 'btn btn-sm btn-warning',
            text: '<i class="feather-eye me-1"></i>Columns',
            columns: ':not(.no-toggle)'
        }
    ],
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    responsive: true,
    autoWidth: false,
    stateSave: true,
    language: {
        search: "_INPUT_",
        searchPlaceholder: "Search records...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _TOTAL_ total entries)",
        paginate: {
            first: "First",
            last: "Last",
            next: "Next",
            previous: "Previous"
        },
        emptyTable: "No data available in table",
        zeroRecords: "No matching records found"
    },
    order: [[0, 'asc']],
    columnDefs: [
        {
            targets: 'no-sort',
            orderable: false
        },
        {
            targets: 'text-center',
            className: 'text-center'
        },
        {
            targets: 'text-end',
            className: 'text-end'
        }
    ]
};

// Initialize all DataTables with class 'data-table'
function initDataTables() {
    if ($.fn.DataTable) {
        $('.data-table').each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                const customConfig = $(this).data('config') || {};
                const config = $.extend(true, {}, dataTableConfig, customConfig);
                $(this).DataTable(config);
            }
        });
    }
}

// Initialize on document ready (runs once)
$(document).ready(function() {
    // Small delay to let component scripts register first
    setTimeout(function() {
        initDataTables();
    }, 10);
});

// Export the config for manual initialization
window.dataTableConfig = dataTableConfig;
window.initDataTables = initDataTables;
