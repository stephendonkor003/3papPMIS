<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index()
    {
        $assignments = Assignment::with(['applicant', 'evaluator'])->latest()->paginate(15);

        return view('assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
{
    // Get applicants who passed prescreening (eligible = Yes)
    $applicants = Applicant::whereHas('prescreeningCriteria', function ($query) {
        $query->where('eligible', 'Yes');
    })->get();

    // Get evaluators
    $evaluators = User::where('user_type', 'evaluator')->get();

    return view('assignments.create', compact('applicants', 'evaluators'));
}


    /**
     * Store a newly created assignment in storage.
     */
    // AssignmentController@store
    public function store(Request $request)
{
    try {
        // ✅ Validate input
        $request->validate([
            'applicant_id'   => 'required|exists:applicants,id',
            'evaluator_ids'  => 'required|array|min:1',
            'evaluator_ids.*' => 'exists:users,id',
            'role'           => 'nullable|string|max:255',
        ]);

        // ✅ Loop and insert one row per evaluator
        foreach ($request->evaluator_ids as $evaluatorId) {
            \App\Models\Assignment::updateOrCreate(
                [
                    'applicant_id' => $request->applicant_id,
                    'evaluator_id' => $evaluatorId,
                ],
                [
                    'role' => $request->role,
                ]
            );
        }

        return redirect()
            ->route('assignments.index')
            ->with('success', 'Applicant successfully assigned to selected evaluators.');
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Error while saving: ' . $e->getMessage());
    }
}




    /**
     * Display the specified assignment.
     */
    public function show($id)
    {
        $assignment = Assignment::with(['applicant', 'evaluator'])->findOrFail($id);

        return view('assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($id)
    {
        $assignment = Assignment::findOrFail($id);
        $applicants = Applicant::all();
        $evaluators = User::where('user_type', 'evaluator')->get();

        return view('assignments.edit', compact('assignment', 'applicants', 'evaluators'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(Request $request, Assignment $assignment)
{
    try {
        $request->validate([
            'applicant_id'   => 'required|exists:applicants,id',
            'evaluator_ids'  => 'required|array|min:1',
            'evaluator_ids.*'=> 'exists:users,id',
            'role'           => 'nullable|string|max:255',
        ]);

        $assignment->update([
            'applicant_id'  => $request->applicant_id,
            'evaluator_ids' => json_encode($request->evaluator_ids),
            'role'          => $request->role,
        ]);

        return redirect()->route('assignments.index')->with('success', 'Assignment updated successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error updating assignment: ' . $e->getMessage());
    }
}


    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->delete();

        return redirect()->route('assignments.index')->with('success', 'Assignment deleted successfully.');
    }
}
