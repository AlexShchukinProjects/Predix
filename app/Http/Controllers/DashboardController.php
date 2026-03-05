<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** Соответствие id модуля дашборда и групп из config/system_pages.php (доступ = любая страница из группы). */
    private const MODULE_GROUPS = [
        'reliability' => ['Надежность'],
    ];

    /**
     * Полный список модулей дашборда (для настроек и дашборда).
     */
    public static function getModulesList(): array
    {
        return [
            ['id' => 'reliability', 'name' => 'Надежность', 'name_en' => 'Reliability', 'description' => 'Мониторинг надежности и отказов агрегатов', 'icon' => 'fas fa-cogs', 'color' => '#fd7e14', 'route' => 'modules.reliability.index'],
        ];
    }

    /**
     * Проверяет, есть ли у пользователя доступ к модулю дашборда (по ролям/правам).
     */
    private function userCanAccessModule(array $module): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        $groups = self::MODULE_GROUPS[$module['id']] ?? [];
        if ($groups === []) {
            return true;
        }
        $pages = config('system_pages', []);
        $allowedSlugs = $user->getAllowedRouteSlugs();
        if ($allowedSlugs === null) {
            return true;
        }
        foreach ($groups as $group) {
            $slugs = array_keys($pages[$group] ?? []);
            foreach ($slugs as $slug) {
                if (in_array($slug, $allowedSlugs, true)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function index(Request $request)
    {
        $allModules = self::getModulesList();
        $enabledIds = SystemSetting::get('dashboard_modules');

        if (!is_array($enabledIds) || $enabledIds === []) {
            $modules = $allModules;
        } else {
            $idSet = array_flip($enabledIds);
            $modules = array_values(array_filter($allModules, fn($m) => isset($idSet[$m['id']])));
        }

        $modules = array_values(array_filter($modules, fn($m) => $this->userCanAccessModule($m)));

        if (count($modules) === 1) {
            return redirect()->route($modules[0]['route']);
        }

        return view('dashboard', compact('modules'));
    }
}
