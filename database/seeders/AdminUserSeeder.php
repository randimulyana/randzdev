<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the development admin account.
     *
     * Run explicitly with `php artisan db:seed --class=AdminUserSeeder`.
     * Requires RolePermissionSeeder to have been run first.
     */
    public function run(): void
    {
        if (App::isProduction()) {
            $this->command?->warn('AdminUserSeeder skipped: not allowed in production.');

            return;
        }

        $password = config('admin.password');

        if (blank($password)) {
            $this->command?->warn('AdminUserSeeder skipped: ADMIN_PASSWORD is not set.');

            return;
        }

        $admin = User::firstOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'password' => Hash::make($password),
            ],
        );

        $admin->assignRole('admin');

        $this->command?->info("Development admin account ready: {$admin->email}");
    }
}
