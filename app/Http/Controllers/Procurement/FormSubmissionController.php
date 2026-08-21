<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function create(DynamicForm $form)
    {
        $form->ensureGlobalFields();
        $form->load('fields');

        return view('procurement.submissions.create', compact('form'));
    }

    public function store(Request $request, DynamicForm $form)
    {
        $form->ensureGlobalFields();
        $form->load('fields');

        $submission = FormSubmission::create([
            'procurement_id' => request('procurement_id'),
            'form_id' => $form->id,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        foreach ($form->fields as $field) {
            FormSubmissionValue::create([
                'submission_id' => $submission->id,
                'field_key' => $field->field_key,
                'value' => $request->input($field->field_key),
            ]);
        }

        return redirect()->route('procurement.submissions.show', $submission);
    }
}
