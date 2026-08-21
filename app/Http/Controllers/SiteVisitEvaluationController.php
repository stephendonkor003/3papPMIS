<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EvaluatorTeam;
use App\Models\TeamMember;
use App\Models\TeamConsortium;
use App\Models\SiteVisitEvaluation;
use App\Models\User;
use App\Models\Applicant;
use PDF;

class SiteVisitEvaluationController extends Controller
{
    /* ============================
     *  ADMIN: View all teams
     * ============================ */
    public function index()
    {
        $user = Auth::user();

        if (!$user || $user->user_type !== 'admin') {
            abort(403, 'Access denied. Admins only.');
        }

        $assignedUserIds = TeamMember::pluck('user_id')->toArray();

        $availableEvaluators = User::where('user_type', 'evaluator')
            ->whereNotIn('id', $assignedUserIds)
            ->orderBy('name')
            ->get();

        $teams = EvaluatorTeam::with(['leader', 'members.user', 'consortia.consortium'])->get();

        return view('sitevisit.index', compact('teams', 'availableEvaluators'));
    }

    /* ============================
     *  ADMIN: Create Team
     * ============================ */
    public function createTeam(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $request->validate([
            'name' => 'required|string|max:255',
            'leader_id' => 'required|exists:users,id',
        ]);

        $team = EvaluatorTeam::create([
            'name' => $request->name,
            'leader_id' => $request->leader_id,
            'created_by' => $user->id,
        ]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $request->leader_id,
            'role' => 'leader',
        ]);

