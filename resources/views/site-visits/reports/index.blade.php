@extends('layouts.app')
@section('title', 'Site Visit Reports')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            {{-- ================= HEADER ================= --}}
            <div class="page-header">
                <div class="page-header-left">
                    <h5 class="m-b-10">Site Visit Reports</h5>
                    <p class="text-muted">
                        Select a procurement to view its comprehensive site visit report
                    </p>
                </div>
            </div>

            <div class="main-content">

                {{-- ================= QUICK STATS ================= --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4>{{ $procurements->count() }}</h4>
                                <small class="text-muted">Total Procurements</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Search procurement by name…">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= TABLE ================= --}}
                <div class="card">
                    <div class="card-body">

                        <table class="table table-bordered" id="procurementTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="cursor:pointer" onclick="sortTable(0)">
                                        Procurement Name
                                        <span class="text-muted">⇅</span>
                                    </th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($procurements as $procurement)
                                    <tr>
                                        <td>{{ $procurement->title }}</td>
                                        <td>
                                            <a href="{{ route('site-visits.procurements.site-visit-report', $procurement) }}"
                                                class="btn btn-sm btn-outline-primary w-100">
                                                View Report
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p id="noResults" class="text-center text-muted mt-3" style="display:none;">
                            No procurements found.
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </main>

    {{-- ================= JS ================= --}}
    <script>
        /* ================= SEARCH ================= */
        const searchInput = document.getElementById('searchInput');
        const table = document.getElementById('procurementTable');
        const rows = table.querySelectorAll('tbody tr');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.cells[0].innerText.toLowerCase();
                const match = text.includes(value);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        });

        /* ================= SORT ================= */
        function sortTable(colIndex) {
            const tbody = table.tBodies[0];
            const rowsArray = Array.from(tbody.rows);
            const asc = tbody.getAttribute('data-sort') !== 'asc';

            rowsArray.sort((a, b) => {
                const aText = a.cells[colIndex].innerText.toLowerCase();
                const bText = b.cells[colIndex].innerText.toLowerCase();
                return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            rowsArray.forEach(row => tbody.appendChild(row));
            tbody.setAttribute('data-sort', asc ? 'asc' : 'desc');
        }
    </script>
@endsection
