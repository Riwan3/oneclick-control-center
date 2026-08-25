<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Minimal reference table so per-user site access has a real relation to
// point at. The Sites/Site Detail frontend pages are NOT wired to this
// table yet — they continue reading resources/js/data/mock/sites.js
// unchanged. This table exists purely to support the STEP 3M user
// access architecture without hardcoding site identifiers into the
// users table.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
