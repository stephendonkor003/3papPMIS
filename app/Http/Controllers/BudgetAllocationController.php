<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Sector,
    Program,
    Project,
    Activity,
    SubActivity,
    ProgramBudgetAllocation
};
use Illuminate\Support\Facades\DB;
use Exception;

class BudgetAllocationController extends Controller
{
    /**
     * Display index page with all programs and relationships.
     */
    public function index()
    {
        $programs = Program::with(['sector', 'projects.activities.subActivities'])->latest()->get();
        $sectors  = Sector::all();

        return view('budget_allocations.index', compact('programs', 'sectors'));
    }

    /**
     * Store a new program under a sector.
     */
    public function storeProgram(Request $request)
    {
        $request->validate([
            'sector_id'   => 'required|exists:sectors,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_budget'=> 'required|numeric|min:0',
            'years'       => 'required|integer|min:1|max:10',
        ]);

        DB::beginTransaction();
        try {
            $program = Program::create($request->only('sector_id', 'name', 'description', 'total_budget', 'years'));
            DB::commit();

            return back()->with('success', 'Program created successfully with ID '.$program->program_id);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a new project under a program.
     */
    public function storeProject(Request $request)
    {
        $request->validate([
            'program_id'   => 'required|exists:programs,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'total_budget' => 'required|numeric|min:0',
            'years'        => 'required|integer|min:1|max:10',
            'allocations'  => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $program = Program::findOrFail($request->program_id);

            // enforce 5-project limit
            if ($program->projects()->count() >= 5) {
                throw new Exception('Maximum of 5 projects allowed per program.');
            }

            // validate allocation total
            $sumAlloc = array_sum($request->allocations);
            if ($sumAlloc > $request->total_budget) {
                throw new Exception('Total yearly allocations exceed total project budget.');
            }

            $project = Project::create($request->only('program_id','name','description','total_budget','years'));

            // store allocations
            foreach ($request->allocations as $year => $amount) {
                ProgramBudgetAllocation::create([
                    'project_id' => $project->id,
                    'year' => $year,
                    'allocated_amount' => $amount,
                ]);
            }

            DB::commit();
            return back()->with('success', "Project {$project->project_id} created successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a new activity under a project.
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'total_budget' => 'required|numeric|min:0',
            'years'        => 'required|integer|min:1|max:10',
            'allocations'  => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $project = Project::findOrFail($request->project_id);
            $sumAlloc = array_sum($request->allocations);
            if ($sumAlloc > $request->total_budget) {
                throw new Exception('Allocations exceed activity budget.');
            }

            $activity = Activity::create($request->only('project_id','name','description','total_budget','years'));

            foreach ($request->allocations as $year => $amount) {
                ProgramBudgetAllocation::create([
                    'activity_id' => $activity->id,
                    'year' => $year,
                    'allocated_amount' => $amount,
                ]);
            }

            DB::commit();
            return back()->with('success', "Activity {$activity->activity_id} created successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a new sub-activity under an activity.
     */
    public function storeSubActivity(Request $request)
    {
        $request->validate([
            'activity_id'  => 'required|exists:activities,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'total_budget' => 'required|numeric|min:0',
            'years'        => 'required|integer|min:1|max:10',
            'allocations'  => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $activity = Activity::findOrFail($request->activity_id);
            $sumAlloc = array_sum($request->allocations);
            if ($sumAlloc > $request->total_budget) {
                throw new Exception('Allocations exceed sub-activity budget.');
            }

            $sub = SubActivity::create($request->only('activity_id','name','description','total_budget','years'));

            foreach ($request->allocations as $year => $amount) {
                ProgramBudgetAllocation::create([
                    'sub_activity_id' => $sub->id,
                    'year' => $year,
                    'allocated_amount' => $amount,
                ]);
            }

            DB::commit();
            return back()->with('success', "Sub-Activity {$sub->sub_activity_id} created successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show detailed report of a program with budgets and allocations.
     */
    public function show($programId)
    {
        $program = Program::with([
            'sector',
            'projects.activities.subActivities',
            'projects.allocations',
            'projects.activities.allocations'
        ])->findOrFail($programId);

        return view('budget_allocations.show', compact('program'));
    }


    public function summary()
{
    $sectors = Sector::withCount(['programs', 'programs as projects_count' => function ($q) {
        $q->withCount('projects');
    }])
    ->withSum('programs', 'total_budget')
    ->get();

    $totals = [
        'sectors'  => $sectors->count(),
        'programs' => Program::count(),
        'projects' => Project::count(),
        'budget'   => Program::sum('total_budget'),
    ];

    $sectorNames   = $sectors->pluck('name');
    $sectorBudgets = $sectors->pluck('programs_sum_total_budget');
    $programs      = Program::select('name','total_budget')->get();
    $programNames  = $programs->pluck('name');
    $programBudgets= $programs->pluck('total_budget');

    return view('budget_allocations.summary', compact(
        'sectors', 'totals', 'sectorNames', 'sectorBudgets', 'programNames', 'programBudgets'
    ));
}



 /* ==========================================================
       🔹 SECTOR CRUD
       ========================================================== */

    /**
     * Display all sectors.
     */
    public function indexSectors()
    {
        $sectors = Sector::latest()->paginate(10);
        return view('budget_allocations.sectors.index', compact('sectors'));
    }

    /**
     * Store new sector.
     */
    public function storeSector(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name',
            'description' => 'nullable|string',
        ]);

        Sector::create($request->only('name', 'description'));
        return back()->with('success', 'Sector created successfully.');
    }

    /**
     * Update an existing sector.
     */
    public function updateSector(Request $request, $id)
    {
        $sector = Sector::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name,' . $sector->id,
            'description' => 'nullable|string',
        ]);

        $sector->update($request->only('name', 'description'));
        return back()->with('success', 'Sector updated successfully.');
    }

    /**
     * Delete a sector.
     */
    public function destroySector($id)
    {
        $sector = Sector::findOrFail($id);
        $sector->delete();
        return back()->with('success', 'Sector deleted successfully.');
    }






}