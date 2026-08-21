<?php

namespace Database\Seeders;

use App\Models\GovernanceNode;
use App\Models\GovernanceReportingLine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GovernanceReportingLineSeeder extends Seeder
{
    public function run(): void
    {
        $relationships = [
            ['child' => 'OPC', 'parent' => 'GOM'],
            ['child' => 'MOD', 'parent' => 'OPC'],
            ['child' => 'MOS', 'parent' => 'OPC'],
            ['child' => 'MOFEPD', 'parent' => 'OPC'],
            ['child' => 'MOAIWD', 'parent' => 'OPC'],
            ['child' => 'MOEST', 'parent' => 'OPC'],
            ['child' => 'MOJCA', 'parent' => 'OPC'],
            ['child' => 'MOFAIC', 'parent' => 'OPC'],
            ['child' => 'MOHS', 'parent' => 'OPC'],
            ['child' => 'MOLGRD', 'parent' => 'OPC'],
            ['child' => 'MOIBTT', 'parent' => 'OPC'],
            ['child' => 'MOTPW', 'parent' => 'OPC'],
            ['child' => 'MOHSEC', 'parent' => 'OPC'],
            ['child' => 'MOGCDSW', 'parent' => 'OPC'],
            ['child' => 'MONREM', 'parent' => 'OPC'],
            ['child' => 'MOLHUD', 'parent' => 'OPC'],
            ['child' => 'MOLSI', 'parent' => 'OPC'],
            ['child' => 'MOYSC', 'parent' => 'OPC'],
            ['child' => 'MOICT', 'parent' => 'OPC'],
            ['child' => 'ESCOM', 'parent' => 'MONREM'],
            ['child' => 'MPC', 'parent' => 'MOICT'],
            ['child' => 'MACRA', 'parent' => 'MOICT'],
            ['child' => 'MRA', 'parent' => 'MOFEPD'],
            ['child' => 'RBM', 'parent' => 'GOM'],
            ['child' => 'PPDA', 'parent' => 'GOM'],
        ];

        DB::transaction(function () use ($relationships): void {
            $nodeIds = GovernanceNode::query()->pluck('id', 'code');

            foreach ($relationships as $relationship) {
                if (! $nodeIds->has($relationship['child']) || ! $nodeIds->has($relationship['parent'])) {
                    throw new RuntimeException(
                        "Missing governance node for reporting line [{$relationship['child']} -> {$relationship['parent']}]. Run GovernanceNodeSeeder first."
                    );
                }
            }

            $lines = GovernanceReportingLine::query()->orderBy('id')->get()->values();
            $rootId = $nodeIds->get('GOM');

            // Neutralise the unique relationship key before reusing the existing UUIDs.
            // PostgreSQL permits multiple NULL values in this unique key.
            foreach ($lines as $line) {
                $line->forceFill([
                    'child_node_id' => $rootId,
                    'parent_node_id' => $rootId,
                    'line_type' => 'primary',
                    'effective_start' => null,
                    'effective_end' => null,
                    'deleted_at' => null,
                ])->save();
            }

            foreach ($relationships as $index => $relationship) {
                $attributes = [
                    'child_node_id' => $nodeIds->get($relationship['child']),
                    'parent_node_id' => $nodeIds->get($relationship['parent']),
                    'line_type' => 'primary',
                    'effective_start' => '2025-10-30',
                    'effective_end' => null,
                    'deleted_at' => null,
                ];

                if ($lines->has($index)) {
                    $lines->get($index)->forceFill($attributes)->save();
                } else {
                    GovernanceReportingLine::query()->create($attributes);
                }
            }

            $lines->slice(count($relationships))->each->delete();
        });
    }
}
