<?php

return [
    // Absolute score difference that triggers justification
    'delta_threshold' => 2, // e.g. |Human - AI| >= 2

    // Confidence thresholds (example)
    'confidence' => [
        'high'   => 0.85,
        'medium' => 0.60,
        'low'    => 0.00,
    ],
];