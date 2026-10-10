<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SITE CONFIGURATION FOUNDATION.
 *
 * Stores ONLY explicit per-site overrides against the Master Baseline
 * Registry's menu manifest (see the sibling site_module_configs
 * migration for the full rationale — same sparse-override model, same
 * "no baseline duplication" reasoning applies here).
 *
 * `module_key` is carried alongside `menu_key` even though observed
 * menu_key values happen to be globally unique today — this is
 * defensive: it avoids relying on that as an unstated invariant and
 * makes "all menu overrides for site X, module Y" a direct query.
 *
 * Only LEAF menu items (no children in the baseline hierarchy) are
 * ever expected to have a row here — a parent item's effective
 * visibility is derived from its children, not independently stored
 * (see composables/useSiteConfiguration.js for the documented rule).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_menu_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('module_key');
            $table->string('menu_key');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'module_key', 'menu_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_menu_configs');
    }
};
