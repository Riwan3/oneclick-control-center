<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the bootstrap Super Admin account. The password is NEVER
 * hardcoded here: it is read from BOOTSTRAP_SUPER_ADMIN_PASSWORD in
 * .env (gitignored, never committed) if set, otherwise a random
 * password is generated and printed once to the console output — never
 * written to a file or log. Idempotent: does nothing if the bootstrap
 * username already exists.
 */
class SuperAdminSeeder extends Seeder
{
    private const USERNAME = 'novtandhip';

    public function run(): void
    {
        if (User::where('username', self::USERNAME)->exists()) {
            $this->command?->info("Bootstrap Super Admin '".self::USERNAME."' already exists — skipping.");

            return;
        }

        $envPassword = env('BOOTSTRAP_SUPER_ADMIN_PASSWORD');
        $password = $envPassword ?: Str::password(16);

        User::create([
            'username' => self::USERNAME,
            'name' => 'Novtandhip',
            'email' => 'novtandhip@ppa.co.id',
            'password' => $password, // hashed automatically via the model's 'hashed' cast
            'role' => 'super_admin',
            'status' => 'active',
            'site_access_scope' => 'all',
            'module_access_scope' => 'all',
        ]);

        if ($envPassword) {
            $this->command?->info("Bootstrap Super Admin '".self::USERNAME."' created using BOOTSTRAP_SUPER_ADMIN_PASSWORD from .env.");
        } else {
            $this->command?->warn("Generated bootstrap password for '".self::USERNAME."': {$password}");
            $this->command?->warn('Save this now — it will not be shown again. Change it after first login.');
        }
    }
}
