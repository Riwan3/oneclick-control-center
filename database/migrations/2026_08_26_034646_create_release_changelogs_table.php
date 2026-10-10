<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One changelog entry belonging to exactly one Release. `position` is a
// plain manual ordering integer (same convention as
// baselineRegistry.js's/navigation.js's `order` fields) — named
// `position` rather than `order` to avoid the SQL reserved word.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_changelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_id')->constrained('releases')->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['release_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_changelogs');
    }
};
