<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        // Filters
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $status = $request->string('status');
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'blocked') {
                $query->where('status', 'blocked');
            }
        }
        if ($request->filled('role')) {
            $roleId = (int) $request->get('role');
            if ($roleId > 0) {
                $query->whereHas('roles', static function ($sub) use ($roleId): void {
                    $sub->where('roles.id', $roleId);
                });
            }
        }

        // Pagination с возможностью выбора "на странице"
        $perPage = (int) $request->get('per_page', 10);

        /** @var LengthAwarePaginator $users */
        $users = $query
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->appends($request->query());

        return view('Admin.Users.index', [
            'users' => $users,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
                'role' => $request->string('role')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('Admin.Users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,blocked',
            'login' => 'nullable|string|max:255',
            'blocked_until' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'personnel_number' => 'nullable|string|max:64',
            'mobile_phone' => 'nullable|string|max:32',
            'roles' => 'array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'login' => $validated['login'] ?? null,
            'blocked_until' => $validated['blocked_until'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'personnel_number' => $validated['personnel_number'] ?? null,
            'mobile_phone' => $validated['mobile_phone'] ?? null,
        ]);

        $roleIds = $this->filterSuperAdminRole($validated['roles'] ?? []);
        $user->roles()->sync($roleIds);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь создан');
    }

    public function edit(User $user): View
    {
        return view('Admin.Users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,blocked',
            'login' => 'nullable|string|max:255',
            'blocked_until' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'personnel_number' => 'nullable|string|max:64',
            'mobile_phone' => 'nullable|string|max:32',
            'roles' => 'array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user->update($validated);

        $roleIds = $this->filterSuperAdminRole($validated['roles'] ?? [], $user);
        $user->roles()->sync($roleIds);

        return redirect()->route('admin.users.index')->with('success', 'Данные пользователя обновлены');
    }

    /**
     * Если текущий пользователь не суперадминистратор — убирает роль суперадминистратора
     * из переданного массива ID ролей. Если редактируется существующий пользователь,
     * его текущая роль суперадминистратора сохраняется (не снимается).
     *
     * @param array<int, mixed> $requestedRoleIds
     */
    private function filterSuperAdminRole(array $requestedRoleIds, ?User $targetUser = null): array
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();

        if ($currentUser && $currentUser->isSuperAdmin()) {
            return $requestedRoleIds;
        }

        $superAdminId = Role::where('name', Role::SUPER_ADMIN_NAME)->value('id');

        if (!$superAdminId) {
            return $requestedRoleIds;
        }

        // Убираем суперадмин роль из запрошенных
        $filtered = array_values(array_filter(
            $requestedRoleIds,
            static fn ($id) => (int) $id !== (int) $superAdminId
        ));

        // Если у редактируемого пользователя уже была роль суперадмина — сохраняем её
        if ($targetUser && $targetUser->roles()->where('roles.id', $superAdminId)->exists()) {
            $filtered[] = $superAdminId;
        }

        return $filtered;
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Пользователь удален');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);
        $ids = array_map('intval', $validated['ids']);
        $currentId = Auth::id();
        $ids = array_values(array_filter($ids, static fn (int $id): bool => $id !== $currentId));
        $deleted = User::whereIn('id', $ids)->delete();
        $message = $deleted === 1
            ? 'Удалён 1 пользователь.'
            : 'Удалено пользователей: ' . $deleted . '.';
        if (count($validated['ids']) > $deleted && $currentId) {
            $message .= ' Текущий пользователь не удаляется.';
        }
        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                // хотя бы одна заглавная, одна строчная, одна цифра и один спецсимвол
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.min' => 'Пароль должен содержать не менее 8 символов.',
            'password.confirmed' => 'Пароль и подтверждение не совпадают.',
            'password.regex' => 'Пароль должен содержать прописные и строчные буквы, цифры и спецсимволы.',
        ]);
        $newPassword = $validated['password'];

        $user->update(['password' => Hash::make($newPassword)]);

        return back()->with('success', 'Пароль успешно обновлен');
    }

    /**
     * Login as another user (admin impersonation)
     */
    public function loginAsUser(User $user): RedirectResponse
    {
        $currentAdmin = Auth::user();
        
        // Prevent logging in as yourself
        if ($currentAdmin->id === $user->id) {
            return back()->with('error', 'Вы не можете авторизоваться под своим аккаунтом');
        }

        // Check if user is blocked
        if ($user->status === 'blocked') {
            return back()->with('error', 'Невозможно авторизоваться под заблокированным пользователем');
        }

        // Store current admin ID in session to allow returning back
        session(['impersonating_admin_id' => $currentAdmin->id]);
        
        // Login as the target user
        Auth::login($user);
        
        return redirect()->route('dashboard')->with('success', "Вы авторизовались под пользователем: {$user->name}");
    }

    /**
     * Return to admin account from impersonation
     */
    public function stopImpersonating(): RedirectResponse
    {
        $adminId = session('impersonating_admin_id');
        
        if (!$adminId) {
            return redirect()->route('dashboard')->with('error', 'Вы не находитесь в режиме авторизации под другим пользователем');
        }

        $admin = User::find($adminId);
        
        if (!$admin) {
            session()->forget('impersonating_admin_id');
            return redirect()->route('dashboard')->with('error', 'Администратор не найден');
        }

        // Clear impersonation session
        session()->forget('impersonating_admin_id');
        
        // Login back as admin
        Auth::login($admin);
        
        return redirect()->route('admin.users.index')->with('success', 'Вы вернулись к своему аккаунту');
    }
}
