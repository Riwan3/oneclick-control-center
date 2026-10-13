<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Two-way sync: a menu override remembers the menu's label (the baseline
// and each OneClick site name menus differently, so labels are the
// shared identity a site matches on), and a site reports whether it is
// applying the configuration the Control Center sends it.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_menu_configs', function (Blueprint $table) {
            $table->string('menu_label')->nullable()->after('menu_key');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->json('config_sync')->nullable()->after('menus');
        });
    }

    public function down(): void
    {
        Schema::table('site_menu_configs', function (Blueprint $table) {
            $table->dropColumn('menu_label');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('config_sync');
        });
    }
};
