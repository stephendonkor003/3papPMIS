<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('procurements', 'portfolio_id')) {
            Schema::table('procurements', function (Blueprint $table): void {
                $table->foreignUuid('portfolio_id')
                    ->nullable()
                    ->after('governance_node_id')
                    ->constrained('myb_sectors')
                    ->nullOnDelete();
            });
        }

        // A governance node can manage two portfolios. Only backfill rows for
        // unambiguous nodes; new workflows should persist the explicit portfolio.
        DB::statement(<<<'SQL'
            UPDATE procurements AS procurement
            SET portfolio_id = portfolio.id
            FROM myb_sectors AS portfolio
            WHERE procurement.portfolio_id IS NULL
              AND procurement.governance_node_id = portfolio.governance_node_id
              AND portfolio.governance_node_id IN (
                  SELECT governance_node_id
                  FROM myb_sectors
                  WHERE governance_node_id IS NOT NULL
                  GROUP BY governance_node_id
                  HAVING COUNT(*) = 1
              )
        SQL);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('procurements', 'portfolio_id')) {
            return;
        }

        Schema::table('procurements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('portfolio_id');
        });
    }
};
