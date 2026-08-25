<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Security correction: changes the column-level defaults for
// site_access_scope/module_access_scope from 'all' to 'selected' so
// any future insertion path that forgets to set these explicitly
// fails SAFE (restricted) rather than fails OPEN (full access).
//
// This does NOT touch existing row data — ALTER ... SET DEFAULT only
// affects future inserts that omit the column. novtandhip's existing
// 'all' values are untouched, and every real insertion path in this
// app (UserManagementController, SuperAdminSeeder, UserFactory) now
// sets these fields explicitly regardless of the default. Effective
// access for super_admin is governed by role via
// User::effectiveSiteAccess()/effectiveModuleAccess(), not by this
// stored default, so this is defense-in-depth, not the primary fix.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('site_access_scope')->default('selected')->change();
            $table->string('module_access_scope')->default('selected')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('site_access_scope')->default('all')->change();
            $table->string('module_access_scope')->default('all')->change();
        });
    }
};
