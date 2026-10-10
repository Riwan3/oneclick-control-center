<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot child rows contain the ACTUAL effective menu state (already
 * resolved for parent-derived-from-children visibility, see
 * App\Services\ConfigurationSnapshotService) at snapshot creation time
 * — a full, dense mirror of every baseline menu item, frozen forever.
 * Never updated after insert (see App\Models\ConfigurationSnapshotMenu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_snapshot_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('configuration_snapshots')->cascadeOnDelete();
            $table->string('module_key');
            $table->string('menu_key');
            $table->boolean('enabled');
            $table->timestamps();

            // Explicit short names — the auto-generated name for a
            // 3-column composite unique on this table name exceeds
            // MySQL's 64-character identifier limit.
            $table->unique(['snapshot_id', 'module_key', 'menu_key'], 'config_snapshot_menus_unique');
            $table->index(['snapshot_id', 'module_key'], 'config_snapshot_menus_module_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_snapshot_menus');
    }
};
