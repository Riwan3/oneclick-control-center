<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SITE CONFIGURATION FOUNDATION.
 *
 * Stores ONLY explicit per-site overrides against the Master Baseline
 * Registry's module list (data/mock/baselineRegistry.js — deliberately
 * NOT duplicated here). Absence of a row for (site, module_key) means
 * "use the baseline default" (enabled), matching the "prefer explicit
 * overrides over full duplication" rule — a site with no customization
 * has zero rows here, not 8.
 *
 * `module_key` is a plain string, not a foreign key into a "baseline
 * modules" table, because no such table exists in this database — the
 * baseline registry intentionally lives only in frontend JS (see the
 * Master Baseline Registry step). Validity of the key is enforced by
 * the FormRequest (format only) and, ultimately, by the frontend being
 * the only caller that knows the true baseline — see the STEP report's
 * "Warnings" section for this tradeoff spelled out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_module_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('module_key');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_module_configs');
    }
};
