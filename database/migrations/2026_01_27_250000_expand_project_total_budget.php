<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('myb_projects', function (Blueprint $table) {
            $table->decimal('total_budget', 20, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('myb_projects', function (Blueprint $table) {
            $table->decimal('total_budget', 15, 2)->nullable()->change();
        });
    }
};
