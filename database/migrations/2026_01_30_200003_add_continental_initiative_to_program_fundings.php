<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('myb_program_fundings', function (Blueprint $table) {
            $table->boolean('is_continental_initiative')
                ->default(false)
                ->after('status')
                ->comment('When true, applies to all AU member states');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myb_program_fundings', function (Blueprint $table) {
            $table->dropColumn('is_continental_initiative');
        });
    }
};
