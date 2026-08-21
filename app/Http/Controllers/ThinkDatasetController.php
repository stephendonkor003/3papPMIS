<?php

namespace App\Http\Controllers;

use App\Models\ThinkDataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GeoRegion;

class ThinkDatasetController extends Controller
{
    public function index()
    {
        $datasets = ThinkDataset::latest()->paginate(20);
        return view('think_datasets.index', compact('datasets'));
    }

    public function create()
    {
        return view('think_datasets.create', [
            'continents' => GeoRegion::distinct()->pluck('continent')->filter()->sort()->unique(),
            'subRegions' => GeoRegion::distinct()->pluck('sub_region')->filter()->sort()->unique(),
            'countries' => GeoRegion::distinct()->pluck('country')->filter()->sort()->unique(),
            'regionGroups' => GeoRegion::distinct()->pluck('region_group')->filter()->sort()->unique(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['created_by'] = Auth::id();
        $data['is_validated'] = $request->input('is_validated', 'No');

        ThinkDataset::create($data);

        return redirect()->route('think-datasets.index')->with('success', 'Record added successfully.');
    }

    public function show($id)
    {
        $dataset = ThinkDataset::findOrFail($id);
        return view('think_datasets.show', compact('dataset'));
    }

    public function edit($id)
    {
        $dataset = ThinkDataset::findOrFail($id);
        return view('think_datasets.edit', compact('dataset'));
    }

    public function update(Request $request, $id)
    {
        $dataset = ThinkDataset::findOrFail($id);

        $data = $request->all();
        $data['is_validated'] = $request->input('is_validated', 'No');

        $dataset->update($data);

        return redirect()->route('think-datasets.index')->with('success', 'Record updated successfully.');
    }
}