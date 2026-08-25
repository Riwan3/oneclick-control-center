<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Brings module access to parity with the existing site_access_scope
// column (added in 2026_08_24_000001) — until now module access had no
// "all vs selected" concept, only the user_module_access pivot rows.
// Additive only: existing rows default to 'all', which matches today's
// actual behavior (no user currently has module_access enforcement).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('module_access_scope')->default('all')->after('site_access_scope');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('module_access_scope');
        });
    }
};
