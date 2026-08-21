<?php

namespace App\Http\Controllers\AuMasterData;

use App\Http\Controllers\Controller;
use App\Models\AuAspiration;
use App\Models\AuGoal;
use Illuminate\Http\Request;

class AuGoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.au_master_data.view')->only(['index']);
        $this->middleware('permission:settings.au_master_data.create')->only(['create', 'store']);
        $this->middleware('permission:settings.au_master_data.edit')->only(['edit', 'update']);
        $this->middleware('permission:settings.au_master_data.delete')->only(['destroy']);
    }

    public function index()
    {
        $goals = AuGoal::with('aspiration')->ordered()->get();
        return view('au-master-data.goals.index', compact('goals'));
    }

    public function create()
    {
        $aspirations = AuAspiration::active()->ordered()->get();
        $nextNumber = AuGoal::max('number') + 1;
        return view('au-master-data.goals.create', compact('aspirations', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'aspiration_id' => 'required|exists:myb_au_aspirations,id',
            'number' => 'required|integer|min:1|unique:myb_au_goals,number',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        AuGoal::create($validated);

        return redirect()
            ->route('settings.au.goals.index')
            ->with('success', 'Goal created successfully.');
    }

    public function edit(AuGoal $goal)
    {
        $aspirations = AuAspiration::active()->ordered()->get();
        return view('au-master-data.goals.edit', compact('goal', 'aspirations'));
    }

    public function update(Request $request, AuGoal $goal)
    {
        $validated = $request->validate([
            'aspiration_id' => 'required|exists:myb_au_aspirations,id',
            'number' => 'required|integer|min:1|unique:myb_au_goals,number,' . $goal->id,
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $goal->update($validated);

        return redirect()
            ->route('settings.au.goals.index')
            ->with('success', 'Goal updated successfully.');
    }

    public function destroy(AuGoal $goal)
    {
        $goal->delete();

        return redirect()
            ->route('settings.au.goals.index')
            ->with('success', 'Goal deleted successfully.');
    }

    /**
     * Get goals by aspiration (AJAX endpoint).
     */
    public function byAspiration(Request $request)
    {
        $aspirationIds = $request->input('aspiration_ids', []);

        $goals = AuGoal::active()
            ->whereIn('aspiration_id', $aspirationIds)
            ->ordered()
            ->get(['id', 'number', 'title', 'aspiration_id']);

        return response()->json($goals);
    }
}
