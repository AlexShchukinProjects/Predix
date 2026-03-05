<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthPermission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    /** Группы модуля «Планирование» — отображаются под одной папкой */
    private const PLANNING_GROUPS = ['ПДС', 'Летная служба', 'ИАС', 'Отчеты'];

    public function index(): View
    {
        $this->syncSystemPages();
        $all = Role::with('permissions')->orderBy('sort_order')->orderBy('name')->get();
        $super = $all->firstWhere('name', Role::SUPER_ADMIN_NAME);
        $others = $all->where('name', '!=', Role::SUPER_ADMIN_NAME)->values();
        $roles = $super ? $others->prepend($super)->values() : $others;
        $permissionsByGroup = AuthPermission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $permissionTree = $this->buildPermissionTree($permissionsByGroup);
        return view('Admin.Roles.index', [
            'roles' => $roles,
            'permissionTree' => $permissionTree,
            'hasPermissions' => AuthPermission::count() > 0,
            'superAdminRoleName' => Role::SUPER_ADMIN_NAME,
        ]);
    }

    /**
     * Строит дерево: папка «Планирование» объединяет ПДС, Летная служба, ИАС, Отчеты; остальные группы — как есть.
     */
    private function buildPermissionTree(\Illuminate\Support\Collection $permissionsByGroup): array
    {
        $underPlanning = [];
        $other = [];
        foreach ($permissionsByGroup as $group => $items) {
            if (in_array($group, self::PLANNING_GROUPS, true)) {
                $underPlanning[$group] = $items;
            } else {
                $other[$group] = $items;
            }
        }
        $tree = [];
        if ($underPlanning !== []) {
            $tree[] = ['parent' => 'Планирование', 'children' => $underPlanning];
        }
        foreach ($other as $group => $items) {
            $tree[] = ['parent' => null, 'children' => [$group => $items]];
        }
        return $tree;
    }

    /**
     * Синхронизирует страницы из config/system_pages.php с таблицей auth_permissions.
     */
    private function syncSystemPages(): void
    {
        $pages = config('system_pages', []);
        if (! is_array($pages)) {
            return;
        }
        foreach ($pages as $group => $items) {
            if (! is_array($items)) {
                continue;
            }
            foreach ($items as $route => $name) {
                AuthPermission::updateOrCreate(
                    ['slug' => $route],
                    ['name' => $name, 'group' => $group ?: null]
                );
            }
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:roles,id',
        ]);
        foreach ($data['order'] as $position => $roleId) {
            Role::where('id', $roleId)->update(['sort_order' => (int) $position]);
        }
        return response()->json(['success' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $slug = \Illuminate\Support\Str::slug($data['name']);
        $maxOrder = (int) Role::max('sort_order');
        Role::firstOrCreate(
            ['slug' => $slug],
            ['name' => $data['name'], 'sort_order' => $maxOrder + 1]
        );
        return back()->with('success', 'Роль добавлена');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === Role::SUPER_ADMIN_NAME) {
            return back()->with('error', 'Название роли «Суперадминистратор» менять нельзя.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $slug = \Illuminate\Support\Str::slug($data['name']);
        $role->update(['name' => $data['name'], 'slug' => $slug]);
        return back()->with('success', 'Название роли обновлено');
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'integer',
        ]);
        $role = Role::findOrFail($data['role_id']);
        $requestedIds = collect($data['permissions'] ?? [])->map(fn($v) => (int) $v)->unique()->values()->all();
        // Фильтр по той же таблице, из которой строится форма (AuthPermission) — иначе на проде ничего не сохраняется
        $validIds = AuthPermission::whereIn('id', $requestedIds)->pluck('id')->all();
        $role->permissions()->sync($validIds);
        return back()->with('success', 'Права роли обновлены');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === Role::SUPER_ADMIN_NAME) {
            return back()->with('error', 'Роль «Суперадминистратор» удалять нельзя.');
        }
        $role->permissions()->detach();
        $role->delete();
        return back()->with('success', 'Роль удалена');
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
        ]);

        $slug = ($data['slug'] ?? '') !== '' ? (string) $data['slug'] : str()->slug($data['name']);
        AuthPermission::firstOrCreate(['slug' => $slug], [
            'name' => $data['name'],
            'group' => $data['group'] ?? null,
        ]);

        return back()->with('success', 'Право добавлено');
    }
}


