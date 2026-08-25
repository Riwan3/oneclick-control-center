<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * Mirrors resources/js/config/modules.js keys/labels exactly — this is
 * the same catalogue, not a second one.
 */
class ModuleSeeder extends Seeder
{
    private const MODULES = [
        ['key' => 'one-manpower', 'label' => 'One-Manpower'],
        ['key' => 'one-fleet', 'label' => 'One-Fleet'],
        ['key' => 'one-dms', 'label' => 'One-DMS'],
        ['key' => 'one-qcc', 'label' => 'One-QCC'],
    ];

    public function run(): void
    {
        foreach (self::MODULES as $module) {
            Module::updateOrCreate(['key' => $module['key']], $module);
        }
    }
}
