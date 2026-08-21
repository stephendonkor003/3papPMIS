<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\PrescreeningTemplate;
use App\Models\PrescreeningTemplateProcurement;
use Illuminate\Http\Request;
use App\Http\Controllers\Procurement\Concerns\GovernanceScope;

class PrescreeningAssignmentController extends Controller
{
    use GovernanceScope;

    /**
     * Show assignment screen
     */
    public function edit(Procurement $procurement)
    {
        $this->assertProcurementInScope($procurement);
        $templates = PrescreeningTemplate::where('is_active', true)
            ->with('criteria')
            ->get();

        $assignedTemplate = $procurement->prescreeningTemplate;

        return view(
            'procurement.prescreening.assign',
            compact('procurement', 'templates', 'assignedTemplate')
        );
    }

    /**
     * Assign template to procurement
     */
    public function store(Request $request, Procurement $procurement)
    {
        $this->assertProcurementInScope($procurement);
        $request->validate([
            'prescreening_template_id' => 'required|exists:prescreening_templates,id',
        ]);

        // Optional rule: block reassignment if submissions exist
        if ($procurement->submissions()->exists()) {
            return back()->withErrors([
                'prescreening_template_id' =>
                    'Prescreening template cannot be changed after submissions start.',
            ]);
        }

        PrescreeningTemplateProcurement::updateOrCreate(
            ['procurement_id' => $procurement->id],
            [
                'prescreening_template_id' => $request->prescreening_template_id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]
        );

        return redirect()
            ->route('procurements.show', $procurement)
            ->with('success', 'Prescreening template assigned successfully.');
    }
}
