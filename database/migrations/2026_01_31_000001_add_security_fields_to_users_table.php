<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(true)->after('password_changed_at');
            }
            if (!Schema::hasColumn('users', 'otp_verified_at')) {
                $table->timestamp('otp_verified_at')->nullable()->after('must_change_password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'password_changed_at')) {
                $columns[] = 'password_changed_at';
            }
            if (Schema::hasColumn('users', 'must_change_password')) {
                $columns[] = 'must_change_password';
            }
            if (Schema::hasColumn('users', 'otp_verified_at')) {
                $columns[] = 'otp_verified_at';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
