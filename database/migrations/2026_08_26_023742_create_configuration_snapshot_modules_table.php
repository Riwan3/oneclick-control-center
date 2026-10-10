<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot child rows contain the ACTUAL effective module state at
 * snapshot creation time — unlike site_module_configs (a sparse,
 * mutable set of deviations from baseline), this is a full, dense
 * mirror of every baseline module's resolved enabled/disabled state,
 * frozen forever. Never updated after insert (see
 * App\Models\ConfigurationSnapshotModule).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_snapshot_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('configuration_snapshots')->cascadeOnDelete();
            $table->string('module_key');
            $table->boolean('enabled');
            $table->timestamps();

            $table->unique(['snapshot_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_snapshot_modules');
    }
};
