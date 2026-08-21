<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\User;
use App\Models\FinancialEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{


    /**
     * Show assignment form (admin only).
     */
 public function assign()
{
    // Only applicants that passed prescreening (eligible = Yes)
    $applicants = Applicant::whereHas('prescreeningCriteria', function ($q) {
        $q->where('eligible', 'Yes');
    })->get();

    // Only financial evaluators
    $evaluators = User::where('user_type', 'financial_evaluator')->get();

    // Existing assignments
    $assignments = DB::table('assignments')
        ->join('applicants', 'assignments.applicant_id', '=', 'applicants.id')
        ->join('users', 'assignments.evaluator_id', '=', 'users.id')
        ->where('assignments.role', 'financial')
        ->select(
            'assignments.*',
            'applicants.think_tank_name',
            'users.name as evaluator_name'
        )
        ->orderBy('assignments.created_at', 'desc')
        ->get();

    return view('financial.assign', compact('applicants', 'evaluators', 'assignments'));
}



    /**
     * Store new assignment.
     */
    public function storeAssignment(Request $request)
{
    $request->validate([
        'applicant_id' => 'required|exists:applicants,id',
        'evaluator_id' => 'required|exists:users,id',
    ]);

    try {
        DB::table('assignments')->insert([
            'applicant_id' => $request->applicant_id,
            'evaluator_id' => $request->evaluator_id,
            'role'         => 'financial',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('financial.assign')
            ->with('success', 'Financial evaluator assigned successfully.');

    } catch (\Illuminate\Database\QueryException $ex) {
        if ($ex->errorInfo[1] == 1062) {
            // Duplicate entry
            return redirect()->route('financial.assign')
                ->with('error', 'This applicant is already assigned to this evaluator.');
        }

        // General DB error
        return redirect()->route('financial.assign')
            ->with('error', 'An error occurred while assigning the evaluator.');
    }
}




public function deleteAssignment($id)
{
    $assignment = DB::table('assignments')->where('id', $id)->where('role', 'financial')->first();

    if (!$assignment) {
        return redirect()->back()->with('error', 'Assignment not found.');
    }

    DB::table('assignments')->where('id', $id)->delete();

    return redirect()->back()->with('success', 'Assignment removed successfully.');
}



    /**
     * Show evaluations depending on role.
     */

public function index()
{
    $user = Auth::user();

    if ($user->user_type === 'admin') {
        $evaluations = FinancialEvaluation::with('applicant')->latest()->get();
        $pendingApplicants = collect(); // admin sees only evaluations
    } elseif ($user->user_type === 'financial_evaluator') {
        // Assigned applicants
        $assignedIds = DB::table('assignments')
            ->where('role', 'financial')
            ->where('evaluator_id', $user->id)
            ->pluck('applicant_id');

        // Evaluations already done
        $evaluations = FinancialEvaluation::with('applicant')
            ->where('evaluator_id', $user->id)
            ->latest()
            ->get();

        // Applicants assigned but not yet evaluated
        $pendingApplicants = Applicant::whereIn('id', $assignedIds)
            ->whereNotIn('id', $evaluations->pluck('applicant_id'))
            ->get();
    } else {
        $evaluations = collect();
        $pendingApplicants = collect();
    }

    return view('financial.index', compact('evaluations', 'pendingApplicants'));
}




 public function create($applicant_id)
{
    $applicant = Applicant::findOrFail($applicant_id);

    // Ensure evaluator is assigned
    $assigned = DB::table('assignments')
        ->where('role', 'financial')
        ->where('applicant_id', $applicant_id)
        ->where('evaluator_id', Auth::id())
        ->exists();

    if (Auth::user()->user_type !== 'admin' && !$assigned) {
        abort(403, 'You are not assigned to evaluate this applicant.');
    }

    // Check if already submitted
    $existing = FinancialEvaluation::where('applicant_id', $applicant_id)
        ->where('evaluator_id', Auth::id())
        ->where('status', 'submitted')
        ->first();

    if ($existing && Auth::user()->user_type !== 'admin') {
        return redirect()->route('financial.index')
            ->with('error', 'You have already submitted this evaluation.');
    }

    $evaluation = new FinancialEvaluation();

    return view('financial.create', compact('applicant', 'evaluation'));
}




    /**
     * Store evaluation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'status'       => 'required|in:draft,submitted',
        ]);

        $evaluation = new FinancialEvaluation();
        $evaluation->applicant_id = $request->applicant_id;
        $evaluation->evaluator_id = Auth::id();

        // Criteria
        $evaluation->strength_financial_health = $request->strength_financial_health;
        $evaluation->gap_financial_health      = $request->gap_financial_health;

        $evaluation->strength_accuracy = $request->strength_accuracy;
        $evaluation->gap_accuracy      = $request->gap_accuracy;

        $evaluation->strength_revenue = $request->strength_revenue;
        $evaluation->gap_revenue      = $request->gap_revenue;

        $evaluation->strength_fund_use = $request->strength_fund_use;
        $evaluation->gap_fund_use      = $request->gap_fund_use;

        $evaluation->strength_liabilities = $request->strength_liabilities;
        $evaluation->gap_liabilities      = $request->gap_liabilities;

        $evaluation->strength_compliance = $request->strength_compliance;
        $evaluation->gap_compliance      = $request->gap_compliance;

        $evaluation->overall_financial_assessment = $request->overall_financial_assessment;
        $evaluation->status = $request->status;

        $evaluation->save();

        return redirect()->route('financial.index')->with('success', 'Financial evaluation saved successfully.');
    }

    /**
     * Show single evaluation.
     */
    public function show($id)
    {
        $evaluation = FinancialEvaluation::with(['applicant', 'evaluator'])->findOrFail($id);

        return view('financial.show', compact('evaluation'));
    }

    /**
     * Edit evaluation.
     */
    public function edit($id)
    {
        abort(410, 'The legacy financial evaluation editor has been retired. Use the procurement evaluation workflow.');
    }

    /**
     * Update evaluation.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,submitted',
        ]);

        $evaluation = FinancialEvaluation::findOrFail($id);

        $evaluation->strength_financial_health = $request->strength_financial_health;
        $evaluation->gap_financial_health      = $request->gap_financial_health;

        $evaluation->strength_accuracy = $request->strength_accuracy;
        $evaluation->gap_accuracy      = $request->gap_accuracy;

        $evaluation->strength_revenue = $request->strength_revenue;
        $evaluation->gap_revenue      = $request->gap_revenue;

        $evaluation->strength_fund_use = $request->strength_fund_use;
        $evaluation->gap_fund_use      = $request->gap_fund_use;

        $evaluation->strength_liabilities = $request->strength_liabilities;
        $evaluation->gap_liabilities      = $request->gap_liabilities;

        $evaluation->strength_compliance = $request->strength_compliance;
        $evaluation->gap_compliance      = $request->gap_compliance;

        $evaluation->overall_financial_assessment = $request->overall_financial_assessment;
        $evaluation->status = $request->status;

        $evaluation->save();

        return redirect()->route('financial.index')->with('success', 'Financial evaluation updated successfully.');
    }

    /**
     * Delete evaluation.
     */
    public function destroy($id)
    {
        $evaluation = FinancialEvaluation::findOrFail($id);
        $evaluation->delete();

        return redirect()->route('financial.index')->with('success', 'Financial evaluation deleted.');
    }
}
