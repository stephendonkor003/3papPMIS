<?php

namespace App\Http\Controllers\AuMasterData;

use App\Http\Controllers\Controller;
use App\Models\AuRegionalBlock;
use Illuminate\Http\Request;

class AuRegionalBlockController extends Controller
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
        $regionalBlocks = AuRegionalBlock::ordered()->get();
        return view('au-master-data.regional-blocks.index', compact('regionalBlocks'));
    }

    public function create()
    {
        return view('au-master-data.regional-blocks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:myb_au_regional_blocks,name',
            'abbreviation' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        AuRegionalBlock::create($validated);

        return redirect()
            ->route('settings.au.regional-blocks.index')
            ->with('success', 'Regional block created successfully.');
    }

    public function edit(AuRegionalBlock $regional_block)
    {
        return view('au-master-data.regional-blocks.edit', ['regionalBlock' => $regional_block]);
    }

    public function update(Request $request, AuRegionalBlock $regional_block)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:myb_au_regional_blocks,name,' . $regional_block->id,
            'abbreviation' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $regional_block->update($validated);

        return redirect()
            ->route('settings.au.regional-blocks.index')
            ->with('success', 'Regional block updated successfully.');
    }

    public function destroy(AuRegionalBlock $regional_block)
    {
        $regional_block->delete();

        return redirect()
            ->route('settings.au.regional-blocks.index')
            ->with('success', 'Regional block deleted successfully.');
    }
}