        return back()->with('success', 'Team created successfully.');
    }

    /* ============================
     *  ADMIN: Add Member
     * ============================ */
    public function addMember(Request $request, $teamId)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $request->validate(['user_id' => 'required|exists:users,id']);

        TeamMember::create([
            'team_id' => $teamId,
            'user_id' => $request->user_id,
            'role' => 'member',
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    /* ============================
     *  ADMIN: Remove Member
     * ============================ */
    public function removeMember($memberId)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $member = TeamMember::findOrFail($memberId);
        if ($member->role === 'leader') {
            return back()->with('warning', 'You cannot remove a team leader.');
        }

        $member->delete();
        return back()->with('success', 'Team member removed successfully.');
    }

    /* ============================
     *  ADMIN: Assign Consortium
     * ============================ */
    public function assignConsortium(Request $request, $teamId)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $request->validate(['consortium_id' => 'required|exists:applicants,id']);

        $exists = TeamConsortium::where('team_id', $teamId)
            ->where('consortium_id', $request->consortium_id)
            ->exists();

        if ($exists) {
            return back()->with('warning', 'This consortium is already assigned to this team.');
        }

        TeamConsortium::create([
            'team_id' => $teamId,
            'consortium_id' => $request->consortium_id,
            'assigned_by' => $user->id,
        ]);

        return back()->with('success', 'Consortium assigned successfully.');
    }

    /* ============================
     *  LEADER: Dashboard
     * ============================ */
    public function leaderDashboard()
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'evaluator', 403, 'Access denied.');

        $team = EvaluatorTeam::where('leader_id', $user->id)
            ->with(['consortia.consortium'])
            ->first();

        if (!$team) {
            return back()->with('error', 'No team assigned or unauthorized.');
        }

        return view('sitevisit.leader_dashboard', compact('team'));
    }

    /* ============================
     *  LEADER: Show Form
     * ============================ */
       public function showForm($consortiumId)
{
    $user = Auth::user();
    abort_unless($user && $user->user_type === 'evaluator', 403, 'Access denied.');

    $consortium = Applicant::findOrFail($consortiumId);

    // ==========================
    // Define Site Visit Sections
    // ==========================
    $sections = [
        1 => [
            'title' => 'Organizational Capacity',
            'subs' => [
                ['label' => 'Leadership Support', 'marks' => 3, 'guidelines' => [
                    'Check formal endorsement (signed commitments, approvals, appointment letters).',
                    'Assess leadership participation in key approvals, work plans, and budgets.',
                    'Review leadership’s allocation of staff, time, and monitoring.',
                ]],
                ['label' => 'Institutional Support', 'marks' => 3, 'guidelines' => [
                    'Check updated organogram with clear roles and KPI owners.',
                    'Review functioning of finance, procurement, and M&E structures.',
                ]],
                ['label' => 'Leadership Vision', 'marks' => 2, 'guidelines' => [
                    'Check clarity and inclusiveness of the organization’s vision.',
                ]],
                ['label' => 'Organizational Ownership', 'marks' => 2, 'guidelines' => [
                    'Look for evidence of internal ownership of the proposed project.',
                ]],
            ],
        ],
        2 => [
            'title' => 'Technical Capability',
            'subs' => [
                ['label' => 'Technical Expertise', 'marks' => 2, 'guidelines' => [
                    'Review availability of qualified technical staff.',
                ]],
                ['label' => 'Past Performance', 'marks' => 2, 'guidelines' => [
                    'Check previous successful project completions.',
                ]],
                ['label' => 'Methodology Rigor', 'marks' => 1, 'guidelines' => [
                    'Assess soundness of proposed research or operational approach.',
                ]],
            ],
        ],
        3 => [
            'title' => 'Partnerships and Collaboration',
            'subs' => [
                ['label' => 'Consortium Strength', 'marks' => 2],
                ['label' => 'Clear Roles & Contributions', 'marks' => 2],
                ['label' => 'Past Collaborations', 'marks' => 1],
            ],
        ],
        4 => [
            'title' => 'Innovation and Impact',
            'subs' => [
                ['label' => 'Innovation Potential', 'marks' => 2],
                ['label' => 'Project Impact', 'marks' => 2],
                ['label' => 'Achievable Outcomes', 'marks' => 1],
            ],
        ],
        5 => [
            'title' => 'Sustainability',
            'subs' => [
                ['label' => 'Long-Term Sustainability Plan', 'marks' => 2],
                ['label' => 'Strategy for Continued Success', 'marks' => 2],
                ['label' => 'Ongoing Funding Plan', 'marks' => 1],
            ],
        ],
        6 => [
            'title' => 'Facility and Resource Adequacy',
            'subs' => [
                ['label' => 'Physical Resources', 'marks' => 2],
                ['label' => 'Procurement Readiness', 'marks' => 2],
                ['label' => 'Maintenance & Continuity', 'marks' => 1],
            ],
        ],
    ];

    return view('sitevisit.form', compact('consortium', 'sections'));
}

    /* ============================
     *  LEADER: Submit Evaluation
     * ============================ */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->user_type, ['evaluator', 'admin']), 403, 'Access denied.');

        // Base validation
        $rules = [
            'consortium_id' => 'required|exists:applicants,id',
            'evaluation_date' => 'required|date',
            'overall_strength' => 'nullable|string',
            'overall_weakness' => 'nullable|string',
            'additional_comments' => 'nullable|string',
            'evaluator_signature' => 'nullable|string',
            'general_observations' => 'nullable|string',
        ];

        // Dynamic validation for all subsections
        for ($i = 1; $i <= 6; $i++) {
            $subCount = match ($i) {
                1 => 4,
                2, 3, 4, 5, 6 => 3,
                default => 0,
            };
            for ($j = 1; $j <= $subCount; $j++) {
                $rules["s{$i}_{$j}_score"] = 'nullable|numeric|min:0|max:10';
                $rules["s{$i}_{$j}_strength"] = 'nullable|string';
                $rules["s{$i}_{$j}_weakness"] = 'nullable|string';
            }
            $rules["s{$i}_comments"] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // Compute total score
        $total = collect($validated)
            ->filter(fn($v, $k) => str_ends_with($k, '_score'))
            ->sum();

        $team = EvaluatorTeam::where('leader_id', $user->id)->first();

        // Combine all fields
        $data = array_merge($validated, [
            'team_id' => $team->id ?? null,
            'leader_id' => $user->id,
            'evaluator_id' => $user->id,
            'total_score' => $total,
            'evaluator_name' => $user->name,
        ]);

        SiteVisitEvaluation::create($data);

        if ($team) {
            TeamConsortium::where('team_id', $team->id)
                ->where('consortium_id', $request->consortium_id)
                ->update(['status' => 'completed']);
        }

        return redirect()
            ->route('sitevisit.leader.dashboard')
            ->with('success', 'Site visit evaluation submitted successfully. Total Score: ' . $total);
    }

    /* ============================
     *  ADMIN: Reports
     * ============================ */
    public function report()
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $evaluations = SiteVisitEvaluation::with(['consortium', 'team.leader'])->latest()->get();
        return view('sitevisit.report', compact('evaluations'));
    }

    /* ============================
     *  ADMIN: Export PDF (All)
     * ============================ */
    public function exportAllPDF()
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $evaluations = SiteVisitEvaluation::with(['consortium', 'team.leader'])->get();
        $pdf = PDF::loadView('sitevisit.pdf_all', compact('evaluations'))->setPaper('a4', 'portrait');

        return $pdf->download('Site_Visit_Evaluations_Report.pdf');
    }

    /* ============================
     *  ADMIN: Export PDF (Single)
     * ============================ */
    public function exportSinglePDF($id)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

        $evaluation = SiteVisitEvaluation::with(['consortium', 'team.leader'])->findOrFail($id);
        $pdf = PDF::loadView('sitevisit.pdf_single', compact('evaluation'))->setPaper('a4', 'portrait');

        return $pdf->download('Site_Visit_Evaluation_' . $evaluation->id . '.pdf');
    }



    public function requestRework(Request $request, $id)
{
    $user = Auth::user();
    abort_unless($user && $user->user_type === 'admin', 403, 'Access denied.');

    $request->validate(['rework_comment' => 'required|string']);

    $evaluation = SiteVisitEvaluation::findOrFail($id);
    $evaluation->update([
        'rework_status' => 'requested',
        'rework_comment' => $request->rework_comment,
        'rework_requested_by' => $user->id,
    ]);

    return back()->with('success', 'Rework has been requested from the evaluator.');
}

