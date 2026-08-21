<?php

namespace App\Services;

class ExecutionInsightBuilder
{
    public static function build(array $data): array
    {
        $insights = [];

        $totalAllocation = $data['total_allocation'];
        $totalCommitment = $data['total_commitment'];
        $executionRate   = $data['execution_rate'];
        $yearly          = $data['yearly'];

        /* =====================================================
         * OVERALL EXECUTION HEALTH
         * ===================================================== */
        if ($executionRate >= 90) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Strong Execution Performance',
                'message' =>
                    'Execution is strong, with most allocated funds effectively committed within the planned period.'
            ];
        } elseif ($executionRate >= 60) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Moderate Execution Performance',
                'message' =>
                    'Execution is progressing but shows signs of delay or uneven spending across years.'
            ];
        } else {
            $insights[] = [
                'type' => 'danger',
                'title' => 'Low Execution Performance',
                'message' =>
                    'A significant portion of allocated funds remains uncommitted, indicating potential implementation or governance issues.'
            ];
        }

        /* =====================================================
         * YEARLY PATTERN ANALYSIS
         * ===================================================== */
        foreach ($yearly as $row) {
            $alloc = $row['allocation'];
            $commit = $row['commitment'];
            $year = $row['year'];

            if ($alloc > 0 && $commit == 0) {
                $insights[] = [
                    'type' => 'danger',
                    'title' => "Idle Allocation Detected ({$year})",
                    'message' =>
                        "Funds were allocated in {$year} but no commitments were recorded. This suggests delayed execution or procurement bottlenecks."
                ];
            }

            if ($commit > $alloc && $alloc > 0) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => "Over-Execution Risk ({$year})",
                    'message' =>
                        "Commitments exceeded allocations in {$year}, indicating a potential budget overrun or reallocation risk."
                ];
            }
        }

        /* =====================================================
         * EXECUTION ACCELERATION / DECELERATION
         * ===================================================== */
        $commitments = collect($yearly)->pluck('commitment')->values();

        if ($commitments->count() >= 3) {
            $trend = $commitments->last() - $commitments->first();

            if ($trend > 0) {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Execution Acceleration Observed',
                    'message' =>
                        'Commitment levels increased over time, suggesting improved implementation capacity or delayed start-up that later stabilized.'
                ];
            } elseif ($trend < 0) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Execution Slowdown Observed',
                    'message' =>
                        'Commitment levels declined in later years, which may indicate completion issues or funding constraints.'
                ];
            }
        }

        /* =====================================================
         * GOVERNANCE SIGNAL
         * ===================================================== */
        if ($totalCommitment > 0 && $totalAllocation > 0) {
            $idleRatio = 100 - $executionRate;

            if ($idleRatio > 30) {
                $insights[] = [
                    'type' => 'danger',
                    'title' => 'High Idle Budget Risk',
                    'message' =>
                        'More than 30% of allocated funds remain uncommitted. Management intervention may be required to unblock execution.'
                ];
            }
        }

        return $insights;
    }
}