<?php

namespace App\Http\Controllers;

use App\Models\SystemAuditLog;
use Illuminate\Http\Request;

class SystemAuditController extends Controller
{
    public function index(Request $request)
    {
        // Load recent logs for DataTable (client-side search/sort/pagination)
        $logs = SystemAuditLog::with('user')
            ->latest()
            ->limit(1000) // Limit to last 1000 entries for performance
            ->get();

        return view('system.audit.index', compact('logs'));
    }
}
