<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            // Control Center role — keep in sync with resources/js/config/roles.js.
            $table->string('role')->default('view')->after('email');
            $table->string('status')->default('active')->after('role');
            // 'all' = every site; 'selected' = only sites in user_site_access.
            $table->string('site_access_scope')->default('all')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'status', 'site_access_scope']);
        });
    }
};
