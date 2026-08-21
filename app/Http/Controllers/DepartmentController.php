<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HrPosition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    // public function index()
    // {
    //     $departments = Department::withCount([
    //             'programs',
    //             'programFundings' // 👈 NEW: funded programs/projects
    //         ])
    //         ->orderBy('name')
    //         ->paginate(15);

    //     return view('finance.departments.index', compact('departments'));
    // }

    public function index()
    {
        $departments = Department::query()
            ->with([
                'head', // 👈 REQUIRED for Department Head display
            ])
            ->withCount([
                'programs',
                'programFundings',
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('finance.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('finance.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:myb_departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = Auth::id();

        Department::create($validated);

        return redirect()
            ->route('finance.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load([
            'programs.projects',     // deep visibility
            'programFundings.funder', // funding overview
        ]);

        return view('finance.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('finance.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:myb_departments,code,'.$department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        return redirect()
            ->route('finance.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove an unused department.
     */
    public function destroy(Department $department)
    {
        $dependencies = collect([
            'programs' => $department->programs()->exists(),
            'program funding records' => $department->programFundings()->exists(),
            'HR positions' => HrPosition::query()
                ->where('department_id', $department->getKey())
                ->exists(),
        ])->filter()->keys();

        if ($dependencies->isNotEmpty()) {
            return redirect()
                ->route('finance.departments.index')
                ->with(
                    'error',
                    'This department cannot be deleted because it is linked to '.$dependencies->implode(', ').'.'
                );
        }

        $department->delete();

        return redirect()
            ->route('finance.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * (OPTIONAL) Toggle department status
     * Useful for governance without deletion.
     */
    public function toggleStatus(Department $department)
    {
        $department->update([
            'status' => $department->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Department status updated.');
    }

    public function assignHead(Request $request, $departmentId)
    {
        $request->validate([
            'head_user_id' => 'required|exists:users,id',
        ]);

        // Only allow employees
        $user = User::where('id', $request->head_user_id)
            ->where('user_type', 'employee')
            ->firstOrFail();

        $department = Department::findOrFail($departmentId);

        $department->update([
            'head_user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'head_name' => $user->name,
        ]);
    }
}
