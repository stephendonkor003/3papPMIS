<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('myb_sub_activities', function (Blueprint $table) {
            $table->string('expected_outcome_type')->nullable()->after('description');
            $table->text('expected_outcome_value')->nullable()->after('expected_outcome_type');
        });
    }

    public function down(): void
    {
        Schema::table('myb_sub_activities', function (Blueprint $table) {
            $table->dropColumn(['expected_outcome_type', 'expected_outcome_value']);
        });
    }
};
