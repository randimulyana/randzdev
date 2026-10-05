<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::query()->orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        $role->load('permissions');
        $permissions = Permission::query()->orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $rolePermissionNames = $role->permissions->pluck('name')->toArray();
        $isSystemRole = in_array($role->name, ['admin', 'author', 'user'], true);

        return view('admin.roles.edit', compact(
            'role',
            'groupedPermissions',
            'rolePermissionNames',
            'isSystemRole'
        ));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $validated = $request->validated();
        $isSystemRole = in_array($role->name, ['admin', 'author', 'user'], true);

        if ($isSystemRole) {
            if ($request->filled('name') && $request->input('name') !== $role->name) {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'System role names cannot be renamed.'])
                    ->with('error', 'System role names cannot be renamed.');
            }
        }

        if ($role->name === 'admin') {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'The admin role permissions are protected and cannot be modified.');
        }

        if (! $isSystemRole && isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        if ($role->users()->exists()) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Cannot delete this role because it is currently assigned to one or more users. Reassign the users before deleting.');
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Display a read-only permission matrix showing role permissions.
     */
    public function permissions(Request $request): View
    {
        abort_unless($request->user()?->can('permissions.view'), 403);

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()->orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);

        return view('admin.permissions.index', compact('roles', 'groupedPermissions'));
    }

    /**
     * Group permissions by resource domain with readable titles.
     *
     * @param  Collection<int, Permission>  $permissions
     * @return array<string, array{title: string, permissions: Collection<int, Permission>}>
     */
    protected function groupPermissions(Collection $permissions): array
    {
        $domainLabels = [
            'posts' => 'Posts (Blog)',
            'projects' => 'Projects (Portfolio)',
            'learning_paths' => 'Learning Paths',
            'lessons' => 'Lessons',
            'categories' => 'Categories',
            'technologies' => 'Technologies',
            'tags' => 'Tags',
            'users' => 'Users',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
            'contact_messages' => 'Contact Messages',
        ];

        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name, 2);
            $domain = $parts[0] ?? 'general';

            if (! isset($grouped[$domain])) {
                $grouped[$domain] = [
                    'title' => $domainLabels[$domain] ?? ucfirst(str_replace('_', ' ', $domain)),
                    'permissions' => collect(),
                ];
            }

            $grouped[$domain]['permissions']->push($permission);
        }

        return $grouped;
    }
}
