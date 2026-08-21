<?php

namespace App\Http\Controllers\AuMasterData;

use App\Http\Controllers\Controller;
use App\Models\AuFlagshipProject;
use Illuminate\Http\Request;

class AuFlagshipProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.au_master_data.view')->only(['index']);
        $this->middleware('permission:settings.au_master_data.create')->only(['create', 'store']);
        $this->middleware('permission:settings.au_master_data.edit')->only(['edit', 'update']);
        $this->middleware('permission:settings.au_master_data.delete')->only(['destroy']);
    }

    public function index()
    {
        $flagshipProjects = AuFlagshipProject::ordered()->get();
        return view('au-master-data.flagship-projects.index', compact('flagshipProjects'));
    }

    public function create()
    {
        $nextNumber = AuFlagshipProject::max('number') + 1;
        return view('au-master-data.flagship-projects.create', compact('nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:myb_au_flagship_projects,number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        AuFlagshipProject::create($validated);

        return redirect()
            ->route('settings.au.flagship-projects.index')
            ->with('success', 'Flagship project created successfully.');
    }

    public function edit(AuFlagshipProject $flagship_project)
    {
        return view('au-master-data.flagship-projects.edit', ['flagshipProject' => $flagship_project]);
    }

    public function update(Request $request, AuFlagshipProject $flagship_project)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:myb_au_flagship_projects,number,' . $flagship_project->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $flagship_project->update($validated);

        return redirect()
            ->route('settings.au.flagship-projects.index')
            ->with('success', 'Flagship project updated successfully.');
    }

    public function destroy(AuFlagshipProject $flagship_project)
    {
        $flagship_project->delete();

        return redirect()
            ->route('settings.au.flagship-projects.index')
            ->with('success', 'Flagship project deleted successfully.');
    }
}
