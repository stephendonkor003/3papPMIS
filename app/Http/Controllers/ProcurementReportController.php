<?php

namespace App\Http\Controllers;

use App\Models\Procurement;

class ProcurementReportController extends Controller
{
    public function show(Procurement $procurement)
    {
        // Permission gate
        if (!auth()->user()->can('site_visits.approve')) {
            abort(403, 'Unauthorized');
        }

        return redirect()->route('reports.evaluations.procurement', $procurement);
    }
}
