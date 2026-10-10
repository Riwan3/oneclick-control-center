<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * STEP 3R-1 — CONFIGURATION SNAPSHOT FOUNDATION.
 *
 * An immutable historical record of one site's EFFECTIVE configuration
 * (baseline + site_module_configs/site_menu_configs overrides, resolved
 * at creation time) — see App\Services\ConfigurationSnapshotService.
 * Once created, a snapshot's header and child rows are never updated or
 * deleted by application code (see App\Models\ConfigurationSnapshot's
 * `updating` guard) — only new snapshots are ever created.
 *
 * `uuid` is the externally-exposed identifier (route binding, API
 * responses) so snapshot numbers/ids never need to be guessed
 * sequentially; `snapshot_number` is the per-site, human-readable
 * incrementing counter shown in the UI (SNAP-000001 style).
 *
 * `source_baseline_version` is nullable and left null for now — no
 * formally established baseline release version exists yet (that is
 * STEP 3R-2's concern); this column exists so a future release-version
 * value can be attached without another migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('snapshot_number');
            $table->string('source_baseline_version')->nullable();
            $table->unsignedInteger('module_count')->default(0);
            $table->unsignedInteger('menu_count')->default(0);
            $table->string('status')->default('immutable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['site_id', 'snapshot_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_snapshots');
    }
};
