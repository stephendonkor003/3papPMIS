<?php

namespace App\Services\Procurement;

use App\Models\FormSubmission;
use App\Models\PrescreeningEvaluation;
use App\Models\PrescreeningResult;

class PrescreeningScoringService
{
    public function calculate(FormSubmission $submission): PrescreeningResult
    {
        $evaluations = PrescreeningEvaluation::where([
                'submission_id' => $submission->id,
                'evaluated_by_ai' => false
            ])
            ->with('criterion')
            ->get();

        if ($evaluations->isEmpty()) {
            throw new \Exception('No prescreening evaluations found.');
        }

        $mandatoryFailed = false;
        $totalScore = 0;

        foreach ($evaluations as $evaluation) {

            $criterion = $evaluation->criterion;

            $score = $evaluation->score ?? 0;
            $max = $criterion->max_score;
            $weight = $criterion->weight;

            if ($criterion->is_mandatory && $score <= 0) {
                $mandatoryFailed = true;
            }

            if ($max > 0) {
                $totalScore += ($score / $max) * $weight;
            }
        }

        // Determine pass mark
        $passMark = $submission->procurement->prescreen_pass_mark ?? 0;

        $status = 'pending';

        if ($mandatoryFailed) {
            $status = 'fail';
        } elseif ($totalScore >= $passMark) {
            $status = 'pass';
        } else {
            $status = 'fail';
        }

        return PrescreeningResult::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'total_score'      => round($totalScore, 2),
                'mandatory_failed' => $mandatoryFailed,
                'pass_status'      => $status,
                'ai_used'          => false,
                'finalized_by'     => auth()->id(),
                'finalized_at'     => now(),
            ]
        );
    }
}
