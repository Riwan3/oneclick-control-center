<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modules REGISTERED into the Master Baseline from the Control Center.
 *
 * The 9 modules of the SKS site stay locked in code (App\Support\BaselineKeys,
 * mirrored by the frontend registry). A module a site reports that is not
 * part of that set is an "extension"; registering it promotes it to the
 * baseline, and unregistering it turns it back into an extension. Only the
 * registered ones live here.
 *
 * `menus` is the sidebar hierarchy as the reporting site sent it when the
 * module was registered: [{menu_key, label, parent_key, order}].
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baseline_registered_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 100)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('menus');
            $table->string('source_site_code')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baseline_registered_modules');
    }
};
