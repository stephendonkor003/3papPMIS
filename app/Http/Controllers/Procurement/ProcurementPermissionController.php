<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ProcurementUserPermission;
use Illuminate\Http\Request;

class ProcurementPermissionController extends Controller
{
    public function store(Request $request)
    {
        ProcurementUserPermission::create(
            $request->validate([
                'user_id' => 'required',
                'procurement_id' => 'required',
                'form_id' => 'nullable',
                'stage' => 'required',
                'permission' => 'required'
            ]) + [
                'assigned_by' => auth()->id(),
                'assigned_at' => now()
            ]
        );

        return back()->with('success', 'Permission assigned');
    }
}