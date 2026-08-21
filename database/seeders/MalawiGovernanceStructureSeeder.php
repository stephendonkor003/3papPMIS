<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MalawiGovernanceStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call([
                GovernanceLevelSeeder::class,
                GovernanceNodeSeeder::class,
                GovernanceReportingLineSeeder::class,
            ]);
        });
    }
}
