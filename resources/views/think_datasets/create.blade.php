@extends('layouts.app')
@section('title', 'Add Think Dataset')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <h5 class="m-b-10">Add Think Dataset</h5>
                </div>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('think-datasets.store') }}" method="POST">
                            @csrf
                            <div class="row">

                                <!-- Ottd ID -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Ottd ID</label>
                                    <input type="text" name="ottd_id" class="form-control">
                                </div>

                                <!-- Tt Name En -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tt Name En</label>
                                    <input type="text" name="tt_name_en" class="form-control">
                                </div>

                                <!-- Continent -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Continent</label>
                                    <select name="continent" class="form-control">
                                        <option value="">-- Select Continent --</option>
                                        @foreach ($continents as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sub Region -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Sub Region</label>
                                    <select name="sub_region" class="form-control">
                                        <option value="">-- Select Sub Region --</option>
                                        @foreach ($subRegions as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Country -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Country</label>
                                    <select name="country" class="form-control">
                                        <option value="">-- Select Country --</option>
                                        @foreach ($countries as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Region Group -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Region Group</label>
                                    <select name="Region_group" class="form-control">
                                        <option value="">-- Select Region Group --</option>
                                        @foreach ($regionGroups as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Other Fields -->
                                @foreach (['Count', 'website', 'g_email', 'operating_langs', 'tt_init', 'description', 'main_city', 'other_offices', 'address', 'tt_business_model', 'Funding.sources', 'Funding.Mechanism', 'tt_affiliations', 'topics', 'geographies', 'date_founded', 'Date founded groups', 'founder', 'founder_gender', 'founder_other_type', 'staff_no', 'pc_staff_female', 'pc_res_staff_female', 'assc_no', 'assc_female_no', 'pub_no', 'fin_usd', 'twitter_handle_link', 'facebook_page', 'youtube_page', 'instagram_acc', 'linkedIn_acc'] as $field)
                                    <div class="col-md-3 mb-3">
                                        <label
                                            class="form-label">{{ ucwords(str_replace(['_', '.'], ' ', $field)) }}</label>
                                        <input type="text" name="{{ str_replace(' ', '_', strtolower($field)) }}"
                                            class="form-control">
                                    </div>
                                @endforeach

                                <!-- Is Validated -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Is Validated</label>
                                    <select name="is_validated" class="form-control">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button class="btn btn-primary">Save Dataset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