/**
 * Get the Site Visit Evaluation Section structure.
 *
 * @return array
 */
private function getSections(): array
{
    return [
        1 => [
            'title' => 'Organizational Capacity',
            'subs' => [
                [
                    'label' => 'Leadership Support',
                    'marks' => 3,
                    'guidelines' => [
                        'Check formal endorsement (signed commitments, approvals, appointment letters).',
                        'Assess leadership participation in key approvals, work plans, and budgets.',
                        'Review leadership’s allocation of staff, time, and monitoring.',
                    ],
                ],
                [
                    'label' => 'Institutional Support',
                    'marks' => 3,
                    'guidelines' => [
                        'Check updated organogram with clear roles and KPI owners.',
                        'Review functioning of finance, procurement, and M&E structures.',
                    ],
                ],
                ['label' => 'Leadership Vision', 'marks' => 2, 'guidelines' => [
                    'Check clarity and inclusiveness of the organization’s vision.',
                ]],
                ['label' => 'Organizational Ownership', 'marks' => 2, 'guidelines' => [
                    'Look for evidence of internal ownership of the proposed project.',
                ]],
            ],
        ],
        2 => [
            'title' => 'Technical Capability',
            'subs' => [
                ['label' => 'Technical Expertise', 'marks' => 2, 'guidelines' => [
                    'Review availability of qualified technical staff.',
                ]],
                ['label' => 'Past Performance', 'marks' => 2, 'guidelines' => [
                    'Check previous successful project completions.',
                ]],
                ['label' => 'Methodology Rigor', 'marks' => 1, 'guidelines' => [
                    'Assess soundness of proposed research or operational approach.',
                ]],
            ],
        ],
        3 => [
            'title' => 'Partnerships and Collaboration',
            'subs' => [
                ['label' => 'Consortium Strength', 'marks' => 2],
                ['label' => 'Clear Roles & Contributions', 'marks' => 2],
                ['label' => 'Past Collaborations', 'marks' => 1],
            ],
        ],
        4 => [
            'title' => 'Innovation and Impact',
            'subs' => [
                ['label' => 'Innovation Potential', 'marks' => 2],
                ['label' => 'Project Impact', 'marks' => 2],
                ['label' => 'Achievable Outcomes', 'marks' => 1],
            ],
        ],
        5 => [
            'title' => 'Sustainability',
            'subs' => [
                ['label' => 'Long-Term Sustainability Plan', 'marks' => 2],
                ['label' => 'Strategy for Continued Success', 'marks' => 2],
                ['label' => 'Ongoing Funding Plan', 'marks' => 1],
            ],
        ],
        6 => [
            'title' => 'Facility and Resource Adequacy',
            'subs' => [
                ['label' => 'Physical Resources', 'marks' => 2],
                ['label' => 'Procurement Readiness', 'marks' => 2],
                ['label' => 'Maintenance & Continuity', 'marks' => 1],
            ],
        ],
    ];
}

