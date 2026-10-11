<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Telemetry pulled from each OneClick site: server health, environment,
// last deploy time, login activity, and per-module menu manifests.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('environment', 30)->nullable()->after('version');
            $table->timestamp('deployed_at')->nullable()->after('environment');
            $table->json('health')->nullable()->after('modules');
            $table->json('activity')->nullable()->after('health');
            $table->json('menus')->nullable()->after('activity');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['environment', 'deployed_at', 'health', 'activity', 'menus']);
        });
    }
};
