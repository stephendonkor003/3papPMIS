<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('system_audit_logs', 'country')) {
                $table->string('country')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('system_audit_logs', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
