<?php

it('reconciles approved work plans by stable code before a recreated commitment', function () {
    $source = file_get_contents(
        dirname(__DIR__, 2).'/database/seeders/AttpWorkPlan2025Seeder.php'
    );

    expect($source)
        ->toContain("ApprovedWorkPlan::where('awp_code', \$awpCode)->first()")
        ->toContain("ApprovedWorkPlan::where('budget_commitment_id', \$commitment->id)->first()")
        ->not->toMatch("/ApprovedWorkPlan::updateOrCreate\\(\\s*\\['budget_commitment_id'/");

    $codeLookup = strpos($source, "ApprovedWorkPlan::where('awp_code', \$awpCode)->first()");
    $commitmentLookup = strpos($source, "ApprovedWorkPlan::where('budget_commitment_id', \$commitment->id)->first()");

    expect($codeLookup)->not->toBeFalse()
        ->and($commitmentLookup)->not->toBeFalse()
        ->and($codeLookup)->toBeLessThan($commitmentLookup);
});

it('updates canonical seeded fields without resetting workflow history', function () {
    $source = file_get_contents(
        dirname(__DIR__, 2).'/database/seeders/AttpWorkPlan2025Seeder.php'
    );
    $methodStart = strpos($source, 'private function syncApprovedWorkPlan(');
    $methodEnd = strpos($source, 'private function resolveProgram(', $methodStart);

    expect($methodStart)->not->toBeFalse()
        ->and($methodEnd)->not->toBeFalse();

    $method = substr($source, $methodStart, $methodEnd - $methodStart);
    $newRecordBlock = strpos($method, 'if ($isNew)');

    expect($method)
        ->toContain("'awp_code' => \$awpCode")
        ->toContain("'budget_commitment_id' => \$commitment->id")
        ->toContain("'planned_amount' => \$amount")
        ->toContain('if ($isNew)')
        ->toContain("'status' => 'approved'")
        ->toContain("'implementation_notes' =>")
        ->toContain("'approved_by' => \$createdBy")
        ->toContain("'approved_at' => now()")
        ->toContain("'review_notes' =>");

    foreach (['status', 'implementation_notes', 'created_by', 'approved_by', 'approved_at', 'review_notes'] as $field) {
        expect(strpos($method, "'{$field}' =>"))->toBeGreaterThan($newRecordBlock);
    }
});

it('preserves workflow state when reconciling purchase requests commitments and item reviews', function () {
    $source = file_get_contents(
        dirname(__DIR__, 2).'/database/seeders/AttpWorkPlan2025Seeder.php'
    );

    expect($source)
        ->not->toContain('PurchaseRequest::updateOrCreate(')
        ->not->toContain('BudgetCommitment::updateOrCreate(')
        ->not->toContain('ApprovedWorkPlanItemReview::updateOrCreate(');

    $extractMethod = static function (string $name, string $nextName) use ($source): string {
        $start = strpos($source, "private function {$name}(");
        $end = strpos($source, "private function {$nextName}(", $start);

        expect($start)->not->toBeFalse()
            ->and($end)->not->toBeFalse();

        return substr($source, $start, $end - $start);
    };

    $purchaseRequest = $extractMethod('syncPurchaseRequest', 'syncBudgetCommitment');
    $purchaseRequestNew = strpos($purchaseRequest, 'if ($isNew)');

    expect($purchaseRequest)
        ->toContain("PurchaseRequest::firstOrNew(['reference_no' => \$reference])")
        ->toContain("'total_amount' => \$amount")
        ->toContain("'work_plan_source' => self::SOURCE");

    foreach (['status', 'created_by'] as $field) {
        expect(strpos($purchaseRequest, "'{$field}' =>"))->toBeGreaterThan($purchaseRequestNew);
    }

    $commitment = $extractMethod('syncBudgetCommitment', 'syncApprovedWorkPlanItemReview');
    $commitmentNew = strpos($commitment, 'if ($isNew)');

    expect($commitment)
        ->toContain('BudgetCommitment::firstOrNew([')
        ->toContain("'commitment_amount' => \$amount")
        ->toContain("'description' => 'FY2025 ATTP work plan commitment: '");

    foreach (['status', 'created_by', 'approved_by', 'approved_at'] as $field) {
        expect(strpos($commitment, "'{$field}' =>"))->toBeGreaterThan($commitmentNew);
    }

    $review = $extractMethod('syncApprovedWorkPlanItemReview', 'syncApprovedWorkPlan');
    $reviewNew = strpos($review, 'if ($isNew)');

    expect($review)
        ->toContain('ApprovedWorkPlanItemReview::firstOrNew([')
        ->toContain("'program_funding_id' => \$funding->id")
        ->toContain("'funder_id' => \$funder->id");

    foreach (['status', 'reviewed_by', 'reviewed_at', 'review_notes'] as $field) {
        expect(strpos($review, "'{$field}' =>"))->toBeGreaterThan($reviewNew);
    }
});
