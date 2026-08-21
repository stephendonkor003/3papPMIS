<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SubActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectBudgetController extends Controller
{
    /**
     * Display list of all projects
     */
    public function index()
    {
        $projects = Project::with('subActivities')->latest()->paginate(10);
        return view('project_budget.index', compact('projects'));
    }

    /**
     * Show form for creating a new project
     */
    public function create()
    {
        return view('project_budget.create');
    }

    /**
     * Store new project
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'total_budget' => 'required|numeric|min:0',
            'start_year' => 'required|digits:4',
            'end_year' => 'required|digits:4|gte:start_year',
            'yearly_allocations' => 'nullable|array',
            'yearly_allocations.*' => 'numeric|min:0|max:100',
            'project_id' => 'nullable|string|max:50|unique:projects,project_id',
        ]);

        $project = Project::create([
            'project_id' => $request->project_id, // will auto-generate if null
            'project_name' => $request->project_name,
            'description' => $request->description,
            'total_budget' => $request->total_budget,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'yearly_allocations' => $request->yearly_allocations,
            'is_custom_id' => $request->filled('project_id'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('project_budget.index')
            ->with('success', 'Project created successfully!');
    }




    /**
     * Store sub-activity under a project
     */
    public function storeSubActivity(Request $request, $projectId)
    {
        $request->validate([
            'sub_activity_name' => 'required|string|max:255',
            'budget_allocation' => 'required|numeric|min:0',
            'year' => 'required|digits:4',
            'percentage_allocation' => 'nullable|numeric|min:0|max:100',
        ]);

        $project = Project::findOrFail($projectId);

        SubActivity::create([
            'project_id' => $project->id,
            'sub_activity_name' => $request->sub_activity_name,
            'budget_allocation' => $request->budget_allocation,
            'year' => $request->year,
            'percentage_allocation' => $request->percentage_allocation,
        ]);

        return back()->with('success', 'Sub-Activity added successfully!');
    }

    /**
     * View project details + sub-activities
     */
    public function show($id)
    {
        $project = Project::with('subActivities')->findOrFail($id);
        return view('project_budget.show', compact('project'));
    }

    /**
     * Delete project (with its sub-activities)
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return back()->with('success', 'Project deleted successfully.');
    }


    /**
 * Delete a single sub-activity
 */
 public function destroySubActivity($id)
{
    $sub = \App\Models\SubActivity::findOrFail($id);
    $sub->delete();

    return response()->json(['success' => true, 'message' => 'Sub-Activity removed successfully.']);
}


}