public function editRework($id)
{
    $user = Auth::user();
    abort_unless($user && $user->user_type === 'evaluator', 403, 'Access denied.');

    $evaluation = SiteVisitEvaluation::findOrFail($id);

    if ($evaluation->rework_status !== 'requested') {
        return back()->with('warning', 'This evaluation is not flagged for rework.');
    }

    $consortium = Applicant::find($evaluation->consortium_id);

    // Load same form but pre-filled
    $sections = $this->getSections(); // reuse same logic as showForm
    return view('sitevisit.rework_form', compact('evaluation', 'consortium', 'sections'));
}


public function updateRework(Request $request, $id)
{
    $user = Auth::user();
    abort_unless($user && $user->user_type === 'evaluator', 403, 'Access denied.');

    $evaluation = SiteVisitEvaluation::findOrFail($id);

    // ===============================
    // Base Validation Rules
    // ===============================
    $rules = [
        'evaluation_date' => 'nullable|date',
        'overall_strength' => 'nullable|string',
        'overall_weakness' => 'nullable|string',
        'additional_comments' => 'nullable|string',
        'evaluator_signature' => 'nullable|string',
        'general_observations' => 'nullable|string',
    ];

    // ===============================
    // Dynamic Section & Subsection Rules
    // ===============================
    for ($i = 1; $i <= 6; $i++) {
        $subCount = match ($i) {
            1 => 4,
            2, 3, 4, 5, 6 => 3,
            default => 0,
        };

        for ($j = 1; $j <= $subCount; $j++) {
            $rules["s{$i}_{$j}_score"] = 'nullable|numeric|min:0|max:10';
            $rules["s{$i}_{$j}_strength"] = 'nullable|string';
            $rules["s{$i}_{$j}_weakness"] = 'nullable|string';
        }

        $rules["s{$i}_comments"] = 'nullable|string';
    }

    $validated = $request->validate($rules);

    // ===============================
    // Compute New Total Score
    // ===============================
    $total = collect($validated)
        ->filter(fn($v, $k) => str_ends_with($k, '_score'))
        ->sum();

    // ===============================
    // Update Evaluation Record
    // ===============================
    $evaluation->update(array_merge($validated, [
        'total_score' => $total,
        'rework_status' => 'completed',
        'rework_completed_by' => $user->id,
    ]));

    // ===============================
    // Update Related Consortium Status
    // ===============================
    if ($evaluation->team_id && $evaluation->consortium_id) {
        \App\Models\TeamConsortium::where('team_id', $evaluation->team_id)
            ->where('consortium_id', $evaluation->consortium_id)
            ->update(['status' => 'rework_completed']);
    }

    // ===============================
    // Redirect with Success Message
    // ===============================
    return redirect()
        ->route('sitevisit.leader.dashboard')
        ->with('success', 'Rework completed and submitted successfully. Total Score: ' . $total);
}

}
