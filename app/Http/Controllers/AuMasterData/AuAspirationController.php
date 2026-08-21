<?php

namespace App\Http\Controllers\AuMasterData;

use App\Http\Controllers\Controller;
use App\Models\AuAspiration;
use Illuminate\Http\Request;

class AuAspirationController extends Controller
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
        $aspirations = AuAspiration::with('goals')->ordered()->get();
        return view('au-master-data.aspirations.index', compact('aspirations'));
    }

    public function create()
    {
        $nextNumber = AuAspiration::max('number') + 1;
        return view('au-master-data.aspirations.create', compact('nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:myb_au_aspirations,number',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        AuAspiration::create($validated);

        return redirect()
            ->route('settings.au.aspirations.index')
            ->with('success', 'Aspiration created successfully.');
    }

    public function edit(AuAspiration $aspiration)
    {
        return view('au-master-data.aspirations.edit', compact('aspiration'));
    }

    public function update(Request $request, AuAspiration $aspiration)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:myb_au_aspirations,number,' . $aspiration->id,
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $aspiration->update($validated);

        return redirect()
            ->route('settings.au.aspirations.index')
            ->with('success', 'Aspiration updated successfully.');
    }

    public function destroy(AuAspiration $aspiration)
    {
        if ($aspiration->goals()->count() > 0) {
            return redirect()
                ->route('settings.au.aspirations.index')
                ->with('error', 'Cannot delete aspiration with associated goals. Please delete the goals first.');
        }

        $aspiration->delete();

        return redirect()
            ->route('settings.au.aspirations.index')
            ->with('success', 'Aspiration deleted successfully.');
    }
}
