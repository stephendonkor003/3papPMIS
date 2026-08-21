<?php

namespace App\Http\Controllers;

use App\Models\PrescreeningTemplate;
use App\Models\PrescreeningCriterion;
use Illuminate\Http\Request;

class PrescreeningCriterionController extends Controller
{
    public function index(PrescreeningTemplate $template)
    {
        $criteria = PrescreeningCriterion::where('prescreening_template_id', $template->id)
            ->orderBy('sort_order')
            ->get();

        return view('prescreening.criteria.index', compact('template', 'criteria'));
    }

    public function create(PrescreeningTemplate $template)
    {
        return view('prescreening.criteria.create', compact('template'));
    }

    public function store(Request $request, PrescreeningTemplate $template)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'field_key'       => 'required|string|max:191',
            'evaluation_type' => 'required|in:yes_no,numeric,exists',
            'min_value'       => 'nullable|numeric',
            'is_mandatory'    => 'boolean',
            'sort_order'      => 'nullable|integer',
        ]);

        PrescreeningCriterion::create([
            ...$validated,
            'prescreening_template_id' => $template->id,
        ]);

        return redirect()
            ->route('prescreening.criteria.index', $template)
            ->with('success', 'Criterion added successfully.');
    }

    public function show(PrescreeningTemplate $template, PrescreeningCriterion $criterion)
    {
        return view('prescreening.criteria.show', compact('template', 'criterion'));
    }

    public function edit(PrescreeningTemplate $template, PrescreeningCriterion $criterion)
    {
        return view('prescreening.criteria.edit', compact('template', 'criterion'));
    }

    public function update(Request $request, PrescreeningTemplate $template, PrescreeningCriterion $criterion)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'field_key'       => 'required|string|max:191',
            'evaluation_type' => 'required|in:yes_no,numeric,exists',
            'min_value'       => 'nullable|numeric',
            'is_mandatory'    => 'boolean',
            'sort_order'      => 'nullable|integer',
        ]);

        $criterion->update($validated);

        return redirect()
            ->route('prescreening.criteria.index', $template)
            ->with('success', 'Criterion updated successfully.');
    }
}