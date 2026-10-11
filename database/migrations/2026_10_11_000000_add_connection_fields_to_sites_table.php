<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Connection to each OneClick site (pull model): the Control Center
// calls {base_url}/api/cc/status with api_token. The token is stored
// encrypted (see Site casts) and never returned by any API resource.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('base_url')->nullable()->after('name');
            $table->text('api_token')->nullable()->after('base_url');
            $table->string('status', 20)->default('unknown')->after('api_token');
            $table->string('version')->nullable()->after('status');
            $table->unsignedInteger('users_count')->nullable()->after('version');
            $table->json('modules')->nullable()->after('users_count');
            $table->timestamp('last_checked_at')->nullable()->after('modules');
            $table->string('last_error')->nullable()->after('last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'base_url', 'api_token', 'status', 'version',
                'users_count', 'modules', 'last_checked_at', 'last_error',
            ]);
        });
    }
};
