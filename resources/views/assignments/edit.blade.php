@extends('layouts.app')
@section('title', 'Edit Assignment')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">

            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <h4>Edit Assignment</h4>
                <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                    Back to Assignments
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('assignments.update', $assignment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Applicant -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Applicant</label>
                            <select name="applicant_id" class="form-select" required>
                                <option value="">-- Select Applicant --</option>
                                @foreach ($applicants as $applicant)
                                    <option value="{{ $applicant->id }}"
                                        {{ $assignment->applicant_id == $applicant->id ? 'selected' : '' }}>
                                        {{ $applicant->think_tank_name }} ({{ $applicant->country }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Custom Multiselect -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Evaluators</label>
                            <div class="custom-multiselect" id="multiBox">
                                <div class="selected-items" id="selectedItems"></div>
                                <input type="text" id="searchInput" placeholder="Type to search..." autocomplete="off">
                                <div class="dropdown" id="dropdownList">
                                    @foreach ($evaluators as $eval)
                                        <div class="dropdown-item" data-id="{{ $eval->id }}">
                                            {{ $eval->name }} — <small>{{ $eval->email }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div id="selectedHiddenInputs"></div>
                            <small class="text-muted">You can select multiple evaluators.</small>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role (optional)</label>
                            <input type="text" name="role" class="form-control" value="{{ $assignment->role }}">
                        </div>

                        <!-- Actions -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <style>
        .custom-multiselect {
            position: relative;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 6px;
            background: #fff;
            min-height: 45px;
        }

        .selected-items {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 5px;
        }

        .selected-items .tag {
            background: #017C76;
            color: #fff;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .selected-items .tag span {
            cursor: pointer;
            font-weight: bold;
        }

        #searchInput {
            border: none;
            outline: none;
            width: 100%;
            padding: 6px;
        }

        .dropdown {
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 6px;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            z-index: 1000;
            display: none;
        }

        .dropdown-item {
            padding: 8px 10px;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: #f1fdfc;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const searchInput = document.getElementById("searchInput");
            const dropdown = document.getElementById("dropdownList");
            const selectedItems = document.getElementById("selectedItems");
            const hiddenInputsContainer = document.getElementById("selectedHiddenInputs");

            let selectedIds = @json($assignment->evaluator_ids ?? []);

            // Preload selected evaluators
            selectedIds.forEach(id => {
                const item = dropdown.querySelector(`.dropdown-item[data-id="${id}"]`);
                if (item) {
                    const name = item.textContent.trim();
                    addTag(id, name);
                }
            });

            function addTag(id, name) {
                if (document.querySelector(`input[data-id="${id}"]`)) return;

                const tag = document.createElement("div");
                tag.className = "tag";
                tag.innerHTML = `${name} <span data-id="${id}">×</span>`;
                selectedItems.appendChild(tag);

                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "evaluator_ids[]";
                hiddenInput.value = id;
                hiddenInput.dataset.id = id;
                hiddenInputsContainer.appendChild(hiddenInput);

                tag.querySelector("span").addEventListener("click", () => {
                    selectedIds = selectedIds.filter(v => v != id);
                    tag.remove();
                    hiddenInputsContainer.querySelector(`input[data-id="${id}"]`).remove();
                });
            }

            searchInput.addEventListener("focus", () => dropdown.style.display = "block");
            document.addEventListener("click", (e) => {
                if (!e.target.closest(".custom-multiselect")) dropdown.style.display = "none";
            });

            searchInput.addEventListener("input", () => {
                const value = searchInput.value.toLowerCase();
                dropdown.querySelectorAll(".dropdown-item").forEach(item => {
                    item.style.display = item.textContent.toLowerCase().includes(value) ? "block" :
                        "none";
                });
            });

            dropdown.querySelectorAll(".dropdown-item").forEach(item => {
                item.addEventListener("click", () => {
                    const id = item.dataset.id;
                    const name = item.textContent.trim();
                    if (!selectedIds.includes(id)) {
                        selectedIds.push(id);
                        addTag(id, name);
                    }
                    searchInput.value = "";
                    dropdown.style.display = "none";
                });
            });
        });
    </script>
@endsection
