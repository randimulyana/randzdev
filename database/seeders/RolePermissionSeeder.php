<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Every permission available in the authorization system.
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
        'posts.view',
        'posts.create',
        'posts.update',
        'posts.delete',

        'projects.view',
        'projects.create',
        'projects.update',
        'projects.delete',

        'learning_paths.view',
        'learning_paths.create',
        'learning_paths.update',
        'learning_paths.delete',

        'lessons.view',
        'lessons.create',
        'lessons.update',
        'lessons.delete',

        'categories.view',
        'categories.create',
        'categories.update',
        'categories.delete',

        'technologies.view',
        'technologies.create',
        'technologies.update',
        'technologies.delete',

        'tags.view',
        'tags.create',
        'tags.update',
        'tags.delete',

        'users.view',
        'users.create',
        'users.update',
        'users.delete',

        'roles.view',
        'roles.create',
        'roles.update',
        'roles.delete',

        'permissions.view',
        'permissions.create',
        'permissions.update',
        'permissions.delete',
    ];

    /**
     * Permissions granted to the author role (content management only).
     *
     * @var list<string>
     */
    public const AUTHOR_PERMISSIONS = [
        'posts.view',
        'posts.create',
        'posts.update',
        'posts.delete',

        'projects.view',
        'projects.create',
        'projects.update',
        'projects.delete',

        'learning_paths.view',
        'learning_paths.create',
        'learning_paths.update',
        'learning_paths.delete',

        'lessons.view',
        'lessons.create',
        'lessons.update',
        'lessons.delete',

        'categories.view',
        'categories.create',
        'categories.update',
        'categories.delete',

        'technologies.view',
        'technologies.create',
        'technologies.update',
        'technologies.delete',

        'tags.view',
        'tags.create',
        'tags.update',
        'tags.delete',
    ];

    /**
     * Permissions granted to the regular user role (read-only content).
     *
     * @var list<string>
     */
    public const USER_PERMISSIONS = [
        'posts.view',
        'projects.view',
        'learning_paths.view',
        'lessons.view',
    ];

    /**
     * Seed the roles, permissions, and their mapping.
     *
     * Safe to run repeatedly: records are looked up before being created and
     * role permissions are synced, so no duplicates are produced.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions(Permission::all());
        Role::findOrCreate('author')->syncPermissions(self::AUTHOR_PERMISSIONS);
        Role::findOrCreate('user')->syncPermissions(self::USER_PERMISSIONS);
    }
}
