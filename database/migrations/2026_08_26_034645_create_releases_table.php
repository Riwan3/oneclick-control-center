<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * STEP 3R-2 — RELEASE VERSION + CHANGELOG FOUNDATION.
 *
 * An independent formal Release catalog. Deliberately NOT connected to:
 *  - the Master Baseline's own display version (baselineRegistry.js's
 *    BASELINE_VERSION) or any per-module version (e.g. One-Power's
 *    ONE_POWER_BASELINE_VERSION),
 *  - the Deployments page's per-site "OneClick platform version"
 *    (data/mock/sites.js/deployments.js — pure frontend mock data),
 *  - configuration_snapshots.source_baseline_version (still always
 *    null; this table does not populate it — that link is explicitly
 *    deferred to a future step).
 * `version` is a free-text string, intentionally NOT parsed/normalized
 * into a semantic-versioning system — the business may use whatever
 * version labeling it wants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->date('release_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
