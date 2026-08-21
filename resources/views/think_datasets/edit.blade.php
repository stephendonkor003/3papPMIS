@extends('layouts.app')
@section('title', 'Edit Think Dataset')

@section('content')
    <main class="nxl-container">
        <div class="nxl-content">
            <div class="page-header">
                <h5>Edit Think Dataset</h5>
            </div>

            <div class="main-content">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('think-datasets.update', $dataset->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                @foreach (['ottd_id', 'tt_name_en', 'country', 'continent', 'sub_region', 'Count', 'website', 'g_email', 'operating_langs', 'tt_init', 'description', 'main_city', 'Region_group', 'other_offices', 'address', 'tt_business_model', 'Funding.sources', 'Funding.Mechanism', 'tt_affiliations', 'topics', 'geographies', 'date_founded', 'Date founded groups', 'founder', 'founder_gender', 'founder_other_type', 'staff_no', 'pc_staff_female', 'pc_res_staff_female', 'assc_no', 'assc_female_no', 'pub_no', 'fin_usd', 'twitter_handle_link', 'facebook_page', 'youtube_page', 'instagram_acc', 'linkedIn_acc'] as $field)
                                    @php $name = str_replace(' ', '_', strtolower($field)); @endphp
                                    <div class="col-md-3 mb-3">
                                        <label
                                            class="form-label">{{ ucwords(str_replace(['_', '.'], ' ', $field)) }}</label>
                                        <input type="text" name="{{ $name }}" class="form-control"
                                            value="{{ old($name, $dataset->$name) }}">
                                    </div>
                                @endforeach

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Is Validated</label>
                                    <select name="is_validated" class="form-control">
                                        <option value="No" {{ $dataset->is_validated == 'No' ? 'selected' : '' }}>No
                                        </option>
                                        <option value="Yes" {{ $dataset->is_validated == 'Yes' ? 'selected' : '' }}>Yes
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-success">Update Dataset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
